<?php

namespace App\Services;

use App\Enums\ChatType;
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
        
        // Enviar email para cada participante
        foreach ($participants as $userSchedule) {
            if ($userSchedule->user && $userSchedule->user->email) {
                try {
                    \Illuminate\Support\Facades\Mail::to($userSchedule->user->email)
                        ->send(new \App\Mail\SchedulePublishedMail($schedule));
                    Log::info("Published schedule email sent", [
                        'schedule_id' => $scheduleId,
                        'user_id' => $userSchedule->user->id,
                        'email' => $userSchedule->user->email
                    ]);
                } catch (\Exception $e) {
                    Log::error("Failed to send published schedule email", [
                        'schedule_id' => $scheduleId,
                        'user_id' => $userSchedule->user->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
        
        return $schedule;
    }
}
