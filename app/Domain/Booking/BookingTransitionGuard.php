<?php

namespace App\Domain\Booking;

use App\Domain\Booking\Enums\BookingStatus;

class BookingTransitionGuard
{
    public function canCancel(BookingStatus $current): bool
    {
        return $current === BookingStatus::PENDING;
    }

    public function canPay(BookingStatus $current): bool
    {
        return $current === BookingStatus::PENDING;
    }
}
