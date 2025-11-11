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

    public function generateSchedule(int $scheduleId, array $areas, int $maxUsers)
    {
        // Delegar toda a lógica para o repository
        return $this->repository->generateSchedule($scheduleId, $areas, $maxUsers);
    }
}
