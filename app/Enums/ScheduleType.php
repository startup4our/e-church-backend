<?php

namespace App\Enums;

enum ScheduleType: string
{
    case LOUVOR = 'louvor';
    case GERAL = 'geral';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
