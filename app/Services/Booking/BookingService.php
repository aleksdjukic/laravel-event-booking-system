<?php

namespace App\Services\Booking;

use App\Contracts\Services\BookingServiceInterface;
use App\Domain\Booking\BookingTransitionGuard;
use App\Domain\Booking\Repositories\BookingRepositoryInterface;
use App\Domain\Shared\DomainError;
use App\Domain\Shared\DomainException;
use App\Domain\Ticket\Repositories\TicketRepositoryInterface;
use App\DTO\Booking\CreateBookingData;
use App\Enums\BookingStatus;
use App\Enums\Role;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class BookingService implements BookingServiceInterface
{
    public function __construct(
        private readonly BookingTransitionGuard $transitionGuard,
        private readonly BookingRepositoryInterface $bookingRepository,
        private readonly TicketRepositoryInterface $ticketRepository,
    ) {
    }

    public function create(User $user, int $ticketId, CreateBookingData $data): Booking
    {
        $ticket = $this->ticketRepository->find($ticketId);

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

        return $this->bookingRepository->create($user, $ticket->id, $quantity, BookingStatus::PENDING);
    }

    /**
     * @return LengthAwarePaginator<int, Booking>
     */
    public function listFor(User $user): LengthAwarePaginator
    {
        $role = $user->role instanceof Role ? $user->role->value : (string) $user->role;
        $all = $role !== Role::CUSTOMER->value;

        return $this->bookingRepository->paginateForUser($user, $all);
    }

    public function findOrFail(int $id): Booking
    {
        $booking = $this->bookingRepository->find($id);

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

        return $this->bookingRepository->save($booking);
    }
}
