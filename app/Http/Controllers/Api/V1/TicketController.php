<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\TicketStoreRequest;
use App\Http\Requests\Api\V1\TicketUpdateRequest;
use App\Models\Event;
use App\Models\Ticket;
use App\Support\Http\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class TicketController extends Controller
{
    use ApiResponse;

    public function store(TicketStoreRequest $request, int $event_id): JsonResponse
    {
        $event = Event::query()->find($event_id);

        if ($event === null) {
            return $this->error('Event not found.', 404);
        }

        $this->authorize('create', [Ticket::class, $event]);

        $validated = $request->validated();
        $duplicateTypeExists = Ticket::query()
            ->where('event_id', $event->id)
            ->where('type', $validated['type'])
            ->exists();

        if ($duplicateTypeExists) {
            return $this->error('Ticket type already exists for this event.', 409);
        }

        $ticket = new Ticket();
        $ticket->event_id = $event->id;
        $ticket->type = $validated['type'];
        $ticket->price = number_format((float) $validated['price'], 2, '.', '');
        $ticket->quantity = $validated['quantity'];
        $ticket->save();

        $this->bumpEventIndexVersion();

        return $this->created($ticket, 'Ticket created successfully');
    }

    public function update(TicketUpdateRequest $request, int $id): JsonResponse
    {
        $ticket = Ticket::query()->find($id);

        if ($ticket === null) {
            return $this->error('Ticket not found.', 404);
        }

        $this->authorize('update', $ticket);

        $validated = $request->validated();
        $type = $validated['type'] ?? $ticket->type;
        $duplicateTypeExists = Ticket::query()
            ->where('event_id', $ticket->event_id)
            ->where('type', $type)
            ->where('id', '!=', $ticket->id)
            ->exists();

        if ($duplicateTypeExists) {
            return $this->error('Ticket type already exists for this event.', 409);
        }

        if (array_key_exists('type', $validated)) {
            $ticket->type = $validated['type'];
        }

        if (array_key_exists('price', $validated)) {
            $ticket->price = number_format((float) $validated['price'], 2, '.', '');
        }

        if (array_key_exists('quantity', $validated)) {
            $ticket->quantity = $validated['quantity'];
        }

        $ticket->save();

        $this->bumpEventIndexVersion();

        return $this->success($ticket, 'Ticket updated successfully');
    }

    public function destroy(int $id): JsonResponse
    {
        $ticket = Ticket::query()->find($id);

        if ($ticket === null) {
            return $this->error('Ticket not found.', 404);
        }

        $this->authorize('delete', $ticket);

        $ticket->delete();

        $this->bumpEventIndexVersion();

        return $this->success(null, 'Ticket deleted successfully');
    }

    private function bumpEventIndexVersion(): void
    {
        Cache::add('events:index:version', 1);
        Cache::increment('events:index:version');
    }
}
