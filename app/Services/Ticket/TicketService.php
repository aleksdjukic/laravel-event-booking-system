<?php

namespace App\Services\Ticket;

use App\Contracts\Services\TicketServiceInterface;
use App\Domain\Shared\DomainError;
use App\Domain\Shared\DomainException;
use App\DTO\Ticket\CreateTicketData;
use App\DTO\Ticket\UpdateTicketData;
use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Support\Facades\Cache;

class TicketService implements TicketServiceInterface
{
    public function findEventOrFail(int $eventId): Event
    {
        $event = Event::query()->find($eventId);

        if ($event === null) {
            throw new DomainException(DomainError::EVENT_NOT_FOUND);
        }

        return $event;
    }

    public function findTicketOrFail(int $id): Ticket
    {
        $ticket = Ticket::query()->find($id);

        if ($ticket === null) {
            throw new DomainException(DomainError::TICKET_NOT_FOUND);
        }

        return $ticket;
    }

    public function create(Event $event, CreateTicketData $data): Ticket
    {
        $duplicateTypeExists = Ticket::query()
            ->where('event_id', $event->id)
            ->where('type', $data->type)
            ->exists();

        if ($duplicateTypeExists) {
            throw new DomainException(DomainError::DUPLICATE_TICKET_TYPE);
        }

        $ticket = new Ticket();
        $ticket->event_id = $event->id;
        $ticket->type = $data->type;
        $ticket->price = number_format($data->price, 2, '.', '');
        $ticket->quantity = $data->quantity;
        $ticket->save();

        $this->bumpEventIndexVersion();

        return $ticket;
    }

    public function update(Ticket $ticket, UpdateTicketData $data): Ticket
    {
        $type = $data->type ?? $ticket->type;
        $duplicateTypeExists = Ticket::query()
            ->where('event_id', $ticket->event_id)
            ->where('type', $type)
            ->where('id', '!=', $ticket->id)
            ->exists();

        if ($duplicateTypeExists) {
            throw new DomainException(DomainError::DUPLICATE_TICKET_TYPE);
        }

        if ($data->type !== null) {
            $ticket->type = $data->type;
        }

        if ($data->price !== null) {
            $ticket->price = number_format($data->price, 2, '.', '');
        }

        if ($data->quantity !== null) {
            $ticket->quantity = $data->quantity;
        }

        $ticket->save();
        $this->bumpEventIndexVersion();

        return $ticket;
    }

    public function delete(Ticket $ticket): void
    {
        $ticket->delete();
        $this->bumpEventIndexVersion();
    }

    private function bumpEventIndexVersion(): void
    {
        Cache::add('events:index:version', 1);
        Cache::increment('events:index:version');
    }
}
