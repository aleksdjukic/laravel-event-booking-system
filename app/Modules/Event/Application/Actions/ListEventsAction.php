<?php

namespace App\Modules\Event\Application\Actions;

use App\Modules\Event\Application\DTO\ListEventsData;
use App\Domain\Event\Models\Event;
use App\Domain\Event\Repositories\EventRepositoryInterface;
use App\Domain\Event\Support\EventCache;
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
            $version = (int) Cache::get(EventCache::INDEX_VERSION_KEY, 1);
            $cacheKey = EventCache::indexPageKey($version, $page);

            return Cache::remember(
                $cacheKey,
                EventCache::INDEX_TTL_SECONDS,
                fn () => $this->eventRepository->paginate($query->page, $query->date, $query->search, $query->location)
            );
        }

        return $this->eventRepository->paginate($query->page, $query->date, $query->search, $query->location);
    }
}
