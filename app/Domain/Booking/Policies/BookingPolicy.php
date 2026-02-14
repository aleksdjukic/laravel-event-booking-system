<?php

namespace App\Domain\Booking\Policies;

use App\Domain\User\Enums\Role;
use App\Domain\Booking\Models\Booking;
use App\Domain\User\Models\User;

class BookingPolicy
{
    public function viewAny(User $user): bool
    {
        $userRole = $user->role instanceof Role ? $user->role->value : (string) $user->role;

        return in_array($userRole, [Role::ADMIN->value, Role::CUSTOMER->value], true);
    }

    public function view(User $user, Booking $booking): bool
    {
        $userRole = $user->role instanceof Role ? $user->role->value : (string) $user->role;

        return $userRole === Role::ADMIN->value || $booking->user_id === $user->id;
    }

    public function cancel(User $user, Booking $booking): bool
    {
        $userRole = $user->role instanceof Role ? $user->role->value : (string) $user->role;

        return $userRole === Role::ADMIN->value || $booking->user_id === $user->id;
    }
}
