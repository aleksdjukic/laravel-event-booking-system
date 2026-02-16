<?php

namespace Database\Factories\Modules\Event;

use App\Modules\Event\Domain\Models\Event;
use App\Modules\User\Domain\Models\User;
use Database\Factories\Modules\User\UserFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    protected $model = Event::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            Event::COL_TITLE => fake()->sentence(3),
            Event::COL_DESCRIPTION => fake()->boolean(70) ? fake()->sentence(10) : null,
            Event::COL_DATE => fake()->dateTimeBetween('+1 day', '+6 months'),
            Event::COL_LOCATION => fake()->city(),
            Event::COL_CREATED_BY => UserFactory::new()->organizer(),
        ];
    }
}
