<?php

namespace App\Modules\Event\Application\Actions;

use App\Domain\Event\Models\Event;
use App\Domain\Event\Repositories\EventRepositoryInterface;

class DeleteEventAction
{
    public function __construct(private readonly EventRepositoryInterface $eventRepository)
    {
    }

    public function execute(Event $event): void
    {
        $this->eventRepository->delete($event);
    }
}
