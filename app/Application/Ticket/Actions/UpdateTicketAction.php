<?php

namespace App\Application\Ticket\Actions;

use App\Modules\Event\Application\Actions\BumpEventIndexVersionAction;
use App\Application\Ticket\DTO\UpdateTicketData;
use App\Domain\Shared\DomainError;
use App\Domain\Shared\DomainException;
use App\Domain\Ticket\Models\Ticket;
use App\Domain\Ticket\Repositories\TicketRepositoryInterface;

class UpdateTicketAction
{
    public function __construct(
        private readonly TicketRepositoryInterface $ticketRepository,
        private readonly BumpEventIndexVersionAction $bumpEventIndexVersion,
    ) {
    }

    public function execute(Ticket $ticket, UpdateTicketData $data): Ticket
    {
        $type = $data->type ?? $ticket->type;

        if ($this->ticketRepository->duplicateTypeExists($ticket->event_id, $type, $ticket->id)) {
            throw new DomainException(DomainError::DUPLICATE_TICKET_TYPE);
        }

        if ($data->type !== null) {
            $ticket->type = $data->type;
        }

        if ($data->price !== null) {
            $ticket->price = round($data->price, 2);
        }

        if ($data->quantity !== null) {
            $ticket->quantity = $data->quantity;
        }

        $this->ticketRepository->save($ticket);
        $this->bumpEventIndexVersion->execute();

        return $ticket;
    }
}
