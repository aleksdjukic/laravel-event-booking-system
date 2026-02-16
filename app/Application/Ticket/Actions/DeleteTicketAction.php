<?php

namespace App\Application\Ticket\Actions;

use App\Modules\Event\Application\Actions\BumpEventIndexVersionAction;
use App\Domain\Ticket\Models\Ticket;
use App\Domain\Ticket\Repositories\TicketRepositoryInterface;

class DeleteTicketAction
{
    public function __construct(
        private readonly TicketRepositoryInterface $ticketRepository,
        private readonly BumpEventIndexVersionAction $bumpEventIndexVersion,
    ) {
    }

    public function execute(Ticket $ticket): void
    {
        $this->ticketRepository->delete($ticket);
        $this->bumpEventIndexVersion->execute();
    }
}
