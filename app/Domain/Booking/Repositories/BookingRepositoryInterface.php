<?php

namespace App\Domain\Booking\Repositories;

use App\Domain\Booking\Enums\BookingStatus;
use App\Domain\Booking\Models\Booking;
use App\Domain\User\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface BookingRepositoryInterface
{
    public function find(int $id): ?Booking;

    public function findForUpdate(int $id): ?Booking;

    public function create(User $user, int $ticketId, int $quantity, BookingStatus $status): Booking;

    /**
     * @return LengthAwarePaginator<int, Booking>
     */
    public function paginateForUser(User $user, bool $all): LengthAwarePaginator;

    public function save(Booking $booking): Booking;
}
