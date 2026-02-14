<?php

namespace App\Application\Services\Ticket;

use App\Application\Contracts\Services\TicketServiceInterface;
use App\Domain\Shared\DomainError;
use App\Domain\Shared\DomainException;
use App\Domain\Ticket\Repositories\TicketRepositoryInterface;
use App\Application\Ticket\DTO\CreateTicketData;
use App\Application\Ticket\DTO\UpdateTicketData;
use App\Domain\Event\Models\Event;
use App\Domain\Ticket\Models\Ticket;
use Illuminate\Support\Facades\Cache;

class TicketService implements TicketServiceInterface
{
    public function __construct(private readonly TicketRepositoryInterface $ticketRepository)
    {
    }

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
        $ticket = $this->ticketRepository->find($id);

        if ($ticket === null) {
            throw new DomainException(DomainError::TICKET_NOT_FOUND);
        }

        return $ticket;
    }

    public function create(Event $event, CreateTicketData $data): Ticket
    {
        if ($this->ticketRepository->duplicateTypeExists($event->id, $data->type)) {
            throw new DomainException(DomainError::DUPLICATE_TICKET_TYPE);
        }

        $ticket = $this->ticketRepository->create($event, $data->type, $data->price, $data->quantity);

        $this->bumpEventIndexVersion();

        return $ticket;
    }

    public function update(Ticket $ticket, UpdateTicketData $data): Ticket
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
        $this->bumpEventIndexVersion();

        return $ticket;
    }

    public function delete(Ticket $ticket): void
    {
        $this->ticketRepository->delete($ticket);
        $this->bumpEventIndexVersion();
    }

    private function bumpEventIndexVersion(): void
    {
        Cache::add('events:index:version', 1);
        Cache::increment('events:index:version');
    }
}
