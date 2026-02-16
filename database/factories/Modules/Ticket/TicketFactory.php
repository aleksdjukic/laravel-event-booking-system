<?php

namespace Database\Factories\Modules\Ticket;

use App\Modules\Ticket\Domain\Models\Ticket;
use Database\Factories\Modules\Event\EventFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            Ticket::COL_EVENT_ID => EventFactory::new(),
            Ticket::COL_TYPE => fake()->randomElement(['VIP', 'Standard', 'Regular']),
            Ticket::COL_PRICE => number_format(fake()->randomFloat(2, 15, 250), 2, '.', ''),
            Ticket::COL_QUANTITY => 50,
        ];
    }
}
