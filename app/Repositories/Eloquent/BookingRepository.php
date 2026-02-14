<?php

namespace App\Repositories\Eloquent;

use App\Domain\Booking\Repositories\BookingRepositoryInterface;
use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class BookingRepository implements BookingRepositoryInterface
{
    public function find(int $id): ?Booking
    {
        return Booking::query()->find($id);
    }

    public function findForUpdate(int $id): ?Booking
    {
        return Booking::query()->whereKey($id)->lockForUpdate()->first();
    }

    public function create(User $user, int $ticketId, int $quantity, BookingStatus $status): Booking
    {
        $booking = new Booking();
        $booking->user_id = $user->id;
        $booking->ticket_id = $ticketId;
        $booking->quantity = $quantity;
        $booking->status = $status;
        $booking->save();

        return $booking;
    }

    /**
     * @return LengthAwarePaginator<int, Booking>
     */
    public function paginateForUser(User $user, bool $all): LengthAwarePaginator
    {
        $query = Booking::query()->with(['ticket', 'payment']);

        if (! $all) {
            $query->where('user_id', $user->id);
        }

        return $query->paginate();
    }

    public function save(Booking $booking): Booking
    {
        $booking->save();

        return $booking;
    }
}
