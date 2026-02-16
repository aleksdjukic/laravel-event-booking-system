<?php

namespace App\Modules\Payment\Application\Actions;

use App\Domain\Booking\BookingTransitionGuard;
use App\Domain\Booking\Models\Booking;
use App\Domain\Payment\Repositories\PaymentRepositoryInterface;
use App\Domain\Shared\DomainError;
use App\Domain\Shared\DomainException;

class EnsureBookingPayableAction
{
    public function __construct(
        private readonly BookingTransitionGuard $bookingTransitionGuard,
        private readonly PaymentRepositoryInterface $paymentRepository,
    ) {
    }

    public function execute(Booking $booking): void
    {
        $bookingStatus = $booking->statusEnum();

        if (! $this->bookingTransitionGuard->canPay($bookingStatus)) {
            throw new DomainException(DomainError::INVALID_BOOKING_STATE_FOR_PAYMENT);
        }

        if ($this->paymentRepository->existsForBooking($booking->id)) {
            throw new DomainException(DomainError::PAYMENT_ALREADY_EXISTS);
        }
    }
}
