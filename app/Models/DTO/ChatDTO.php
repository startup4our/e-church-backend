<?php

namespace App\Models\DTO;

use App\Models\Chat;

class ChatDTO
{

    public int $id;
    public string $name;
    public ?string $description;
    public string $chatable_type;
    public int $chatable_id;

    public function __construct(int $id, string $name, ?string $description = null, string $chatable_type, int $chatable_id)
    {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->chatable_type = $chatable_type;
        $this->chatable_id = $chatable_id;
    }

    public static function fromModel(Chat $chat): self
    {
        return new self(
            id: $chat->id,
            name: $chat->name,
            description: $chat->description,
            chatable_type: $chat->chatable_type,
            chatable_id: $chat->chatable_id,
        );
    }
}
