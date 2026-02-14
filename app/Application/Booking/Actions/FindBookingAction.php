<?php

namespace App\Application\Booking\Actions;

use App\Domain\Booking\Models\Booking;
use App\Domain\Booking\Repositories\BookingRepositoryInterface;
use App\Domain\Shared\DomainError;
use App\Domain\Shared\DomainException;

class FindBookingAction
{
    public function __construct(private readonly BookingRepositoryInterface $bookingRepository)
    {
    }

    public function execute(int $id): Booking
    {
        $booking = $this->bookingRepository->find($id);

        if ($booking === null) {
            throw new DomainException(DomainError::BOOKING_NOT_FOUND);
        }

        return $booking;
    }
}
