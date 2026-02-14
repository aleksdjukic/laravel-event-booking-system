<?php

namespace App\Contracts\Services;

use App\DTO\Event\CreateEventData;
use App\DTO\Event\EventIndexData;
use App\DTO\Event\UpdateEventData;
use App\Models\Event;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface EventServiceInterface
{
    public function index(EventIndexData $query): LengthAwarePaginator;

    public function show(int $id): Event;

    public function findOrFail(int $id): Event;

    public function create(User $user, CreateEventData $data): Event;

    public function update(Event $event, UpdateEventData $data): Event;

    public function delete(Event $event): void;
}
