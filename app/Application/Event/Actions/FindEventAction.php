<?php

namespace App\Application\Event\Actions;

use App\Domain\Event\Models\Event;
use App\Domain\Event\Repositories\EventRepositoryInterface;
use App\Domain\Shared\DomainError;
use App\Domain\Shared\DomainException;

class FindEventAction
{
    public function __construct(private readonly EventRepositoryInterface $eventRepository)
    {
    }

    public function execute(int $id): Event
    {
        $event = $this->eventRepository->find($id);

        if ($event === null) {
            throw new DomainException(DomainError::EVENT_NOT_FOUND);
        }

        return $event;
    }
}
