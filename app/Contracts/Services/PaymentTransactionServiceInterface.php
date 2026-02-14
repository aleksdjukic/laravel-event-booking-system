<?php

namespace App\Contracts\Services;

use App\DTO\Payment\ProcessPaymentData;
use App\Models\Payment;
use App\Models\User;

interface PaymentTransactionServiceInterface
{
    public function findOrFail(int $id): Payment;

    public function process(User $user, ProcessPaymentData $data): Payment;
}
