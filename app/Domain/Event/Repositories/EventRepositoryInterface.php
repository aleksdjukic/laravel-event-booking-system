<?php

namespace App\Domain\Event\Repositories;

use App\Application\Event\DTO\CreateEventData;
use App\Application\Event\DTO\EventIndexData;
use App\Application\Event\DTO\UpdateEventData;
use App\Domain\Event\Models\Event;
use App\Domain\User\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface EventRepositoryInterface
{
    /**
     * @return LengthAwarePaginator<int, Event>
     */
    public function paginate(EventIndexData $query): LengthAwarePaginator;

    public function find(int $id): ?Event;

    public function findWithTickets(int $id): ?Event;

    public function create(User $user, CreateEventData $data): Event;

    public function update(Event $event, UpdateEventData $data): Event;

    public function delete(Event $event): void;
}
