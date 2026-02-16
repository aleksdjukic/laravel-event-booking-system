<?php

namespace Database\Factories\Modules\Payment;

use App\Modules\Payment\Domain\Enums\PaymentStatus;
use App\Modules\Payment\Domain\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            Payment::COL_BOOKING_ID => fake()->unique()->numberBetween(1, 1000000),
            Payment::COL_AMOUNT => number_format(fake()->randomFloat(2, 10, 500), 2, '.', ''),
            Payment::COL_STATUS => fake()->randomElement([
                PaymentStatus::SUCCESS->value,
                PaymentStatus::FAILED->value,
                PaymentStatus::REFUNDED->value,
            ]),
        ];
    }
}
