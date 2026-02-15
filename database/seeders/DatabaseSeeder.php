<?php

namespace Database\Seeders;

use App\Domain\Booking\Enums\BookingStatus;
use App\Domain\Booking\Models\Booking;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Ticket\Models\Ticket;
use App\Domain\User\Enums\Role;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        fake()->seed(20260212);
        $demoPassword = 'password123';

        // Demo users (for quick Postman testing)
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Demo Admin',
                'phone' => '0609990001',
                'role' => Role::ADMIN->value,
                'password' => Hash::make($demoPassword),
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'organizer@example.com'],
            [
                'name' => 'Demo Organizer',
                'phone' => '0609990002',
                'role' => Role::ORGANIZER->value,
                'password' => Hash::make($demoPassword),
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'customer@example.com'],
            [
                'name' => 'Demo Customer',
                'phone' => '0609990003',
                'role' => Role::CUSTOMER->value,
                'password' => Hash::make($demoPassword),
                'email_verified_at' => now(),
            ]
        );

        User::factory()
            ->count(2)
            ->admin()
            ->sequence(
                ['name' => 'Admin 1', 'email' => 'admin1@example.com', 'phone' => '0600000001'],
                ['name' => 'Admin 2', 'email' => 'admin2@example.com', 'phone' => '0600000002'],
            )
            ->create();

        $organizers = User::factory()
            ->count(3)
            ->organizer()
            ->sequence(
                ['name' => 'Organizer 1', 'email' => 'organizer1@example.com', 'phone' => '0610000001'],
                ['name' => 'Organizer 2', 'email' => 'organizer2@example.com', 'phone' => '0610000002'],
                ['name' => 'Organizer 3', 'email' => 'organizer3@example.com', 'phone' => '0610000003'],
            )
            ->create();

        $customers = User::factory()
            ->count(10)
            ->customer()
            ->sequence(fn (Sequence $sequence) => [
                'name' => 'Customer '.($sequence->index + 1),
                'email' => 'customer'.($sequence->index + 1).'@example.com',
                'phone' => '06200000'.str_pad((string) ($sequence->index + 1), 2, '0', STR_PAD_LEFT),
            ])
            ->create();

        $events = \Database\Factories\EventFactory::new()
            ->count(5)
            ->sequence(
                [
                    'title' => 'Tech Conference 2026',
                    'description' => 'Annual software and cloud conference.',
                    'date' => '2026-06-10 10:00:00',
                    'location' => 'Belgrade',
                    'created_by' => $organizers[0]->id,
                ],
                [
                    'title' => 'Startup Networking Night',
                    'description' => null,
                    'date' => '2026-07-05 19:30:00',
                    'location' => 'Novi Sad',
                    'created_by' => $organizers[1]->id,
                ],
                [
                    'title' => 'Music Open Air',
                    'description' => 'Outdoor live performances.',
                    'date' => '2026-08-12 18:00:00',
                    'location' => 'Nis',
                    'created_by' => $organizers[2]->id,
                ],
                [
                    'title' => 'Business Expo',
                    'description' => 'Exhibition for local businesses.',
                    'date' => '2026-09-20 09:00:00',
                    'location' => 'Belgrade',
                    'created_by' => $organizers[0]->id,
                ],
                [
                    'title' => 'Design Workshop',
                    'description' => 'Hands-on product design workshop.',
                    'date' => '2026-10-02 11:00:00',
                    'location' => 'Kragujevac',
                    'created_by' => $organizers[1]->id,
                ],
            )
            ->create();

        $ticketTypePrice = [
            'VIP' => '120.00',
            'Standard' => '70.00',
            'Regular' => '40.00',
        ];

        $tickets = collect();

        foreach ($events as $event) {
            foreach (['VIP', 'Standard', 'Regular'] as $type) {
                $tickets->push(\Database\Factories\TicketFactory::new()->create([
                    'event_id' => $event->id,
                    'type' => $type,
                    'price' => $ticketTypePrice[$type],
                    'quantity' => 50,
                ]));
            }
        }

        $bookingsPlan = [
            ['ticket_index' => 0, 'customer_index' => 0, 'quantity' => 2, 'status' => BookingStatus::CONFIRMED],
            ['ticket_index' => 1, 'customer_index' => 1, 'quantity' => 1, 'status' => BookingStatus::PENDING],
            ['ticket_index' => 2, 'customer_index' => 2, 'quantity' => 3, 'status' => BookingStatus::CANCELLED],
            ['ticket_index' => 3, 'customer_index' => 3, 'quantity' => 2, 'status' => BookingStatus::CONFIRMED],
            ['ticket_index' => 4, 'customer_index' => 4, 'quantity' => 5, 'status' => BookingStatus::PENDING],
            ['ticket_index' => 5, 'customer_index' => 5, 'quantity' => 1, 'status' => BookingStatus::CONFIRMED],
            ['ticket_index' => 6, 'customer_index' => 6, 'quantity' => 4, 'status' => BookingStatus::CANCELLED],
            ['ticket_index' => 7, 'customer_index' => 7, 'quantity' => 2, 'status' => BookingStatus::PENDING],
            ['ticket_index' => 8, 'customer_index' => 8, 'quantity' => 3, 'status' => BookingStatus::CONFIRMED],
            ['ticket_index' => 9, 'customer_index' => 9, 'quantity' => 1, 'status' => BookingStatus::PENDING],
            ['ticket_index' => 10, 'customer_index' => 0, 'quantity' => 2, 'status' => BookingStatus::CONFIRMED],
            ['ticket_index' => 11, 'customer_index' => 1, 'quantity' => 4, 'status' => BookingStatus::PENDING],
            ['ticket_index' => 12, 'customer_index' => 2, 'quantity' => 1, 'status' => BookingStatus::CANCELLED],
            ['ticket_index' => 13, 'customer_index' => 3, 'quantity' => 3, 'status' => BookingStatus::CONFIRMED],
            ['ticket_index' => 14, 'customer_index' => 4, 'quantity' => 2, 'status' => BookingStatus::PENDING],
            ['ticket_index' => 0, 'customer_index' => 5, 'quantity' => 1, 'status' => BookingStatus::PENDING],
            ['ticket_index' => 4, 'customer_index' => 6, 'quantity' => 2, 'status' => BookingStatus::CONFIRMED],
            ['ticket_index' => 8, 'customer_index' => 7, 'quantity' => 1, 'status' => BookingStatus::PENDING],
            ['ticket_index' => 10, 'customer_index' => 8, 'quantity' => 2, 'status' => BookingStatus::CONFIRMED],
            ['ticket_index' => 14, 'customer_index' => 9, 'quantity' => 3, 'status' => BookingStatus::CANCELLED],
        ];

        $bookings = collect();

        foreach ($bookingsPlan as $plan) {
            $bookings->push(\Database\Factories\BookingFactory::new()->create([
                'user_id' => $customers[$plan['customer_index']]->id,
                'ticket_id' => $tickets[$plan['ticket_index']]->id,
                'quantity' => $plan['quantity'],
                'status' => $plan['status']->value,
            ]));
        }

        $confirmedBookings = $bookings
            ->filter(fn (Booking $booking): bool => $booking->status === BookingStatus::CONFIRMED)
            ->values();

        foreach ($confirmedBookings as $booking) {
            $ticket = $tickets->firstWhere('id', $booking->ticket_id);

            \Database\Factories\PaymentFactory::new()->create([
                'booking_id' => $booking->id,
                'amount' => number_format($booking->quantity * (float) $ticket->price, 2, '.', ''),
                'status' => PaymentStatus::SUCCESS->value,
            ]);
        }

        $cancelledBooking = $bookings->first(
            fn (Booking $booking): bool => $booking->status === BookingStatus::CANCELLED
        );
        if ($cancelledBooking !== null) {
            $cancelledTicket = $tickets->firstWhere('id', $cancelledBooking->ticket_id);

            \Database\Factories\PaymentFactory::new()->create([
                'booking_id' => $cancelledBooking->id,
                'amount' => number_format($cancelledBooking->quantity * (float) $cancelledTicket->price, 2, '.', ''),
                'status' => PaymentStatus::FAILED->value,
            ]);
        }

        $confirmedByTicket = Booking::query()
            ->selectRaw('ticket_id, SUM(quantity) as confirmed_quantity')
            ->where('status', BookingStatus::CONFIRMED->value)
            ->groupBy('ticket_id')
            ->get()
            ->keyBy('ticket_id');

        $tickets->each(function (Ticket $ticket) use ($confirmedByTicket): void {
            $confirmedQuantity = (int) optional($confirmedByTicket->get($ticket->id))->confirmed_quantity;
            $remainingQuantity = max(0, 50 - $confirmedQuantity);

            $ticket->update(['quantity' => $remainingQuantity]);
        });
    }
}
