<?php

namespace App\Application\Ticket\Actions;

use App\Domain\Shared\DomainError;
use App\Domain\Shared\DomainException;
use App\Domain\Ticket\Models\Ticket;
use App\Domain\Ticket\Repositories\TicketRepositoryInterface;

class FindTicketAction
{
    public function __construct(private readonly TicketRepositoryInterface $ticketRepository)
    {
    }

    public function execute(int $id): Ticket
    {
        $ticket = $this->ticketRepository->find($id);

        if ($ticket === null) {
            throw new DomainException(DomainError::TICKET_NOT_FOUND);
        }

        return $ticket;
    }
}
