<?php

namespace App\Repositories\Eloquent;

use App\Domain\Ticket\Repositories\TicketRepositoryInterface;
use App\Domain\Event\Models\Event;
use App\Domain\Ticket\Models\Ticket;

class TicketRepository implements TicketRepositoryInterface
{
    public function find(int $id): ?Ticket
    {
        return Ticket::query()->find($id);
    }

    public function findForUpdate(int $id): ?Ticket
    {
        return Ticket::query()->whereKey($id)->lockForUpdate()->first();
    }

    public function duplicateTypeExists(int $eventId, string $type, ?int $excludeTicketId = null): bool
    {
        $query = Ticket::query()
            ->where('event_id', $eventId)
            ->where('type', $type);

        if ($excludeTicketId !== null) {
            $query->where('id', '!=', $excludeTicketId);
        }

        return $query->exists();
    }

    public function create(Event $event, string $type, float $price, int $quantity): Ticket
    {
        $ticket = new Ticket();
        $ticket->event_id = $event->id;
        $ticket->type = $type;
        $ticket->price = round($price, 2);
        $ticket->quantity = $quantity;
        $ticket->save();

        return $ticket;
    }

    public function save(Ticket $ticket): Ticket
    {
        $ticket->save();

        return $ticket;
    }

    public function delete(Ticket $ticket): void
    {
        $ticket->delete();
    }
}
