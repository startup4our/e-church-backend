<?php

namespace App\Services\Interfaces;

use App\Models\Message;

interface IMessageService
{
    public function listAll();
    public function create(array $data): Message;
    public function get(int $id): ?Message;
    public function update(Message $message, array $data): Message;
    public function delete(Message $message): void;
}
