<?php

namespace App\Http\Controllers\Api\V1\Event;

use App\Application\Contracts\Services\EventServiceInterface;
use App\Application\Event\DTO\CreateEventData;
use App\Application\Event\DTO\EventIndexData;
use App\Application\Event\DTO\UpdateEventData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Event\EventIndexRequest;
use App\Http\Requests\Api\V1\Event\EventStoreRequest;
use App\Http\Requests\Api\V1\Event\EventUpdateRequest;
use App\Http\Resources\Api\V1\Event\EventResource;
use App\Domain\Event\Models\Event;
use App\Support\Http\ApiResponder;
use Illuminate\Http\JsonResponse;

class EventController extends Controller
{
    public function __construct(
        private readonly EventServiceInterface $eventService,
        private readonly ApiResponder $responder,
    ) {
    }

    public function index(EventIndexRequest $request): JsonResponse
    {
        $events = $this->eventService->index(EventIndexData::fromArray($request->validated()));

        return $this->responder->success(EventResource::collection($events), 'OK');
    }

    public function show(Event $event): JsonResponse
    {
        $event = $this->eventService->show($event->id);

        return $this->responder->success(EventResource::make($event), 'OK');
    }

    public function store(EventStoreRequest $request): JsonResponse
    {
        $event = $this->eventService->create(
            $request->user(),
            CreateEventData::fromArray($request->validated())
        );

        return $this->responder->created(EventResource::make($event), 'Event created successfully');
    }

    public function update(EventUpdateRequest $request, Event $event): JsonResponse
    {
        $event = $this->eventService->update($event, UpdateEventData::fromArray($request->validated()));

        return $this->responder->success(EventResource::make($event), 'Event updated successfully');
    }

    public function destroy(Event $event): JsonResponse
    {
        $this->authorize('delete', $event);

        $this->eventService->delete($event);

        return $this->responder->success(null, 'Event deleted successfully');
    }
}
