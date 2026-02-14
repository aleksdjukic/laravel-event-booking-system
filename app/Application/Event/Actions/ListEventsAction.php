<?php

namespace App\Application\Event\Actions;

use App\Application\Event\DTO\ListEventsData;
use App\Domain\Event\Models\Event;
use App\Domain\Event\Repositories\EventRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class ListEventsAction
{
    public function __construct(private readonly EventRepositoryInterface $eventRepository)
    {
    }

    /**
     * @return LengthAwarePaginator<int, Event>
     */
    public function execute(ListEventsData $query): LengthAwarePaginator
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
}
