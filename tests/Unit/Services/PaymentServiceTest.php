<?php

namespace Tests\Unit\Services;

use App\Models\Booking;
use App\Services\Payment\PaymentGatewayService;
use PHPUnit\Framework\TestCase;

class PaymentServiceTest extends TestCase
{
    public function test_process_returns_forced_true_when_override_is_true(): void
    {
        $service = new PaymentGatewayService();
        $booking = new Booking();

        $this->assertTrue($service->process($booking, true));
    }

    public function test_process_returns_false_when_force_success_is_false(): void
    {
        $service = new PaymentGatewayService();
        $booking = new Booking();

        $this->assertFalse($service->process($booking, false));
    }

    public function test_process_is_deterministic_when_override_is_missing(): void
    {
        $service = new PaymentGatewayService();
        $booking = new Booking();
        $booking->id = 77;
        $booking->ticket_id = 10;
        $booking->user_id = 5;
        $booking->quantity = 2;

        $first = $service->process($booking);
        $second = $service->process($booking);

        $this->assertSame($first, $second);
    }
}
