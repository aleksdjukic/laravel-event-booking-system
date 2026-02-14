<?php

namespace App\Repositories\Eloquent;

use App\Domain\Payment\Repositories\PaymentRepositoryInterface;
use App\Enums\PaymentStatus;
use App\Domain\Booking\Models\Booking;
use App\Domain\Payment\Models\Payment;

class PaymentRepository implements PaymentRepositoryInterface
{
    public function findWithBooking(int $id): ?Payment
    {
        return Payment::query()->with('booking')->find($id);
    }

    public function existsForBooking(int $bookingId): bool
    {
        return Payment::query()->where('booking_id', $bookingId)->exists();
    }

    public function create(Booking $booking, float $amount, PaymentStatus $status): Payment
    {
        $payment = new Payment();
        $payment->booking_id = $booking->id;
        $payment->amount = round($amount, 2);
        $payment->status = $status;
        $payment->save();

        return $payment;
    }
}
