<?php

namespace App\Http\Controllers\Api\V1;

use App\Contracts\Services\TicketServiceInterface;
use App\DTO\Ticket\CreateTicketData;
use App\DTO\Ticket\UpdateTicketData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Ticket\TicketStoreRequest;
use App\Http\Requests\Api\V1\Ticket\TicketUpdateRequest;
use App\Http\Resources\Api\V1\TicketResource;
use App\Models\Event;
use App\Models\Ticket;
use App\Support\Http\ApiResponse;
use Illuminate\Http\JsonResponse;

class TicketController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly TicketServiceInterface $ticketService)
    {
    }

    public function store(TicketStoreRequest $request, Event $event): JsonResponse
    {
        $ticket = $this->ticketService->create($event, CreateTicketData::fromArray($request->validated()));

        return $this->created(TicketResource::make($ticket)->resolve(), 'Ticket created successfully');
    }

    public function update(TicketUpdateRequest $request, Ticket $ticket): JsonResponse
    {
        $ticket = $this->ticketService->update($ticket, UpdateTicketData::fromArray($request->validated()));

        return $this->success(TicketResource::make($ticket)->resolve(), 'Ticket updated successfully');
    }

    public function destroy(Ticket $ticket): JsonResponse
    {
        $this->authorize('delete', $ticket);

        $this->ticketService->delete($ticket);

        return $this->success(null, 'Ticket deleted successfully');
    }
}
