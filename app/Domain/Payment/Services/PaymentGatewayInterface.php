<?php

namespace App\Domain\Payment\Services;

use App\Domain\Booking\Models\Booking;

interface PaymentGatewayInterface
{
    public function process(Booking $booking, ?bool $forceSuccess = null): bool;
}
