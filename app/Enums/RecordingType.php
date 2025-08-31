<?php

namespace App\Enums;

enum RecordingType: string
{
    case SOLO = 'solo';
    case SOPRANO = 'soprano';
    case CONTRALTO = 'contralto';
    case TENOR = 'tenor';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
