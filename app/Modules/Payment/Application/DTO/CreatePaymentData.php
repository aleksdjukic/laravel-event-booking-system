<?php

namespace App\Modules\Payment\Application\DTO;

class CreatePaymentData
{
    public function __construct(
        public readonly int $bookingId,
        public readonly ?bool $forceSuccess,
        public readonly ?string $idempotencyKey,
    ) {
    }

    public static function fromInput(int $bookingId, ?bool $forceSuccess, ?string $idempotencyKey): self
    {
        return new self(
            bookingId: $bookingId,
            forceSuccess: $forceSuccess,
            idempotencyKey: $idempotencyKey,
        );
    }
}
