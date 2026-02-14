<?php

namespace App\Http\Controllers\Api\V1\Ticket;

use App\Application\Contracts\Services\TicketServiceInterface;
use App\Application\Ticket\DTO\CreateTicketData;
use App\Application\Ticket\DTO\UpdateTicketData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Ticket\CreateTicketRequest;
use App\Http\Requests\Api\V1\Ticket\UpdateTicketRequest;
use App\Http\Resources\Api\V1\Ticket\TicketResource;
use App\Domain\Event\Models\Event;
use App\Domain\Ticket\Models\Ticket;
use App\Support\Http\ApiResponder;
use Illuminate\Http\JsonResponse;

class TicketController extends Controller
{
    public function __construct(
        private readonly TicketServiceInterface $ticketService,
        private readonly ApiResponder $responder,
    ) {
    }

    public function store(CreateTicketRequest $request, Event $event): JsonResponse
    {
        $ticket = $this->ticketService->create($event, CreateTicketData::fromArray($request->validated()));

        return $this->responder->created(TicketResource::make($ticket), 'Ticket created successfully');
    }

    public function update(UpdateTicketRequest $request, Ticket $ticket): JsonResponse
    {
        $ticket = $this->ticketService->update($ticket, UpdateTicketData::fromArray($request->validated()));

        return $this->responder->success(TicketResource::make($ticket), 'Ticket updated successfully');
    }

    public function destroy(Ticket $ticket): JsonResponse
    {
        $this->authorize('delete', $ticket);

        $this->ticketService->delete($ticket);

        return $this->responder->success(null, 'Ticket deleted successfully');
    }
}
