<?php

namespace Database\Factories;

use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Booking\Models\Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->customer(),
            'ticket_id' => TicketFactory::new(),
            'quantity' => fake()->numberBetween(1, 5),
            'status' => fake()->randomElement([
                'pending',
                'pending',
                'confirmed',
                'cancelled',
            ]),
        ];
    }
}
