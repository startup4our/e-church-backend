<?php

namespace App\Enums;

enum RecurrenceType: string
{
    case DAILY = 'diária';
    case WEEKLY = 'semanal';
    case BIWEEKLY = 'quinzenal';
    case MONTHLY = 'mensal';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

