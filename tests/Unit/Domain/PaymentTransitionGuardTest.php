<?php

namespace Tests\Unit\Domain;

use App\Domain\Payment\PaymentTransitionGuard;
use App\Enums\PaymentStatus;
use PHPUnit\Framework\TestCase;

class PaymentTransitionGuardTest extends TestCase
{
    public function test_only_successful_payment_can_trigger_customer_notification(): void
    {
        $guard = new PaymentTransitionGuard();

        $this->assertTrue($guard->canNotifyCustomer(PaymentStatus::SUCCESS));
        $this->assertFalse($guard->canNotifyCustomer(PaymentStatus::FAILED));
        $this->assertFalse($guard->canNotifyCustomer(PaymentStatus::REFUNDED));
    }
}
