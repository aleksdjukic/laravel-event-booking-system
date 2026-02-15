<?php

namespace Database\Factories;

use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Event\Models\Event>
 */
class EventFactory extends Factory
{
    protected $model = \App\Domain\Event\Models\Event::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'description' => fake()->boolean(70) ? fake()->sentence(10) : null,
            'date' => fake()->dateTimeBetween('+1 day', '+6 months'),
            'location' => fake()->city(),
            'created_by' => User::factory()->organizer(),
        ];
    }
}
