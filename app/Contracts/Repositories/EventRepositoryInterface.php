<?php

namespace App\Contracts\Repositories;

use App\DTO\Event\CreateEventData;
use App\DTO\Event\EventIndexData;
use App\DTO\Event\UpdateEventData;
use App\Models\Event;
use App\Models\User;
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
