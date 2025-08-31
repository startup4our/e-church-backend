<?php

namespace App\Services;

use App\Models\Message;
use App\Repositories\MessageRepository;
use App\Services\Interfaces\IMessageService;

class MessageService implements IMessageService
{
    protected MessageRepository $repository;

    public function __construct(MessageRepository $repository)
    {
        $this->repository = $repository;
    }

    public function listAll()
    {
        return $this->repository->all();
    }

    public function create(array $data): Message
    {
        return $this->repository->create($data);
    }

    public function get(int $id): ?Message
    {
        return $this->repository->findById($id);
    }

    public function update(Message $message, array $data): Message
    {
        return $this->repository->update($message, $data);
    }

    public function delete(Message $message): void
    {
        $this->repository->delete($message);
    }
}
