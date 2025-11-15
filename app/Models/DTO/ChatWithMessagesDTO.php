<?php

namespace App\Models\DTO;

use App\Models\Chat;
use App\Models\DTO\ChatDTO;

class ChatWithMessagesDTO
{
    public ChatDTO $chat;
    public array $messages;

    public function __construct(int $id, string $name, string $chatable_type, int $chatable_id, ?string $description = null, array $messages = [])
    {
        $this->chat = new ChatDTO(
            $id,
            $name,
            $description,
            $chatable_type,
            $chatable_id
        );
        $this->messages = $messages;
    }

    public static function fromModel(Chat $chat, $messages = []): self
    {
        return new self(
            id: $chat->id,
            name: $chat->name,
            chatable_type: $chat->chatable_type,
            chatable_id: $chat->chatable_id,
            description: $chat->description,
            messages: $messages
        );
    }
}
