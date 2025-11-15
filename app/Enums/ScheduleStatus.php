<?php

namespace App\Enums;

enum ScheduleStatus: string
{
    case DRAFT = 'R';
    case ACTIVE = 'A';
    case COMPLETE = 'C';
    case DELETED = 'D';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

