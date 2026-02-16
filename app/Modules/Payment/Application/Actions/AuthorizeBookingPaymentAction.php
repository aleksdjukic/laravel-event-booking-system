<?php

namespace App\Modules\Payment\Application\Actions;

use App\Domain\Booking\Models\Booking;
use App\Domain\Shared\DomainError;
use App\Domain\Shared\DomainException;
use App\Domain\User\Enums\Role;
use App\Domain\User\Models\User;

class AuthorizeBookingPaymentAction
{
    public function execute(User $user, Booking $booking): void
    {
        if ($user->hasRole(Role::CUSTOMER) && $booking->user_id !== $user->id) {
            throw new DomainException(DomainError::FORBIDDEN);
        }
    }
}
