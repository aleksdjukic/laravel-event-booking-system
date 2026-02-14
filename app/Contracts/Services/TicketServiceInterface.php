<?php

namespace App\Contracts\Services;

use App\DTO\Ticket\CreateTicketData;
use App\DTO\Ticket\UpdateTicketData;
use App\Models\Event;
use App\Models\Ticket;

interface TicketServiceInterface
{
    public function findEventOrFail(int $eventId): Event;

    public function findTicketOrFail(int $id): Ticket;

    public function create(Event $event, CreateTicketData $data): Ticket;

    public function update(Ticket $ticket, UpdateTicketData $data): Ticket;

    public function delete(Ticket $ticket): void;
}
