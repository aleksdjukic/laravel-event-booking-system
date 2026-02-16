<?php

namespace App\Modules\Event\Application\Services;

use App\Modules\Event\Application\Contracts\EventServiceInterface;
use App\Modules\Event\Application\Actions\CreateEventAction;
use App\Modules\Event\Application\Actions\DeleteEventAction;
use App\Modules\Event\Application\Actions\FindEventWithTicketsAction;
use App\Modules\Event\Application\Actions\ListEventsAction;
use App\Modules\Event\Application\Actions\UpdateEventAction;
use App\Modules\Event\Application\DTO\CreateEventData;
use App\Modules\Event\Application\DTO\ListEventsData;
use App\Modules\Event\Application\DTO\UpdateEventData;
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
