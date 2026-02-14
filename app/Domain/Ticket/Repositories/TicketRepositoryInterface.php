<?php

namespace App\Domain\Ticket\Repositories;

use App\Models\Event;
use App\Models\Ticket;

interface TicketRepositoryInterface
{
    public function find(int $id): ?Ticket;

    public function findForUpdate(int $id): ?Ticket;

    public function duplicateTypeExists(int $eventId, string $type, ?int $excludeTicketId = null): bool;

    public function create(Event $event, string $type, float $price, int $quantity): Ticket;

    public function save(Ticket $ticket): Ticket;

    public function delete(Ticket $ticket): void;
}
