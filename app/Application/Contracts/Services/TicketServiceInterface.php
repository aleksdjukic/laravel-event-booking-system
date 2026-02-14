<?php

namespace App\Application\Contracts\Services;

use App\Application\Ticket\DTO\CreateTicketData;
use App\Application\Ticket\DTO\UpdateTicketData;
use App\Domain\Event\Models\Event;
use App\Domain\Ticket\Models\Ticket;

interface TicketServiceInterface
{
    public function findEventOrFail(int $eventId): Event;

    public function findTicketOrFail(int $id): Ticket;

    public function create(Event $event, CreateTicketData $data): Ticket;

    public function update(Ticket $ticket, UpdateTicketData $data): Ticket;

    public function delete(Ticket $ticket): void;
}
