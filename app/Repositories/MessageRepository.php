<?php

namespace App\Repositories;

use App\Models\Message;

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
}
