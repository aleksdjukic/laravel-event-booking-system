<?php

namespace App\Domain\Payment\Repositories;

use App\Models\PaymentIdempotencyKey;

interface PaymentIdempotencyRepositoryInterface
{
    public function findForUserByKey(int $userId, string $idempotencyKey): ?PaymentIdempotencyKey;

    public function createPending(int $userId, int $bookingId, string $idempotencyKey): PaymentIdempotencyKey;

    public function attachPayment(PaymentIdempotencyKey $record, int $paymentId): PaymentIdempotencyKey;
}
