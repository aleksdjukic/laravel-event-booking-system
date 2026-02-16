<?php

namespace App\Modules\Event\Presentation\Http\Controllers;

use App\Modules\Event\Application\Contracts\EventServiceInterface;
use App\Modules\Shared\Presentation\Http\Controllers\ApiController;
use App\Domain\Event\Models\Event;
use App\Modules\Event\Presentation\Http\Requests\CreateEventRequest;
use App\Modules\Event\Presentation\Http\Requests\DeleteEventRequest;
use App\Modules\Event\Presentation\Http\Requests\ListEventsRequest;
use App\Modules\Event\Presentation\Http\Requests\UpdateEventRequest;
use App\Modules\Event\Presentation\Http\Resources\EventResource;
use Illuminate\Http\JsonResponse;

class EventController extends ApiController
{
    public function __construct(private readonly EventServiceInterface $eventService)
    {
    }

    public function index(ListEventsRequest $request): JsonResponse
    {
        $events = $this->eventService->index($request->toDto());

        return $this->success(EventResource::collection($events), 'OK');
    }

    public function show(Event $event): JsonResponse
    {
        $event = $this->eventService->show($event->id);

        return $this->success(EventResource::make($event), 'OK');
    }

    public function store(CreateEventRequest $request): JsonResponse
    {
        $event = $this->eventService->create(
            $request->user(),
            $request->toDto()
        );

        return $this->created(EventResource::make($event), 'Event created successfully');
    }

    public function update(UpdateEventRequest $request, Event $event): JsonResponse
    {
        $event = $this->eventService->update($event, $request->toDto());

        return $this->success(EventResource::make($event), 'Event updated successfully');
    }

    public function destroy(DeleteEventRequest $request, Event $event): JsonResponse
    {
        $this->eventService->delete($event);

        return $this->success(null, 'Event deleted successfully');
    }
}
