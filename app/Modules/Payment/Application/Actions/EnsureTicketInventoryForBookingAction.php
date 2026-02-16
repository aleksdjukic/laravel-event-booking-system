<?php

namespace App\Modules\Payment\Application\Actions;

use App\Domain\Booking\Models\Booking;
use App\Domain\Shared\DomainError;
use App\Domain\Shared\DomainException;
use App\Domain\Ticket\Models\Ticket;

class EnsureTicketInventoryForBookingAction
{
    public function execute(Booking $booking, Ticket $ticket): void
    {
        if ($ticket->quantity <= 0) {
            throw new DomainException(DomainError::TICKET_SOLD_OUT);
        }

        if ($booking->quantity > $ticket->quantity) {
            throw new DomainException(DomainError::NOT_ENOUGH_TICKET_INVENTORY);
        }
    }
}
