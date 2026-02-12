<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'customer'], true);
    }

    public function view(User $user, Booking $booking): bool
    {
        return $user->role === 'admin' || $booking->user_id === $user->id;
    }

    public function cancel(User $user, Booking $booking): bool
    {
        return $user->role === 'admin' || $booking->user_id === $user->id;
    }
}
