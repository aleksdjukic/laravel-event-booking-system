<?php

namespace App\Application\Services\Booking;

use App\Application\Booking\Actions\CancelBookingAction;
use App\Application\Booking\Actions\CreateBookingAction;
use App\Application\Booking\Actions\FindBookingAction;
use App\Application\Booking\Actions\ListBookingsForUserAction;
use App\Application\Contracts\Services\BookingServiceInterface;
use App\Application\Booking\DTO\CreateBookingData;
use App\Domain\Booking\Models\Booking;
use App\Domain\User\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class BookingService implements BookingServiceInterface
{
    public function __construct(
        private readonly CreateBookingAction $createBookingAction,
        private readonly CancelBookingAction $cancelBookingAction,
        private readonly FindBookingAction $findBookingAction,
        private readonly ListBookingsForUserAction $listBookingsForUserAction,
    ) {
    }

    public function create(User $user, int $ticketId, CreateBookingData $data): Booking
    {
        return $this->createBookingAction->execute($user, $ticketId, $data);
    }

    /**
     * @return LengthAwarePaginator<int, Booking>
     */
    public function listFor(User $user): LengthAwarePaginator
    {
        return $this->listBookingsForUserAction->execute($user);
    }

    public function findOrFail(int $id): Booking
    {
        return $this->findBookingAction->execute($id);
    }

    public function cancel(Booking $booking): Booking
    {
        return $this->cancelBookingAction->execute($booking);
    }
}
