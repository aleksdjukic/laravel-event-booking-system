<?php

namespace App\Repositories\Eloquent;

use App\Domain\Payment\Repositories\PaymentIdempotencyRepositoryInterface;
use App\Domain\Payment\Models\PaymentIdempotencyKey;

class PaymentIdempotencyRepository implements PaymentIdempotencyRepositoryInterface
{
    public function findForUserByKey(int $userId, string $idempotencyKey): ?PaymentIdempotencyKey
    {
        return PaymentIdempotencyKey::query()
            ->where('user_id', $userId)
            ->where('idempotency_key', $idempotencyKey)
            ->first();
    }

    public function createPending(int $userId, int $bookingId, string $idempotencyKey): PaymentIdempotencyKey
    {
        return PaymentIdempotencyKey::query()->firstOrCreate(
            [
                'user_id' => $userId,
                'idempotency_key' => $idempotencyKey,
            ],
            [
                'booking_id' => $bookingId,
                'payment_id' => null,
            ]
        );
    }

    public function attachPayment(PaymentIdempotencyKey $record, int $paymentId): PaymentIdempotencyKey
    {
        $record->payment_id = $paymentId;
        $record->save();

        return $record;
    }
}
