<?php

namespace App\Domain\Payment\Repositories;

use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Payment;

interface PaymentRepositoryInterface
{
    public function findWithBooking(int $id): ?Payment;

    public function existsForBooking(int $bookingId): bool;

    public function create(Booking $booking, float $amount, PaymentStatus $status): Payment;
}
