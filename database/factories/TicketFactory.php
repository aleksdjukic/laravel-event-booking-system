<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Ticket\Models\Ticket>
 */
class TicketFactory extends Factory
{
    protected $model = \App\Domain\Ticket\Models\Ticket::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => EventFactory::new(),
            'type' => fake()->randomElement(['VIP', 'Standard', 'Regular']),
            'price' => number_format(fake()->randomFloat(2, 15, 250), 2, '.', ''),
            'quantity' => 50,
        ];
    }
}
