<?php

namespace App\Services\Booking;

use App\Contracts\Services\BookingServiceInterface;
use App\Domain\Booking\BookingTransitionGuard;
use App\Domain\Shared\DomainError;
use App\Domain\Shared\DomainException;
use App\DTO\Booking\CreateBookingData;
use App\Enums\BookingStatus;
use App\Enums\Role;
use App\Models\Booking;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class BookingService implements BookingServiceInterface
{
    public function __construct(private readonly BookingTransitionGuard $transitionGuard)
    {
    }

    public function create(User $user, int $ticketId, CreateBookingData $data): Booking
    {
        $ticket = Ticket::query()->find($ticketId);

        if ($ticket === null) {
            throw new DomainException(DomainError::TICKET_NOT_FOUND);
        }

        if ($ticket->quantity <= 0) {
            throw new DomainException(DomainError::TICKET_SOLD_OUT);
        }

        $quantity = $data->quantity;
        if ($quantity > $ticket->quantity) {
            throw new DomainException(DomainError::NOT_ENOUGH_TICKET_INVENTORY);
        }

        $booking = new Booking();
        $booking->user_id = $user->id;
        $booking->ticket_id = $ticket->id;
        $booking->quantity = $quantity;
        $booking->status = BookingStatus::PENDING;
        $booking->save();

        return $booking;
    }

    public function listFor(User $user): LengthAwarePaginator
    {
        $query = Booking::query()->with(['ticket', 'payment']);
        $role = $user->role instanceof Role ? $user->role->value : (string) $user->role;

        if ($role === Role::CUSTOMER->value) {
            $query->where('user_id', $user->id);
        }

        return $query->paginate();
    }

    public function findOrFail(int $id): Booking
    {
        $booking = Booking::query()->find($id);

        if ($booking === null) {
            throw new DomainException(DomainError::BOOKING_NOT_FOUND);
        }

        return $booking;
    }

    public function cancel(Booking $booking): Booking
    {
        $currentStatus = $booking->status instanceof BookingStatus
            ? $booking->status
            : BookingStatus::from((string) $booking->status);

        if (! $this->transitionGuard->canCancel($currentStatus)) {
            throw new DomainException(DomainError::BOOKING_NOT_PENDING);
        }

        $booking->status = BookingStatus::CANCELLED;
        $booking->save();

        return $booking;
    }
}
