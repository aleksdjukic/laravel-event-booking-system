<?php

namespace Tests\Feature\Modules\Booking;

use App\Modules\Booking\Application\DTO\CreateBookingData;
use App\Modules\Booking\Domain\Enums\BookingStatus;
use App\Modules\Booking\Domain\Models\Booking;
use App\Modules\Event\Domain\Models\Event;
use App\Modules\Ticket\Domain\Models\Ticket;
use App\Modules\User\Domain\Enums\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class BookingFeatureTest extends TestCase
{
    use RefreshDatabase;
    use CreatesUsers;

    public function test_customer_can_create_booking_when_inventory_is_enough(): void
    {
        $customer = $this->createUser(Role::CUSTOMER, 'booking.customer.create@example.com');
        $ticket = $this->createTicket(5);

        Sanctum::actingAs($customer);

        $this->postJson('/api/v1/tickets/'.$ticket->{Ticket::COL_ID}.'/bookings', [
            CreateBookingData::INPUT_QUANTITY => 3,
        ])->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', BookingStatus::PENDING->value);
    }

    public function test_double_booking_returns_409_for_same_user_and_ticket(): void
    {
        $customer = $this->createUser(Role::CUSTOMER, 'booking.customer.double@example.com');
        $ticket = $this->createTicket(8);

        Sanctum::actingAs($customer);

        $this->postJson('/api/v1/tickets/'.$ticket->{Ticket::COL_ID}.'/bookings', [
            CreateBookingData::INPUT_QUANTITY => 2,
        ])->assertStatus(201);

        $this->postJson('/api/v1/tickets/'.$ticket->{Ticket::COL_ID}.'/bookings', [
            CreateBookingData::INPUT_QUANTITY => 1,
        ])->assertStatus(409)
            ->assertJsonPath('success', false);
    }

    public function test_bookings_list_customer_sees_own_admin_sees_all_organizer_forbidden(): void
    {
        $customerA = $this->createUser(Role::CUSTOMER, 'booking.customer.a@example.com');
        $customerB = $this->createUser(Role::CUSTOMER, 'booking.customer.b@example.com');
        $admin = $this->createUser(Role::ADMIN, 'booking.admin@example.com');
        $organizer = $this->createUser(Role::ORGANIZER, 'booking.organizer@example.com');

        $ticket = $this->createTicket(20);

        $bookingA = new Booking();
        $bookingA->{Booking::COL_USER_ID} = $customerA->{\App\Modules\User\Domain\Models\User::COL_ID};
        $bookingA->{Booking::COL_TICKET_ID} = $ticket->{Ticket::COL_ID};
        $bookingA->{Booking::COL_QUANTITY} = 2;
        $bookingA->{Booking::COL_STATUS} = BookingStatus::PENDING;
        $bookingA->save();

        $bookingB = new Booking();
        $bookingB->{Booking::COL_USER_ID} = $customerB->{\App\Modules\User\Domain\Models\User::COL_ID};
        $bookingB->{Booking::COL_TICKET_ID} = $ticket->{Ticket::COL_ID};
        $bookingB->{Booking::COL_QUANTITY} = 1;
        $bookingB->{Booking::COL_STATUS} = BookingStatus::PENDING;
        $bookingB->save();

        Sanctum::actingAs($customerA);
        $customerResponse = $this->getJson('/api/v1/bookings');
        $customerResponse->assertStatus(200)
            ->assertJsonPath('success', true);
        $this->assertCount(1, $customerResponse->json('data.data'));
        $this->assertSame($bookingA->{Booking::COL_ID}, $customerResponse->json('data.data.0.id'));

        Sanctum::actingAs($admin);
        $adminResponse = $this->getJson('/api/v1/bookings');
        $adminResponse->assertStatus(200)
            ->assertJsonPath('success', true);
        $this->assertCount(2, $adminResponse->json('data.data'));

        Sanctum::actingAs($organizer);
        $this->getJson('/api/v1/bookings')->assertStatus(403);
    }

    public function test_cancel_rules_for_customer_and_admin(): void
    {
        $customer = $this->createUser(Role::CUSTOMER, 'booking.cancel.customer@example.com');
        $admin = $this->createUser(Role::ADMIN, 'booking.cancel.admin@example.com');
        $ticket = $this->createTicket(30);

        $pendingBooking = new Booking();
        $pendingBooking->{Booking::COL_USER_ID} = $customer->{\App\Modules\User\Domain\Models\User::COL_ID};
        $pendingBooking->{Booking::COL_TICKET_ID} = $ticket->{Ticket::COL_ID};
        $pendingBooking->{Booking::COL_QUANTITY} = 2;
        $pendingBooking->{Booking::COL_STATUS} = BookingStatus::PENDING;
        $pendingBooking->save();

        Sanctum::actingAs($customer);
        $this->putJson('/api/v1/bookings/'.$pendingBooking->{Booking::COL_ID}.'/cancel')
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', BookingStatus::CANCELLED->value);

        $confirmedBooking = new Booking();
        $confirmedBooking->{Booking::COL_USER_ID} = $customer->{\App\Modules\User\Domain\Models\User::COL_ID};
        $confirmedBooking->{Booking::COL_TICKET_ID} = $ticket->{Ticket::COL_ID};
        $confirmedBooking->{Booking::COL_QUANTITY} = 1;
        $confirmedBooking->{Booking::COL_STATUS} = BookingStatus::CONFIRMED;
        $confirmedBooking->save();

        Sanctum::actingAs($customer);
        $this->putJson('/api/v1/bookings/'.$confirmedBooking->{Booking::COL_ID}.'/cancel')
            ->assertStatus(409)
            ->assertJsonPath('success', false);

        $secondTicket = $this->createTicket(10);

        $adminPendingBooking = new Booking();
        $adminPendingBooking->{Booking::COL_USER_ID} = $customer->{\App\Modules\User\Domain\Models\User::COL_ID};
        $adminPendingBooking->{Booking::COL_TICKET_ID} = $secondTicket->{Ticket::COL_ID};
        $adminPendingBooking->{Booking::COL_QUANTITY} = 1;
        $adminPendingBooking->{Booking::COL_STATUS} = BookingStatus::PENDING;
        $adminPendingBooking->save();

        Sanctum::actingAs($admin);
        $this->putJson('/api/v1/bookings/'.$adminPendingBooking->{Booking::COL_ID}.'/cancel')
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', BookingStatus::CANCELLED->value);
    }

    private function createTicket(int $quantity): Ticket
    {
        static $organizerIndex = 0;
        $organizerIndex++;

        $organizer = $this->createUser(Role::ORGANIZER, 'booking.organizer.'.$organizerIndex.'@example.com');

        $event = new Event();
        $event->{Event::COL_TITLE} = 'Booking Event';
        $event->{Event::COL_DESCRIPTION} = null;
        $event->{Event::COL_DATE} = '2026-09-01 12:00:00';
        $event->{Event::COL_LOCATION} = 'Belgrade';
        $event->{Event::COL_CREATED_BY} = $organizer->{\App\Modules\User\Domain\Models\User::COL_ID};
        $event->save();

        $ticket = new Ticket();
        $ticket->{Ticket::COL_EVENT_ID} = $event->{Event::COL_ID};
        $ticket->{Ticket::COL_TYPE} = 'Standard';
        $ticket->{Ticket::COL_PRICE} = 50.00;
        $ticket->{Ticket::COL_QUANTITY} = $quantity;
        $ticket->save();

        return $ticket;
    }
}
