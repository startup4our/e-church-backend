<?php

namespace App\Enums;

enum HandoutStatus: string
{
    case ACTIVE = 'A';   // active
    case PENDING = 'P';  // scheduled
    case INACTIVE = 'I'; // inactive/deleted
    case DELETED = 'D'; // inactive/deleted

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Ativo',
            self::PENDING => 'Agendado',
            self::INACTIVE => 'Inativo',
            self::DELETED => 'Deletado',
        };
    }
}
