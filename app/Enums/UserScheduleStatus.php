<?php

namespace App\Enums;

enum UserScheduleStatus: string
{
    case CONFIRMED = 'confirmed';
    case SWAP_REQUESTED = 'swap_requested';
}
