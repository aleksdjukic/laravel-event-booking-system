<?php

namespace App\Domain\Booking\Repositories;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\User;
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
