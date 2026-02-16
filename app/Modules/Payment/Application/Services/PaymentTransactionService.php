<?php

namespace App\Modules\Payment\Application\Services;

use App\Modules\Payment\Application\Actions\ProcessPaymentAction;
use App\Modules\Payment\Application\Contracts\PaymentTransactionServiceInterface;
use App\Modules\Payment\Application\DTO\CreatePaymentData;
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
