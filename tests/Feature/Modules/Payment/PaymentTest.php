<?php

namespace Tests\Feature\Modules\Payment;

use App\Modules\Booking\Domain\Enums\BookingStatus;
use App\Modules\Booking\Domain\Models\Booking;
use App\Modules\Event\Domain\Models\Event;
use App\Modules\Payment\Domain\Enums\PaymentStatus;
use App\Modules\Payment\Domain\Models\Payment;
use App\Modules\Payment\Presentation\Http\Requests\CreatePaymentRequest;
use App\Modules\Ticket\Domain\Models\Ticket;
use App\Modules\User\Domain\Enums\Role;
use App\Modules\User\Domain\Models\User;
use App\Modules\Booking\Infrastructure\Notifications\BookingConfirmedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_pay_pending_booking_successfully(): void
    {
        Notification::fake();

        $customer = new User();
        $customer->{User::COL_NAME} = 'Customer';
        $customer->{User::COL_EMAIL} = 'customer.payment@example.com';
        $customer->{User::COL_PASSWORD} = Hash::make('password123');
        $customer->{User::COL_ROLE} = Role::CUSTOMER;
        $customer->save();

        $organizer = new User();
        $organizer->{User::COL_NAME} = 'Organizer';
        $organizer->{User::COL_EMAIL} = 'organizer.payment@example.com';
        $organizer->{User::COL_PASSWORD} = Hash::make('password123');
        $organizer->{User::COL_ROLE} = Role::ORGANIZER;
        $organizer->save();

        $event = new Event();
        $event->{Event::COL_TITLE} = 'Payment Event';
        $event->{Event::COL_DESCRIPTION} = null;
        $event->{Event::COL_DATE} = '2026-07-01 10:00:00';
        $event->{Event::COL_LOCATION} = 'Belgrade';
        $event->{Event::COL_CREATED_BY} = $organizer->{User::COL_ID};
        $event->save();

        $ticket = new Ticket();
        $ticket->{Ticket::COL_EVENT_ID} = $event->{Event::COL_ID};
        $ticket->{Ticket::COL_TYPE} = 'Standard';
        $ticket->{Ticket::COL_PRICE} = 100.00;
        $ticket->{Ticket::COL_QUANTITY} = 10;
        $ticket->save();

        $booking = new Booking();
        $booking->{Booking::COL_USER_ID} = $customer->{User::COL_ID};
        $booking->{Booking::COL_TICKET_ID} = $ticket->{Ticket::COL_ID};
        $booking->{Booking::COL_QUANTITY} = 2;
        $booking->{Booking::COL_STATUS} = BookingStatus::PENDING;
        $booking->save();

        Sanctum::actingAs($customer);

        $response = $this->postJson('/api/v1/bookings/'.$booking->{Booking::COL_ID}.'/payment', [
            CreatePaymentRequest::INPUT_FORCE_SUCCESS => true,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', PaymentStatus::SUCCESS->value);

        $this->assertDatabaseHas(Booking::TABLE, [
            Booking::COL_ID => $booking->{Booking::COL_ID},
            Booking::COL_STATUS => BookingStatus::CONFIRMED->value,
        ]);

        $this->assertDatabaseHas(Payment::TABLE, [
            Payment::COL_BOOKING_ID => $booking->{Booking::COL_ID},
            Payment::COL_STATUS => PaymentStatus::SUCCESS->value,
            Payment::COL_AMOUNT => '200.00',
        ]);

        $this->assertDatabaseHas(Ticket::TABLE, [
            Ticket::COL_ID => $ticket->{Ticket::COL_ID},
            Ticket::COL_QUANTITY => 8,
        ]);

        Notification::assertSentTo($customer, BookingConfirmedNotification::class);
    }
}
