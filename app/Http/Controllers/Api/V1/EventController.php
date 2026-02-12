<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Support\Http\ApiResponse;
use App\Support\Traits\CommonQueryScopes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class EventController extends Controller
{
    use ApiResponse;
    use CommonQueryScopes;

    public function index(Request $request): JsonResponse
    {
        $page = (int) $request->query('page', 1);
        $queryKeys = array_keys($request->query());
        $nonCacheableKeys = array_diff($queryKeys, ['page']);

        if ($nonCacheableKeys === []) {
            $version = Cache::get('events:index:version', 1);
            $cacheKey = 'events:index:v'.$version.':page:'.max(1, $page);
            $events = Cache::remember($cacheKey, 120, function () use ($request) {
                return Event::query()->paginate();
            });

            return $this->success($events, 'OK');
        }

        $query = Event::query();

        $this->searchByTitle($query, $request->query('search'));
        $this->filterByDate($query, $request->query('date'));

        $location = $request->query('location');
        if (is_string($location) && $location !== '') {
            $query->where('location', 'like', '%'.$location.'%');
        }

        $events = $query->paginate();

        return $this->success($events, 'OK');
    }

    public function show(int $id): JsonResponse
    {
        $event = Event::query()->with('tickets')->find($id);

        if ($event === null) {
            return $this->error('Event not found.', 404);
        }

        return $this->success($event, 'OK');
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Event::class);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'date' => ['required', 'date'],
            'location' => ['required', 'string', 'max:255'],
        ]);

        $event = new Event();
        $event->title = $validated['title'];
        $event->description = $validated['description'] ?? null;
        $event->date = $validated['date'];
        $event->location = $validated['location'];
        $event->created_by = $request->user()->id;
        $event->save();

        return $this->created($event, 'Event created successfully');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $event = Event::query()->find($id);

        if ($event === null) {
            return $this->error('Event not found.', 404);
        }

        $this->authorize('update', $event);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'date' => ['required', 'date'],
            'location' => ['required', 'string', 'max:255'],
        ]);

        $event->title = $validated['title'];
        $event->description = $validated['description'] ?? null;
        $event->date = $validated['date'];
        $event->location = $validated['location'];
        $event->save();

        return $this->success($event, 'Event updated successfully');
    }

    public function destroy(int $id): JsonResponse
    {
        $event = Event::query()->find($id);

        if ($event === null) {
            return $this->error('Event not found.', 404);
        }

        $this->authorize('delete', $event);

        $event->delete();

        return $this->success(null, 'Event deleted successfully');
    }
}
