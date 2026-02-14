<?php

namespace Tests\Unit\Domain;

use App\Domain\Booking\BookingTransitionGuard;
use App\Domain\Booking\Enums\BookingStatus;
use PHPUnit\Framework\TestCase;

class BookingTransitionGuardTest extends TestCase
{
    public function test_pending_booking_can_be_paid_and_cancelled(): void
    {
        $guard = new BookingTransitionGuard();

        $this->assertTrue($guard->canPay(BookingStatus::PENDING));
        $this->assertTrue($guard->canCancel(BookingStatus::PENDING));
    }

    public function test_non_pending_booking_cannot_be_paid_or_cancelled(): void
    {
        $guard = new BookingTransitionGuard();

        $this->assertFalse($guard->canPay(BookingStatus::CONFIRMED));
        $this->assertFalse($guard->canPay(BookingStatus::CANCELLED));
        $this->assertFalse($guard->canCancel(BookingStatus::CONFIRMED));
        $this->assertFalse($guard->canCancel(BookingStatus::CANCELLED));
    }
}
