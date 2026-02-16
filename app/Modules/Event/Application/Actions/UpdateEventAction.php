<?php

namespace App\Modules\Event\Application\Actions;

use App\Modules\Event\Application\DTO\UpdateEventData;
use App\Domain\Event\Models\Event;
use App\Domain\Event\Repositories\EventRepositoryInterface;

class UpdateEventAction
{
    public function __construct(private readonly EventRepositoryInterface $eventRepository)
    {
    }

    public function execute(Event $event, UpdateEventData $data): Event
    {
        return $this->eventRepository->update($event, $data->title, $data->description, $data->date, $data->location);
    }
}
