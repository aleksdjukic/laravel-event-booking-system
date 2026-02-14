<?php

namespace App\Domain\Payment;

use App\Domain\Payment\Enums\PaymentStatus;

class PaymentTransitionGuard
{
    public function canNotifyCustomer(PaymentStatus $status): bool
    {
        return $status === PaymentStatus::SUCCESS;
    }
}
