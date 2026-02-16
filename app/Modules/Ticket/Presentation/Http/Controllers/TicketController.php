<?php

namespace App\Modules\Ticket\Presentation\Http\Controllers;

use App\Modules\Ticket\Application\Contracts\TicketServiceInterface;
use App\Modules\Shared\Presentation\Http\Controllers\ApiController;
use App\Domain\Event\Models\Event;
use App\Domain\Ticket\Models\Ticket;
use App\Modules\Ticket\Presentation\Http\Requests\CreateTicketRequest;
use App\Modules\Ticket\Presentation\Http\Requests\DeleteTicketRequest;
use App\Modules\Ticket\Presentation\Http\Requests\UpdateTicketRequest;
use App\Modules\Ticket\Presentation\Http\Resources\TicketResource;
use Illuminate\Http\JsonResponse;

class TicketController extends ApiController
{
    public function __construct(private readonly TicketServiceInterface $ticketService)
    {
    }

    public function store(CreateTicketRequest $request, Event $event): JsonResponse
    {
        $ticket = $this->ticketService->create($event, $request->toDto());

        return $this->created(TicketResource::make($ticket), 'Ticket created successfully');
    }

    public function update(UpdateTicketRequest $request, Ticket $ticket): JsonResponse
    {
        $ticket = $this->ticketService->update($ticket, $request->toDto());

        return $this->success(TicketResource::make($ticket), 'Ticket updated successfully');
    }

    public function destroy(DeleteTicketRequest $request, Ticket $ticket): JsonResponse
    {
        $this->ticketService->delete($ticket);

        return $this->success(null, 'Ticket deleted successfully');
    }
}
