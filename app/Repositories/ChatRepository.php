<?php

namespace App\Repositories;

use App\Enums\ChatType;
use App\Models\Area;
use App\Models\Chat;
use App\Models\Schedule;
use App\Models\UserSchedule;
use Illuminate\Support\Collection;

class ChatRepository
{
    protected $model;

    public function __construct(Chat $chat)
    {
        $this->model = $chat;
    }

    public function getAll()
    {
        return $this->model->all();
    }

    public function getById($id)
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $chat = $this->getById($id);
        $chat->update($data);
        return $chat;
    }

    public function delete($id)
    {
        $chat = $this->getById($id);
        return $chat->delete();
    }

    /**
     * Get all chats for user - search in area and schedules chats
     * @param int $user_id
     * @param array $areas
     * @return Collection
     */
    public function getAllByUser(int $user_id, array $areas): Collection
    {
        // Chats of Areas
        $areaChats = Chat::query()
            ->where('chatable_type', 'A')
            ->whereIn('chatable_id', $areas)
            ->get();

        // Chats of Schedules
        $userScheduleIds = UserSchedule::query()
            ->where('user_id', $user_id)
            ->pluck('schedule_id');

        $scheduleChats = Chat::query()
            ->where('chatable_type', 'S')
            ->whereIn('chatable_id', $userScheduleIds)       
            ->get();

        return $areaChats->merge($scheduleChats);
    }

    /**
     * Get one chats for user - search by id
     * @param int $chat_id
     * @return Collection
     */
    public function getOneById(int $chat_id): Collection
    {
        return Chat::whereKey($chat_id)->get();
    }

    /**
     * Buscar chat específico de uma área
     */
    public function getChatByArea(int $areaId): ?Chat
    {
        return $this->model->where('chatable_type', 'A')
                          ->where('chatable_id', $areaId)
                          ->first();
    }
}