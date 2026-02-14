<?php

namespace App\Application\Services\Event;

use App\Application\Contracts\Services\EventServiceInterface;
use App\Application\Event\Actions\CreateEventAction;
use App\Application\Event\Actions\DeleteEventAction;
use App\Application\Event\Actions\FindEventAction;
use App\Application\Event\Actions\FindEventWithTicketsAction;
use App\Application\Event\Actions\ListEventsAction;
use App\Application\Event\Actions\UpdateEventAction;
use App\Application\Event\DTO\CreateEventData;
use App\Application\Event\DTO\ListEventsData;
use App\Application\Event\DTO\UpdateEventData;
use App\Domain\Event\Models\Event;
use App\Domain\User\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class EventService implements EventServiceInterface
{
    public function __construct(
        private readonly ListEventsAction $listEventsAction,
        private readonly CreateEventAction $createEventAction,
        private readonly UpdateEventAction $updateEventAction,
        private readonly DeleteEventAction $deleteEventAction,
        private readonly FindEventAction $findEventAction,
        private readonly FindEventWithTicketsAction $findEventWithTicketsAction,
    ) {
    }

    /**
     * @return LengthAwarePaginator<int, Event>
     */
    public function index(ListEventsData $query): LengthAwarePaginator
    {
        return $this->listEventsAction->execute($query);
    }

    public function show(int $id): Event
    {
        return $this->findEventWithTicketsAction->execute($id);
    }

    public function findOrFail(int $id): Event
    {
        return $this->findEventAction->execute($id);
    }

    public function create(User $user, CreateEventData $data): Event
    {
        return $this->createEventAction->execute($user, $data);
    }

    public function update(Event $event, UpdateEventData $data): Event
    {
        return $this->updateEventAction->execute($event, $data);
    }

    public function delete(Event $event): void
    {
        $this->deleteEventAction->execute($event);
    }
}
