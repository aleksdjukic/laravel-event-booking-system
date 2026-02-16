<?php

namespace App\Modules\Ticket\Application\Services;

use App\Modules\Ticket\Application\Contracts\TicketServiceInterface;
use App\Modules\Ticket\Application\Actions\CreateTicketAction;
use App\Modules\Ticket\Application\Actions\DeleteTicketAction;
use App\Modules\Ticket\Application\Actions\UpdateTicketAction;
use App\Modules\Ticket\Application\DTO\CreateTicketData;
use App\Modules\Ticket\Application\DTO\UpdateTicketData;
use App\Domain\Event\Models\Event;
use App\Domain\Ticket\Models\Ticket;

class TicketService implements TicketServiceInterface
{
    public function __construct(
        private readonly CreateTicketAction $createTicketAction,
        private readonly UpdateTicketAction $updateTicketAction,
        private readonly DeleteTicketAction $deleteTicketAction,
    ) {
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
