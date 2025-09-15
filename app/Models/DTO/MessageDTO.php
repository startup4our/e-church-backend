<?php

namespace App\Models\DTO;

class MessageDTO
{
    public function __construct(
        public int $chatId,
        public string $content,
        public ?string $imagePath,
        public string $sentAt,
        public string $userName
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            chatId: $data['chat_id'],
            content: $data['content'],
            imagePath: $data['image_path'] ?? null,
            sentAt: $data['sent_at'],
            userName: $data['user_name'],
        );
    }
}
