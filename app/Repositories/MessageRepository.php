<?php

namespace App\Repositories;

use App\Models\DTO\MessageDTO;
use App\Models\Message;
use Illuminate\Support\Collection;

class MessageRepository
{
    public function all()
    {
        return Message::with(['user', 'chat'])->get();
    }

    public function findById(int $id): ?Message
    {
        return Message::with(['user', 'chat'])->find($id);
    }

    public function create(array $data): Message
    {
        return Message::create($data);
    }

    public function update(Message $message, array $data): Message
    {
        $message->update($data);
        return $message;
    }

    public function delete(Message $message): void
    {
        $message->delete();
    }

    public function getAllForChats($chats): Collection
    {
        $rows = Message::query()
            ->select([
                'messages.chat_id',
                'messages.content',
                'messages.image_path',
                'messages.sent_at',
                'users.name as user_name',
            ])
            ->join('users', 'messages.user_id', '=', 'users.id')
            ->whereIn('messages.chat_id', $chats)
            ->orderBy('messages.sent_at', 'desc')
            ->get();

        return $rows->map(fn ($row) => MessageDTO::fromArray($row->toArray()));
    }
}
