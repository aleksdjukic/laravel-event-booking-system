<?php

namespace App\Domain\Ticket\Policies;

use App\Domain\User\Enums\Role;
use App\Domain\Event\Models\Event;
use App\Domain\Ticket\Models\Ticket;
use App\Domain\User\Models\User;

class TicketPolicy
{
    public function create(User $user, Event $event): bool
    {
        $userRole = $user->role instanceof Role ? $user->role->value : (string) $user->role;

        if ($userRole === Role::ADMIN->value) {
            return true;
        }

        return $userRole === Role::ORGANIZER->value && $event->created_by === $user->id;
    }

    public function update(User $user, Ticket $ticket): bool
    {
        $userRole = $user->role instanceof Role ? $user->role->value : (string) $user->role;

        if ($userRole === Role::ADMIN->value) {
            return true;
        }

        return $userRole === Role::ORGANIZER->value && $ticket->event->created_by === $user->id;
    }

    public function delete(User $user, Ticket $ticket): bool
    {
        $userRole = $user->role instanceof Role ? $user->role->value : (string) $user->role;

        if ($userRole === Role::ADMIN->value) {
            return true;
        }

        return $userRole === Role::ORGANIZER->value && $ticket->event->created_by === $user->id;
    }
}
