<?php

namespace App\Domain\Event\Policies;

use App\Domain\User\Enums\Role;
use App\Domain\Event\Models\Event;
use App\Domain\User\Models\User;

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
        $userRole = $user->role instanceof Role ? $user->role->value : (string) $user->role;

        return in_array($userRole, [Role::ADMIN->value, Role::ORGANIZER->value], true);
    }

    public function update(User $user, Event $event): bool
    {
        $userRole = $user->role instanceof Role ? $user->role->value : (string) $user->role;

        if ($userRole === Role::ADMIN->value) {
            return true;
        }

        return $userRole === Role::ORGANIZER->value && $event->created_by === $user->id;
    }

    public function delete(User $user, Event $event): bool
    {
        $userRole = $user->role instanceof Role ? $user->role->value : (string) $user->role;

        if ($userRole === Role::ADMIN->value) {
            return true;
        }

        return $userRole === Role::ORGANIZER->value && $event->created_by === $user->id;
    }
}
