<?php

namespace App\Services;

use App\Models\Chat;
use App\Models\DTO\ChatWithMessagesDTO;
use App\Models\DTO\MessageDTO;
use App\Repositories\ChatRepository;
use App\Repositories\MessageRepository;
use Illuminate\Support\Collection;

class ChatService implements \App\Services\Interfaces\IChatService
{
    protected $repository;
    protected $messageRepository;

    public function __construct(ChatRepository $repository, MessageRepository $messageRepository)
    {
        $this->repository = $repository;
        $this->messageRepository = $messageRepository;
    }

    public function getAll()
    {
        return $this->repository->getAll();
    }

    public function getById($id)
    {
        return $this->repository->getById($id);
    }

    public function create(array $data)
    {
        return $this->repository->create($data);
    }

    public function update($id, array $data)
    {
        return $this->repository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->repository->delete($id);
    }

    /**
     * @return Collection<int, ChatWithMessagesDTO>
     */
    public function getChatsForUser(int $user_id, array $areas): Collection
    {
        // Get all chats user participates
        $chats = $this->repository->getAllByUser($user_id, $areas);

        // Get ids
        $chatIds = $chats->pluck('id')->toArray();

        // Get messages of this chats
        $messages = $this->messageRepository->getAllForChats($chatIds);

        // Build
        return $chats->map(function (Chat $chat) use ($messages) {
            $chatMessages = $messages
                ->where('chatId', $chat->id)
                ->map(function (MessageDTO $msg) {
                    return [
                        'content' => $msg->content,
                        'sent_at' => $msg->sentAt,
                        'user_name' => $msg->userName,
                        'image_path' => $msg->imagePath,
                    ];
                })
                ->toArray();

            return ChatWithMessagesDTO::fromModel($chat, $chatMessages);
        });
    }

}
