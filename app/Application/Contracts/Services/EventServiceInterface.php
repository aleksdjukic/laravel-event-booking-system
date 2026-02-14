<?php

namespace App\Application\Contracts\Services;

use App\Application\Event\DTO\CreateEventData;
use App\Application\Event\DTO\ListEventsData;
use App\Application\Event\DTO\UpdateEventData;
use App\Domain\Event\Models\Event;
use App\Domain\User\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface EventServiceInterface
{
    /**
     * @return LengthAwarePaginator<int, Event>
     */
    public function index(ListEventsData $query): LengthAwarePaginator;

    public function show(int $id): Event;

    public function findOrFail(int $id): Event;

    public function create(User $user, CreateEventData $data): Event;

    public function update(Event $event, UpdateEventData $data): Event;

    public function delete(Event $event): void;
}
