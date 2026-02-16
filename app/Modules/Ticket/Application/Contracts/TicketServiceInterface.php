<?php

namespace App\Modules\Ticket\Application\Contracts;

use App\Modules\Ticket\Application\DTO\CreateTicketData;
use App\Modules\Ticket\Application\DTO\UpdateTicketData;
use App\Domain\Event\Models\Event;
use App\Domain\Ticket\Models\Ticket;

interface TicketServiceInterface
{
    public function create(Event $event, CreateTicketData $data): Ticket;

    public function update(Ticket $ticket, UpdateTicketData $data): Ticket;

    public function delete(Ticket $ticket): void;
}
