<?php

namespace Database\Factories\Modules\Booking;

use App\Modules\Booking\Domain\Enums\BookingStatus;
use App\Modules\Booking\Domain\Models\Booking;
use Database\Factories\Modules\Ticket\TicketFactory;
use Database\Factories\Modules\User\UserFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    protected $model = Booking::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            Booking::COL_USER_ID => UserFactory::new()->customer(),
            Booking::COL_TICKET_ID => TicketFactory::new(),
            Booking::COL_QUANTITY => fake()->numberBetween(1, 5),
            Booking::COL_STATUS => fake()->randomElement([
                BookingStatus::PENDING->value,
                BookingStatus::PENDING->value,
                BookingStatus::CONFIRMED->value,
                BookingStatus::CANCELLED->value,
            ]),
        ];
    }
}
