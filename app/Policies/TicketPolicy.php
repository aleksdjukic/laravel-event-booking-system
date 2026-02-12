<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    public function create(User $user, Event $event): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $user->role === 'organizer' && $event->created_by === $user->id;
    }

    public function update(User $user, Ticket $ticket): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $user->role === 'organizer' && $ticket->event->created_by === $user->id;
    }

    public function delete(User $user, Ticket $ticket): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $user->role === 'organizer' && $ticket->event->created_by === $user->id;
    }
}
