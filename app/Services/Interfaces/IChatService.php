<?php

namespace App\Services\Interfaces;

use App\Models\DTO\ChatWithMessagesDTO;
use Illuminate\Support\Collection;


interface IChatService
{
    public function getAll();
    public function getById($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
    public function getChatsForUser(int $user_id): Collection;
    public function getChatForUserById(int $user_id, int $chat_id): ChatWithMessagesDTO;
    public function userHasAccessToChat(int $userId, int $chatId): bool;

}
