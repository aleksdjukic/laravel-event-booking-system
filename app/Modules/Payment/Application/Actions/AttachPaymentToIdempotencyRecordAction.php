<?php

namespace App\Modules\Payment\Application\Actions;

use App\Domain\Payment\Models\PaymentIdempotencyKey;
use App\Domain\Payment\Repositories\PaymentIdempotencyRepositoryInterface;

class AttachPaymentToIdempotencyRecordAction
{
    public function __construct(private readonly PaymentIdempotencyRepositoryInterface $idempotencyRepository)
    {
    }

    public function execute(PaymentIdempotencyKey $idempotencyRecord, int $paymentId): void
    {
        $this->idempotencyRepository->attachPayment($idempotencyRecord, $paymentId);
    }
}
