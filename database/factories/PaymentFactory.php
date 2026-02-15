<?php

namespace Database\Factories;

use App\Domain\Payment\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Payment\Models\Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = \App\Domain\Payment\Models\Payment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_id' => fake()->unique()->numberBetween(1, 1000000),
            'amount' => number_format(fake()->randomFloat(2, 10, 500), 2, '.', ''),
            'status' => fake()->randomElement([
                PaymentStatus::SUCCESS->value,
                PaymentStatus::FAILED->value,
                PaymentStatus::REFUNDED->value,
            ]),
        ];
    }
}
