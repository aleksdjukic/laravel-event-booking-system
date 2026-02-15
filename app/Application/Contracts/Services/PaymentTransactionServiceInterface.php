<?php

namespace App\Application\Contracts\Services;

use App\Application\Payment\DTO\CreatePaymentData;
use App\Domain\Payment\Models\Payment;
use App\Domain\User\Models\User;

interface PaymentTransactionServiceInterface
{
    public function process(User $user, CreatePaymentData $data): Payment;
}
