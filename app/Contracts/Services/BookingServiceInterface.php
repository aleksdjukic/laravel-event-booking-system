<?php

namespace App\Contracts\Services;

use App\Application\Booking\DTO\CreateBookingData;
use App\Domain\Booking\Models\Booking;
use App\Domain\User\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface BookingServiceInterface
{
    public function create(User $user, int $ticketId, CreateBookingData $data): Booking;

    /**
     * @return LengthAwarePaginator<int, Booking>
     */
    public function listFor(User $user): LengthAwarePaginator;

    public function findOrFail(int $id): Booking;

    public function cancel(Booking $booking): Booking;
}
