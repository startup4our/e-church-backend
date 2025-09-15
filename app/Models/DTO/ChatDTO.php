<?php

namespace App\Models\DTO;

class ChatDTO
{
    public int $id;
    public string $name;
    public ?string $description;
    public string $chatable_type;
    public array $messages;

    public function __construct(int $id, string $name, ?string $description = null, string $chatable_type, array $messages = [])
    {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->chatable_type = $chatable_type;
        $this->messages = $messages;
    }

    public static function fromModel(\App\Models\Chat $chat, $messages = []): self
    {
        return new self(
            id: $chat->id,
            name: $chat->name,
            description: $chat->description,
            chatable_type: $chat->chatable_type,
            messages: $messages
        );
    }
}
