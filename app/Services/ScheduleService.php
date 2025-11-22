<?php

namespace App\Services;

use App\Enums\ChatType;
use App\Jobs\SendEmailNotificationJob;
use App\Jobs\SendPushNotificationJob;
use App\Mail\SchedulePublishedMail;
use App\Models\Schedule;
use App\Repositories\ChatRepository;
use App\Repositories\ScheduleRepository;
use App\Services\Interfaces\IScheduleService;
use Illuminate\Database\Eloquent\Collection;
use Log;

class ScheduleService implements IScheduleService
{
    private ScheduleRepository $repository;
    private ChatRepository $chatRepository;

    public function __construct(
        ScheduleRepository $scheduleRepository,
        ChatRepository $chatRepository
    ) {
        $this->repository = $scheduleRepository;
        $this->chatRepository = $chatRepository;
    }


    public function create(array $data): Schedule
    {
        $schedule = $this->repository->create($data);
        Log::info("Created schedule, going to create chat for schedule [{$schedule->id}]");

        // Check if chat already exists for this schedule
        $existingChat = $this->chatRepository->getChatBySchedule($schedule->id);
        
        if (!$existingChat) {
            $chat = $this->chatRepository->create([
                'name' => $schedule->name,
                'description' => "Chat da escala '{$schedule->name}'",
                'chatable_id' => $schedule->id,
                'chatable_type' => ChatType::SCALE->value
            ]);

            Log::info("Created chat [{$chat->id}]");
        } else {
            Log::info("Chat already exists for schedule [{$schedule->id}], skipping creation");
        }

        return $schedule;
    }

    public function getAll(): Collection
    {
        return $this->repository->getAll();
    }

    public function getById(int $id): Schedule
    {
        return $this->repository->getById($id);
    }

    public function update(int $id, array $data): Schedule
    {
        return $this->repository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }

    public function generateSchedule(int $scheduleId, array $areas, array $roleRequirements)
    {
        // Delegar toda a lógica para o repository
        return $this->repository->generateSchedule($scheduleId, $areas, $roleRequirements);
    }

    public function publish(int $scheduleId): Schedule
    {
        $schedule = $this->repository->getById($scheduleId);
        
        // Atualizar status para ACTIVE
        $schedule->status = \App\Enums\ScheduleStatus::ACTIVE;
        $schedule->save();
        
        Log::info("Schedule [{$scheduleId}] published, status changed to ACTIVE");
        
        // Buscar todos os participantes
        $participants = \App\Models\UserSchedule::where('schedule_id', $scheduleId)
            ->with('user')
            ->get();
        
        Log::info("Found {$participants->count()} participants for schedule [{$scheduleId}]");
        
        // Disparar jobs assíncronos para email e push notification
        foreach ($participants as $userSchedule) {
            if ($userSchedule->user) {
                $user = $userSchedule->user;
                
                // Disparar job de email
                if ($user->email) {
                    SendEmailNotificationJob::dispatch(
                        $user->email,
                        new SchedulePublishedMail($schedule),
                        "schedule_published:schedule_id={$scheduleId},user_id={$user->id}"
                    );
                }
                
                // Disparar job de push notification
                if ($user->fcm_token) {
                    SendPushNotificationJob::dispatch(
                        $user->fcm_token,
                        'Nova Escala Publicada',
                        "A escala '{$schedule->name}' foi publicada. Você está escalado!",
                        [
                            'type' => 'schedule_published',
                            'schedule_id' => $schedule->id,
                            'schedule_name' => $schedule->name,
                        ],
                        "schedule_published:schedule_id={$scheduleId}",
                        $user->id
                    );
                } else {
                    Log::debug("User has no FCM token, skipping push notification", [
                        'user_id' => $user->id,
                        'schedule_id' => $scheduleId
                    ]);
                }
            }
        }
        
        return $schedule;
    }
}
