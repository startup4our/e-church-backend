<?php

namespace App\Services;

use App\Enums\BatchStatus;
use App\Jobs\CreateBulkSchedulesJob;
use App\Models\DTO\BulkScheduleCreateDTO;
use App\Models\ScheduleBatch;
use App\Repositories\ScheduleBatchRepository;
use App\Services\Interfaces\IBulkScheduleService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class BulkScheduleService implements IBulkScheduleService
{
    public function __construct(
        private ScheduleBatchRepository $batchRepository
    ) {}

    /**
     * Inicia criação em massa (cria batch e dispara job)
     */
    public function createBulkSchedules(
        BulkScheduleCreateDTO $dto,
        int $userId,
        int $churchId
    ): ScheduleBatch {
        Log::info("Iniciando criação de escalas em massa", [
            'user_id' => $userId,
            'church_id' => $churchId,
            'quantity' => $dto->quantity,
            'recurrence' => $dto->recurrence->value,
            'auto_fill' => $dto->autoFill,
            'template_id' => $dto->templateId
        ]);
        
        try {
            $batch = $this->batchRepository->create([
                'name' => $dto->nameBase,
                'total_schedules' => $dto->quantity,
                'recurrence' => $dto->recurrence->value,
                'start_date' => $dto->startDate,
                'status' => BatchStatus::PENDING->value,
                'template_id' => $dto->templateId,
                'user_creator' => $userId
            ]);
            
            CreateBulkSchedulesJob::dispatch($batch->id, $dto, $userId, $churchId);
            
            Log::info("Batch criado e job disparado", ['batch_id' => $batch->id]);
            
            return $batch;
        } catch (\Exception $e) {
            Log::error("Erro ao iniciar criação de escalas em massa", [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Retorna status do batch
     */
    public function getBatchStatus(int $batchId, int $userId): ScheduleBatch
    {
        try {
            $batch = $this->batchRepository->getById($batchId);
            
            if ($batch->user_creator !== $userId) {
                Log::warning("Tentativa de acessar batch de outro usuário", [
                    'user_id' => $userId,
                    'batch_id' => $batchId,
                    'batch_owner' => $batch->user_creator
                ]);
                throw new \App\Exceptions\AppException(
                    \App\Enums\ErrorCode::PERMISSION_DENIED,
                    userMessage: 'Você não tem permissão para visualizar este lote'
                );
            }
            
            return $batch;
        } catch (\App\Exceptions\AppException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error("Erro ao buscar status do batch", [
                'batch_id' => $batchId,
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Lista batches do usuário
     */
    public function getUserBatches(int $userId): Collection
    {
        try {
            return $this->batchRepository->getByUserId($userId);
        } catch (\Exception $e) {
            Log::error("Erro ao buscar batches do usuário", [
                'user_id' => $userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}

