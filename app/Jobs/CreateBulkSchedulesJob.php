<?php

namespace App\Jobs;

use App\Enums\BatchStatus;
use App\Enums\RecurrenceType;
use App\Enums\ScheduleStatus;
use App\Jobs\SendPushNotificationJob;
use App\Models\Area;
use App\Models\DTO\BulkScheduleCreateDTO;
use App\Models\Schedule;
use App\Models\ScheduleBatch;
use App\Models\ScheduleTemplate;
use App\Models\User;
use App\Repositories\ScheduleRepository;
use App\Repositories\ScheduleTemplateRepository;
use App\Services\AreaService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateBulkSchedulesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 600;

    public function __construct(
        public int $batchId,
        public BulkScheduleCreateDTO $dto,
        public int $userId,
        public int $churchId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(
        ScheduleRepository $scheduleRepository,
        ScheduleTemplateRepository $templateRepository,
        AreaService $areaService
    ): void {
        Log::info("CreateBulkSchedulesJob iniciado", [
            'batch_id' => $this->batchId,
            'user_id' => $this->userId,
            'church_id' => $this->churchId,
            'quantity' => $this->dto->quantity
        ]);

        try {
            $batch = ScheduleBatch::findOrFail($this->batchId);
            Log::info("Batch encontrado", ['batch_id' => $this->batchId, 'status' => $batch->status->value]);

            DB::beginTransaction();

            $batch->update(['status' => BatchStatus::PROCESSING->value]);

            $template = null;
            if ($this->dto->templateId) {
                $template = $templateRepository->getByIdAndUserId(
                    $this->dto->templateId,
                    $this->userId
                );
                
                Log::info("Template carregado", ['template_id' => $template->id, 'name' => $template->name]);
                
                $this->validateTemplateAreas($template, $this->userId, $areaService);
                $this->dto = $this->buildDTOFromTemplate($template, $this->dto);
            }

            $dates = $this->calculateScheduleDates(
                $this->dto->startDate,
                $this->dto->quantity,
                $this->dto->recurrence
            );
            Log::info("Datas calculadas", [
                'total' => count($dates),
                'start' => $dates[0]->format('Y-m-d'),
                'end' => end($dates)->format('Y-m-d')
            ]);

            $createdCount = 0;
            $failedCount = 0;

            foreach ($dates as $index => $date) {
                $sequence = $index + 1;

                try {
                    $schedule = $this->createSingleSchedule(
                        $this->dto,
                        $date,
                        $sequence,
                        $this->userId,
                        $template
                    );

                    if ($this->dto->autoFill) {
                        $scheduleRepository->generateSchedule(
                            $schedule->id,
                            $this->dto->areas,
                            $this->dto->roleRequirements
                        );
                    }

                    $batch->schedules()->attach($schedule->id);
                    $createdCount++;
                } catch (\Exception $e) {
                    Log::error("Erro ao criar escala", [
                        'sequence' => $sequence,
                        'date' => $date->toDateString(),
                        'error' => $e->getMessage()
                    ]);
                    $failedCount++;
                }
            }

            $finalStatus = ($failedCount > 0 && $createdCount === 0) 
                ? BatchStatus::FAILED->value 
                : BatchStatus::COMPLETED->value;
            
            $batch->update([
                'status' => $finalStatus,
                'created_schedules' => $createdCount,
                'failed_schedules' => $failedCount,
                'end_date' => end($dates)
            ]);
            
            DB::commit();

            Log::info("CreateBulkSchedulesJob concluído", [
                'batch_id' => $this->batchId,
                'created' => $createdCount,
                'failed' => $failedCount,
                'status' => $finalStatus
            ]);

            // Enviar notificação push quando o processo terminar
            try {
                $user = User::find($this->userId);
                if ($user && $user->fcm_token) {
                    $statusMessage = $finalStatus === BatchStatus::COMPLETED->value
                        ? "{$createdCount} escalas criadas com sucesso!"
                        : "Processamento concluído com {$failedCount} falha(s).";
                    
                    $title = $finalStatus === BatchStatus::COMPLETED->value
                        ? "Escalas em Massa Criadas"
                        : "Escalas em Massa - Atenção";
                    
                    SendPushNotificationJob::dispatch(
                        $user->fcm_token,
                        $title,
                        $statusMessage,
                        [
                            'type' => 'bulk_schedule_completed',
                            'batch_id' => $this->batchId,
                            'status' => $finalStatus,
                            'created' => $createdCount,
                            'failed' => $failedCount,
                        ],
                        "bulk_schedule_completed:batch_id={$this->batchId}",
                        $this->userId
                    );
                }
            } catch (\Exception $e) {
                Log::warning("Erro ao enviar notificação push de conclusão", [
                    'batch_id' => $this->batchId,
                    'user_id' => $this->userId,
                    'error' => $e->getMessage()
                ]);
                // Não interromper o fluxo se a notificação falhar
            }

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error("CreateBulkSchedulesJob falhou", [
                'batch_id' => $this->batchId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            try {
                $batch = ScheduleBatch::find($this->batchId);
                if ($batch) {
                    $batch->update([
                        'status' => BatchStatus::FAILED->value,
                        'error_message' => $e->getMessage()
                    ]);
                }

                // Enviar notificação de erro
                try {
                    $user = User::find($this->userId);
                    if ($user && $user->fcm_token) {
                        SendPushNotificationJob::dispatch(
                            $user->fcm_token,
                            "Erro ao Criar Escalas em Massa",
                            "Ocorreu um erro ao processar as escalas. Tente novamente.",
                            [
                                'type' => 'bulk_schedule_failed',
                                'batch_id' => $this->batchId,
                            ],
                            "bulk_schedule_failed:batch_id={$this->batchId}",
                            $this->userId
                        );
                    }
                } catch (\Exception $notifException) {
                    Log::warning("Erro ao enviar notificação push de erro", [
                        'batch_id' => $this->batchId,
                        'error' => $notifException->getMessage()
                    ]);
                }
            } catch (\Exception $updateException) {
                Log::error("Erro ao atualizar batch após falha", [
                    'batch_id' => $this->batchId,
                    'error' => $updateException->getMessage()
                ]);
            }

            throw $e;
        }
    }

    /**
     * Valida se usuário ainda tem acesso às áreas do template
     */
    private function validateTemplateAreas(
        ScheduleTemplate $template,
        int $userId,
        AreaService $areaService
    ): void {
        $userAreas = $areaService->getUserAreas($userId);
        $userAreaIds = $userAreas->pluck('id')->toArray();

        $templateAreaIds = $template->templateRoles()
            ->pluck('area_id')
            ->unique()
            ->toArray();

        $missingAreas = array_diff($templateAreaIds, $userAreaIds);

        if (!empty($missingAreas)) {
            $areaNames = Area::whereIn('id', $missingAreas)->pluck('name')->toArray();
            $areaNamesStr = implode(', ', $areaNames);
            
            Log::warning("Usuário não tem acesso a áreas do template", [
                'user_id' => $userId,
                'template_id' => $template->id,
                'missing_areas' => $areaNamesStr
            ]);
            
            throw new \App\Exceptions\AppException(
                \App\Enums\ErrorCode::VALIDATION_ERROR,
                userMessage: "Você não tem mais acesso às seguintes áreas do template: {$areaNamesStr}"
            );
        }
    }

    /**
     * Calcula datas baseado na recorrência
     */
    private function calculateScheduleDates(
        Carbon $startDate,
        int $quantity,
        RecurrenceType $recurrence
    ): array {
        $dates = [];
        $currentDate = $startDate->copy();

        for ($i = 0; $i < $quantity; $i++) {
            $dates[] = $currentDate->copy();

            match ($recurrence) {
                RecurrenceType::DAILY => $currentDate->addDay(),
                RecurrenceType::WEEKLY => $currentDate->addWeek(),
                RecurrenceType::BIWEEKLY => $currentDate->addWeeks(2),
                RecurrenceType::MONTHLY => $currentDate->addMonth(),
            };
        }

        return $dates;
    }

    /**
     * Cria uma única escala
     */
    private function createSingleSchedule(
        BulkScheduleCreateDTO $dto,
        Carbon $date,
        int $sequence,
        int $userId,
        ?ScheduleTemplate $template
    ): Schedule {
        $name = "{$dto->nameBase} #{$sequence}";

        $startDateTime = $date->copy()->setTimeFromTimeString($dto->startTime);
        $endDateTime = $date->copy()->setTimeFromTimeString($dto->endTime);

        $schedule = Schedule::create([
            'name' => $name,
            'description' => $dto->description,
            'local' => $dto->local,
            'start_date' => $startDateTime,
            'end_date' => $endDateTime,
            'type' => $dto->type->value,
            'status' => ScheduleStatus::DRAFT->value,
            'approved' => true,
            'user_creator' => $userId
        ]);

        return $schedule;
    }

    /**
     * Constrói DTO a partir do template
     */
    private function buildDTOFromTemplate(
        ScheduleTemplate $template,
        BulkScheduleCreateDTO $originalDto
    ): BulkScheduleCreateDTO {
        $roleRequirements = $template->templateRoles->map(function ($templateRole) {
            return [
                'area_id' => $templateRole->area_id,
                'role_id' => $templateRole->role_id,
                'count' => $templateRole->count
            ];
        })->toArray();

        $areas = $template->templateRoles->pluck('area_id')->unique()->toArray();

        return new BulkScheduleCreateDTO(
            quantity: $originalDto->quantity,
            nameBase: $originalDto->nameBase,
            type: $template->type,
            description: $originalDto->description,
            local: $originalDto->local,
            startTime: $originalDto->startTime,
            endTime: $originalDto->endTime,
            recurrence: $originalDto->recurrence,
            areas: $areas,
            roleRequirements: $roleRequirements,
            autoFill: $originalDto->autoFill,
            startDate: $originalDto->startDate,
            templateId: $template->id,
            musicTemplateId: $template->music_template_id
        );
    }
}

