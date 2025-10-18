<?php

namespace App\Repositories;

use App\Models\DTO\MessageDTO;
use App\Models\Message;
use App\Services\Interfaces\IStorageService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class MessageRepository
{
    protected IStorageService $storageService;

    public function __construct(IStorageService $storageService)
    {
        $this->storageService = $storageService;
    }

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

    public function getAllForChats(array $chats): Collection
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
            ->orderBy('messages.sent_at', 'asc')
            ->get();

        return $rows->map(function ($row) {
            $data = $row->toArray();
            
            // Generate signed URL for image if exists
            if (!empty($data['image_path'])) {
                try {
                    $data['image_url'] = $this->storageService->getSignedUrl($data['image_path'], 60);
                } catch (\Exception $e) {
                    Log::warning('Failed to generate signed URL for message image', [
                        'image_path' => $data['image_path'],
                        'error' => $e->getMessage()
                    ]);
                    $data['image_url'] = null;
                }
            }
            
            return MessageDTO::fromArray($data);
        });
    }
}
