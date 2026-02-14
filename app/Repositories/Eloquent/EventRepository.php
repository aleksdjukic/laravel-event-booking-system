<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\EventRepositoryInterface;
use App\DTO\Event\CreateEventData;
use App\DTO\Event\EventIndexData;
use App\DTO\Event\UpdateEventData;
use App\Models\Event;
use App\Models\User;
use App\Support\Traits\CommonQueryScopes;
use Illuminate\Pagination\LengthAwarePaginator;

class EventRepository implements EventRepositoryInterface
{
    use CommonQueryScopes;

    /**
     * @return LengthAwarePaginator<int, Event>
     */
    public function paginate(EventIndexData $query): LengthAwarePaginator
    {
        $eventQuery = Event::query();
        $this->searchByTitle($eventQuery, $query->search);
        $this->filterByDate($eventQuery, $query->date);

        if ($query->location !== null && $query->location !== '') {
            $eventQuery->where('location', 'like', '%'.$query->location.'%');
        }

        return $eventQuery->paginate();
    }

    public function find(int $id): ?Event
    {
        return Event::query()->find($id);
    }

    public function findWithTickets(int $id): ?Event
    {
        return Event::query()->with('tickets')->find($id);
    }

    public function create(User $user, CreateEventData $data): Event
    {
        $event = new Event();
        $event->title = $data->title;
        $event->description = $data->description;
        $event->date = $data->date;
        $event->location = $data->location;
        $event->created_by = $user->id;
        $event->save();

        return $event;
    }

    public function update(Event $event, UpdateEventData $data): Event
    {
        $event->title = $data->title;
        $event->description = $data->description;
        $event->date = $data->date;
        $event->location = $data->location;
        $event->save();

        return $event;
    }

    public function delete(Event $event): void
    {
        $event->delete();
    }
}
