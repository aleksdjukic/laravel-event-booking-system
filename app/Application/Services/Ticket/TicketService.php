<?php

namespace App\Application\Services\Ticket;

use App\Application\Contracts\Services\TicketServiceInterface;
use App\Application\Ticket\Actions\CreateTicketAction;
use App\Application\Ticket\Actions\DeleteTicketAction;
use App\Application\Ticket\Actions\FindEventForTicketAction;
use App\Application\Ticket\Actions\FindTicketAction;
use App\Application\Ticket\Actions\UpdateTicketAction;
use App\Application\Ticket\DTO\CreateTicketData;
use App\Application\Ticket\DTO\UpdateTicketData;
use App\Domain\Event\Models\Event;
use App\Domain\Ticket\Models\Ticket;

class TicketService implements TicketServiceInterface
{
    public function __construct(
        private readonly CreateTicketAction $createTicketAction,
        private readonly UpdateTicketAction $updateTicketAction,
        private readonly DeleteTicketAction $deleteTicketAction,
        private readonly FindEventForTicketAction $findEventForTicketAction,
        private readonly FindTicketAction $findTicketAction,
    ) {
    }

    public function findEventOrFail(int $eventId): Event
    {
        return $this->findEventForTicketAction->execute($eventId);
    }

    public function findTicketOrFail(int $id): Ticket
    {
        return $this->findTicketAction->execute($id);
    }

    public function create(Event $event, CreateTicketData $data): Ticket
    {
        return $this->createTicketAction->execute($event, $data);
    }

    public function update(Ticket $ticket, UpdateTicketData $data): Ticket
    {
        return $this->updateTicketAction->execute($ticket, $data);
    }

    public function delete(Ticket $ticket): void
    {
        $this->deleteTicketAction->execute($ticket);
    }
}
