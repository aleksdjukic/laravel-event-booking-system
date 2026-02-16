<?php

namespace App\Modules\Payment\Application\Contracts;

use App\Modules\Payment\Application\DTO\CreatePaymentData;
use App\Domain\Payment\Models\Payment;
use App\Domain\User\Models\User;

interface PaymentTransactionServiceInterface
{
    public function process(User $user, CreatePaymentData $data): Payment;
}
