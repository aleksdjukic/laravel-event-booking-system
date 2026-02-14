<?php

namespace App\Services\Payment;

use App\Application\Payment\Actions\ProcessPaymentAction;
use App\Contracts\Services\PaymentTransactionServiceInterface;
use App\Domain\Payment\Repositories\PaymentRepositoryInterface;
use App\Domain\Shared\DomainError;
use App\Domain\Shared\DomainException;
use App\Application\Payment\DTO\ProcessPaymentData;
use App\Domain\Payment\Models\Payment;
use App\Domain\User\Models\User;

class PaymentTransactionService implements PaymentTransactionServiceInterface
{
    public function __construct(
        private readonly PaymentRepositoryInterface $paymentRepository,
        private readonly ProcessPaymentAction $processPaymentAction,
    ) {
    }

    public function findOrFail(int $id): Payment
    {
        $payment = $this->paymentRepository->findWithBooking($id);

        if ($payment === null) {
            throw new DomainException(DomainError::PAYMENT_NOT_FOUND);
        }

        return $payment;
    }

    public function process(User $user, ProcessPaymentData $data): Payment
    {
        return $this->processPaymentAction->execute($user, $data);
    }
}
