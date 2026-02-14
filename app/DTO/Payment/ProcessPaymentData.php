<?php

namespace App\DTO\Payment;

class ProcessPaymentData
{
    public function __construct(
        public readonly int $bookingId,
        public readonly ?bool $forceSuccess,
    ) {
    }

    public static function fromInput(int $bookingId, ?bool $forceSuccess): self
    {
        return new self(
            bookingId: $bookingId,
            forceSuccess: $forceSuccess,
        );
    }
}
