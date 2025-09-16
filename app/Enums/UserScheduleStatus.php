<?php

namespace App\Enums;

enum UserScheduleStatus: string
{
    case CONFIRMED = 'Confirmado';
    case SWAP_REQUESTED = 'Troca Solicitada';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
