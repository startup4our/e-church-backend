<?php

namespace App\Services;

use App\Models\Chat;
use App\Models\DTO\ChatWithMessagesDTO;
use App\Models\DTO\MessageDTO;
use App\Repositories\ChatRepository;
use App\Repositories\MessageRepository;
use App\Repositories\UserAreaRepository;
use App\Services\Interfaces\IStorageService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ChatService implements \App\Services\Interfaces\IChatService
{
    protected $repository;
    protected $messageRepository;
    protected $userAreaRepository;
    protected $storageService;

    public function __construct(
        ChatRepository $repository, 
        MessageRepository $messageRepository,
        UserAreaRepository $userAreaRepository,
        IStorageService $storageService
    ) {
        $this->repository = $repository;
        $this->messageRepository = $messageRepository;
        $this->userAreaRepository = $userAreaRepository;
        $this->storageService = $storageService;
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
    public function getChatsForUser(int $user_id, array $areas = []): Collection
    {
        Log::info("Getting chats for user [{$user_id}]", ['provided_areas' => $areas]);

        // Se áreas não foram fornecidas, buscar automaticamente as áreas do usuário
        if (empty($areas)) {
            $userAreas = $this->userAreaRepository->getAreasByUserId($user_id);
            $areas = $userAreas->pluck('area_id')->toArray();
            Log::info("User areas retrieved automatically", ['user_areas' => $areas]);
        }

        // Get all chats user participates
        $chats = $this->repository->getAllByUser($user_id, $areas);
        Log::info("Retrieved {$chats->count()} chats for user [{$user_id}]");

        // Get ids
        $chatIds = $chats->pluck('id')->toArray();

        // Get messages of this chats
        $messages = $this->messageRepository->getAllForChats($chatIds);

        // Build
        return $chats->map(function (Chat $chat) use ($messages) {
            $chatMessages = $messages
                ->where('chatId', $chat->id)
                ->map(function (MessageDTO $msg) {
                    if ($msg->imagePath) $msg->imagePath = $this->storageService->getSignedUrl($msg->imagePath);
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

    /**
     * @return ChatWithMessagesDTO
     */
    public function getChatForUserById(int $user_id, int $chat_id): ChatWithMessagesDTO
    {
        Log::info("Getting chat [{$chat_id}] for user [{$user_id}]");

        // Get all chats user participates
        $chats = $this->repository->getOneById($chat_id);
        Log::info("Retrieved {$chats->count()} chats for user [{$user_id}]");

        // Get messages of this chat
        $messages = $this->messageRepository->getAllForChats([$chat_id]);

        // Build
        return $chats->map(function (Chat $chat) use ($messages) {
            $chatMessages = $messages
                ->where('chatId', $chat->id)
                ->map(function (MessageDTO $msg) {
                    return [
                        'content' => $msg->content,
                        'sent_at' => $msg->sentAt,
                        'user_name' => $msg->userName,
                        'image_path' => $msg->imagePath ? $this->storageService->getSignedUrl($msg->imagePath) : $msg->imagePath,
                    ];
                })
                ->toArray();

            return ChatWithMessagesDTO::fromModel($chat, $chatMessages);
        })[0];
    }

    /**
     * Verificar se usuário tem acesso a um chat específico
     */
    public function userHasAccessToChat(int $userId, int $chatId): bool
    {
        Log::info("Checking user [{$userId}] access to chat [{$chatId}]");
        
        $chat = $this->repository->getById($chatId);
        
        // Se o chat é de uma área, verificar se usuário está na área
        if ($chat->chatable_type === 'A') { // ChatType::AREA->value
            $userHasAccess = $this->userAreaRepository->userBelongsToArea($userId, $chat->chatable_id);
            Log::info("User [{$userId}] access to area chat [{$chatId}]: " . ($userHasAccess ? 'granted' : 'denied'));
            return $userHasAccess;
        }

        // Se o chat é de uma escala, verificar se usuário está na escala
        if ($chat->chatable_type === 'S') { // ChatType::SCALE->value
            // Usar a lógica já existente do repository que verifica UserSchedule
            $userAreas = $this->userAreaRepository->getAreasByUserId($userId);
            $areas = $userAreas->pluck('area_id')->toArray();
            
            $userChats = $this->repository->getAllByUser($userId, $areas);
            $hasAccess = $userChats->contains('id', $chatId);
            
            Log::info("User [{$userId}] access to schedule chat [{$chatId}]: " . ($hasAccess ? 'granted' : 'denied'));
            return $hasAccess;
        }

        // Chat independente - pode precisar de lógica específica
        if ($chat->chatable_type === 'I') { // ChatType::INDEPENDENT->value
            // Por enquanto, retornar false - implementar lógica específica se necessário
            Log::info("User [{$userId}] tried to access independent chat [{$chatId}] - access denied by default");
            return false;
        }
        
        Log::warning("Unknown chatable_type for chat [{$chatId}]: {$chat->chatable_type}");
        return false;
    }
}