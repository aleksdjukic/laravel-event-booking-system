<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    public function viewAny(?User $user = null): bool
    {
        return true;
    }

    public function view(?User $user, Event $event): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'organizer'], true);
    }

    public function update(User $user, Event $event): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $user->role === 'organizer' && $event->created_by === $user->id;
    }

    public function delete(User $user, Event $event): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $user->role === 'organizer' && $event->created_by === $user->id;
    }
}
