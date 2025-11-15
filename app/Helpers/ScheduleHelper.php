<?php

namespace App\Helpers;

use Carbon\Carbon;

class ScheduleHelper
{
    /**
     * Infere o turno (manha, tarde, noite) a partir de um DateTime
     * 
     * Regras:
     * - 6h às 12h = manhã
     * - 12h às 18h = tarde
     * - 18h às 6h = noite
     * 
     * @param \DateTime|Carbon $dateTime
     * @return string 'manha', 'tarde' ou 'noite'
     */
    public static function inferShiftFromDateTime($dateTime): string
    {
        $hour = (int) $dateTime->format('H');
        
        if ($hour >= 6 && $hour < 12) {
            return 'manha';
        } elseif ($hour >= 12 && $hour < 18) {
            return 'tarde';
        } else {
            return 'noite';
        }
    }

    /**
     * Mapeia turno do formato Unavailability para formato DateException
     * 
     * @param string $shift 'manha', 'tarde' ou 'noite'
     * @return string 'morning', 'afternoon' ou 'night'
     */
    public static function mapShiftToDateExceptionFormat(string $shift): string
    {
        return match($shift) {
            'manha' => 'morning',
            'tarde' => 'afternoon',
            'noite' => 'night',
            default => 'morning'
        };
    }

    /**
     * Mapeia turno do formato DateException para formato Unavailability
     * 
     * @param string $shift 'morning', 'afternoon' ou 'night'
     * @return string 'manha', 'tarde' ou 'noite'
     */
    public static function mapShiftToUnavailabilityFormat(string $shift): string
    {
        return match($shift) {
            'morning' => 'manha',
            'afternoon' => 'tarde',
            'night' => 'noite',
            default => 'manha'
        };
    }
}

