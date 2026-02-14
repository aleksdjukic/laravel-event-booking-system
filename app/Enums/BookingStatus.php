<?php

namespace App\Enums;

enum BookingStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case CANCELLED = 'cancelled';

    public function isActive(): bool
    {
        return in_array($this, [self::PENDING, self::CONFIRMED], true);
    }
}
