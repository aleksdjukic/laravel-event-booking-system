<?php

namespace App\Application\Services\Payment;

use App\Application\Payment\Actions\FindPaymentAction;
use App\Application\Payment\Actions\ProcessPaymentAction;
use App\Application\Contracts\Services\PaymentTransactionServiceInterface;
use App\Application\Payment\DTO\CreatePaymentData;
use App\Domain\Payment\Models\Payment;
use App\Domain\User\Models\User;

class PaymentTransactionService implements PaymentTransactionServiceInterface
{
    public function __construct(
        private readonly ProcessPaymentAction $processPaymentAction,
        private readonly FindPaymentAction $findPaymentAction,
    ) {
    }

    public function findOrFail(int $id): Payment
    {
        return $this->findPaymentAction->execute($id);
    }

    public function process(User $user, CreatePaymentData $data): Payment
    {
        return $this->processPaymentAction->execute($user, $data);
    }
}
