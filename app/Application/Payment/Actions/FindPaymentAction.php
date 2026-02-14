<?php

namespace App\Application\Payment\Actions;

use App\Domain\Payment\Models\Payment;
use App\Domain\Payment\Repositories\PaymentRepositoryInterface;
use App\Domain\Shared\DomainError;
use App\Domain\Shared\DomainException;

class FindPaymentAction
{
    public function __construct(private readonly PaymentRepositoryInterface $paymentRepository)
    {
    }

    public function execute(int $id): Payment
    {
        $payment = $this->paymentRepository->findWithBooking($id);

        if ($payment === null) {
            throw new DomainException(DomainError::PAYMENT_NOT_FOUND);
        }

        return $payment;
    }
}
