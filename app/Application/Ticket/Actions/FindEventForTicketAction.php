<?php

namespace App\Application\Ticket\Actions;

use App\Application\Event\Actions\FindEventAction;
use App\Domain\Event\Models\Event;

class FindEventForTicketAction
{
    public function __construct(private readonly FindEventAction $findEventAction)
    {
    }

    public function execute(int $eventId): Event
    {
        return $this->findEventAction->execute($eventId);
    }
}
