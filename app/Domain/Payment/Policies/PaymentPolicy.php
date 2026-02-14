<?php

namespace App\Domain\Payment\Policies;

use App\Domain\User\Enums\Role;
use App\Domain\Payment\Models\Payment;
use App\Domain\User\Models\User;

class PaymentPolicy
{
    public function view(User $user, Payment $payment): bool
    {
        $userRole = $user->role instanceof Role ? $user->role->value : (string) $user->role;

        return $userRole === Role::ADMIN->value || $payment->booking->user_id === $user->id;
    }
}
