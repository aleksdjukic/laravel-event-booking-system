<?php

namespace App\Services\Event;

use App\Contracts\Services\EventServiceInterface;
use App\Contracts\Repositories\EventRepositoryInterface;
use App\Domain\Shared\DomainError;
use App\Domain\Shared\DomainException;
use App\Application\Event\DTO\CreateEventData;
use App\Application\Event\DTO\EventIndexData;
use App\Application\Event\DTO\UpdateEventData;
use App\Domain\Event\Models\Event;
use App\Domain\User\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class EventService implements EventServiceInterface
{
    public function __construct(private readonly EventRepositoryInterface $eventRepository)
    {
    }

    /**
     * @return LengthAwarePaginator<int, Event>
     */
    public function index(EventIndexData $query): LengthAwarePaginator
    {
        $page = $query->page;
        $queryArray = [
            'page' => $query->page,
            'date' => $query->date,
            'search' => $query->search,
            'location' => $query->location,
        ];
        $queryKeys = array_keys(array_filter($queryArray, static fn (mixed $value) => $value !== null));
        $nonCacheableKeys = array_diff($queryKeys, ['page']);

        if ($nonCacheableKeys === []) {
            $version = Cache::get('events:index:version', 1);
            $cacheKey = 'events:index:v'.$version.':page:'.$page;

            return Cache::remember($cacheKey, 120, fn () => $this->eventRepository->paginate($query));
        }

        return $this->eventRepository->paginate($query);
    }

    public function show(int $id): Event
    {
        $event = $this->eventRepository->findWithTickets($id);

        if ($event === null) {
            throw new DomainException(DomainError::EVENT_NOT_FOUND);
        }

        return $event;
    }

    public function findOrFail(int $id): Event
    {
        $event = $this->eventRepository->find($id);

        if ($event === null) {
            throw new DomainException(DomainError::EVENT_NOT_FOUND);
        }

        return $event;
    }

    public function create(User $user, CreateEventData $data): Event
    {
        return $this->eventRepository->create($user, $data);
    }

    public function update(Event $event, UpdateEventData $data): Event
    {
        return $this->eventRepository->update($event, $data);
    }

    public function delete(Event $event): void
    {
        $this->eventRepository->delete($event);
    }
}
