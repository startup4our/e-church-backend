<?php

namespace App\Models\DTO;

use App\Models\Chat;
use App\Models\DTO\ChatDTO;

class ChatWithMessagesDTO
{
    public ChatDTO $chat;
    public array $messages;

    public function __construct(int $id, string $name, string $chatable_type, ?string $description = null, array $messages = [])
    {
        $this->chat = new ChatDTO(
            $id,
            $name,
            $description,
            $chatable_type
        );
        $this->messages = $messages;
    }

    public static function fromModel(Chat $chat, $messages = []): self
    {
        return new self(
            id: $chat->id,
            name: $chat->name,
            chatable_type: $chat->chatable_type,
            description: $chat->description,
            messages: $messages
        );
    }
}
