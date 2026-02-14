<?php

namespace App\Domain\User\Enums;

enum Role: string
{
    case ADMIN = 'admin';
    case ORGANIZER = 'organizer';
    case CUSTOMER = 'customer';
}
