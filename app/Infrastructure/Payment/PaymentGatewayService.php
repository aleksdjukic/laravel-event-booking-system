<?php

namespace App\Infrastructure\Payment;

use App\Domain\Booking\Models\Booking;
use App\Domain\Payment\Services\PaymentGatewayInterface;

class PaymentGatewayService implements PaymentGatewayInterface
{
    private const SIMULATED_SUCCESS_RATE = 80;

    public function process(Booking $booking, ?bool $forceSuccess = null): bool
    {
        if ($forceSuccess !== null) {
            return $forceSuccess;
        }

        return $this->simulateGatewayResult($booking);
    }

    private function simulateGatewayResult(Booking $booking): bool
    {
        $seed = crc32(implode('|', [
            (string) $booking->id,
            (string) $booking->ticket_id,
            (string) $booking->user_id,
            (string) $booking->quantity,
        ]));

        return ($seed % 100) < self::SIMULATED_SUCCESS_RATE;
    }
}
