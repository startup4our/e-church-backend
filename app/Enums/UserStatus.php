<?php

namespace App\Enums;

enum UserStatus: string
{
    case ACTIVE = 'A';
    case INACTIVE = 'I';
    case WAITING_APPROVAL = 'WA';
    case REJECTED = 'R';
}
