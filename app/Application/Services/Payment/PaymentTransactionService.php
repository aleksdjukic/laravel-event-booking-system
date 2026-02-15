<?php

namespace App\Application\Services\Payment;

use App\Application\Payment\Actions\ProcessPaymentAction;
use App\Application\Contracts\Services\PaymentTransactionServiceInterface;
use App\Application\Payment\DTO\CreatePaymentData;
use App\Domain\Payment\Models\Payment;
use App\Domain\User\Models\User;

class PaymentTransactionService implements PaymentTransactionServiceInterface
{
    public function __construct(
        private readonly ProcessPaymentAction $processPaymentAction,
    ) {
    }

    public function process(User $user, CreatePaymentData $data): Payment
    {
        return $this->processPaymentAction->execute($user, $data);
    }
}
