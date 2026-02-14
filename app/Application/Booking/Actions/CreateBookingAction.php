<?php

namespace App\Application\Booking\Actions;

use App\Domain\Booking\Repositories\BookingRepositoryInterface;
use App\Domain\Shared\DomainError;
use App\Domain\Shared\DomainException;
use App\Domain\Ticket\Repositories\TicketRepositoryInterface;
use App\DTO\Booking\CreateBookingData;
use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\User;

class CreateBookingAction
{
    public function __construct(
        private readonly BookingRepositoryInterface $bookingRepository,
        private readonly TicketRepositoryInterface $ticketRepository,
    ) {
    }

    public function execute(User $user, int $ticketId, CreateBookingData $data): Booking
    {
        $ticket = $this->ticketRepository->find($ticketId);

        if ($ticket === null) {
            throw new DomainException(DomainError::TICKET_NOT_FOUND);
        }

        if ($ticket->quantity <= 0) {
            throw new DomainException(DomainError::TICKET_SOLD_OUT);
        }

        if ($data->quantity > $ticket->quantity) {
            throw new DomainException(DomainError::NOT_ENOUGH_TICKET_INVENTORY);
        }

        return $this->bookingRepository->create($user, $ticket->id, $data->quantity, BookingStatus::PENDING);
    }
}
