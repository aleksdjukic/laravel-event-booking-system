<?php

namespace Tests\Feature\Api\V1;

use App\Domain\Booking\Models\Booking;
use App\Domain\Event\Models\Event;
use App\Domain\Ticket\Models\Ticket;
use App\Domain\User\Enums\Role;
use App\Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BookingFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_create_booking_when_inventory_is_enough(): void
    {
        $customer = $this->createUser(Role::CUSTOMER, 'booking.customer.create@example.com');
        $ticket = $this->createTicket(5);

        Sanctum::actingAs($customer);

        $this->postJson('/api/v1/tickets/'.$ticket->id.'/bookings', [
            'quantity' => 3,
        ])->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'pending');
    }

    public function test_double_booking_returns_409_for_same_user_and_ticket(): void
    {
        $customer = $this->createUser(Role::CUSTOMER, 'booking.customer.double@example.com');
        $ticket = $this->createTicket(8);

        Sanctum::actingAs($customer);

        $this->postJson('/api/v1/tickets/'.$ticket->id.'/bookings', [
            'quantity' => 2,
        ])->assertStatus(201);

        $this->postJson('/api/v1/tickets/'.$ticket->id.'/bookings', [
            'quantity' => 1,
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
        $bookingA->user_id = $customerA->id;
        $bookingA->ticket_id = $ticket->id;
        $bookingA->quantity = 2;
        $bookingA->status = 'pending';
        $bookingA->save();

        $bookingB = new Booking();
        $bookingB->user_id = $customerB->id;
        $bookingB->ticket_id = $ticket->id;
        $bookingB->quantity = 1;
        $bookingB->status = 'pending';
        $bookingB->save();

        Sanctum::actingAs($customerA);
        $customerResponse = $this->getJson('/api/v1/bookings');
        $customerResponse->assertStatus(200)
            ->assertJsonPath('success', true);
        $this->assertCount(1, $customerResponse->json('data.data'));
        $this->assertSame($bookingA->id, $customerResponse->json('data.data.0.id'));

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
        $pendingBooking->user_id = $customer->id;
        $pendingBooking->ticket_id = $ticket->id;
        $pendingBooking->quantity = 2;
        $pendingBooking->status = 'pending';
        $pendingBooking->save();

        Sanctum::actingAs($customer);
        $this->putJson('/api/v1/bookings/'.$pendingBooking->id.'/cancel')
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'cancelled');

        $confirmedBooking = new Booking();
        $confirmedBooking->user_id = $customer->id;
        $confirmedBooking->ticket_id = $ticket->id;
        $confirmedBooking->quantity = 1;
        $confirmedBooking->status = 'confirmed';
        $confirmedBooking->save();

        Sanctum::actingAs($customer);
        $this->putJson('/api/v1/bookings/'.$confirmedBooking->id.'/cancel')
            ->assertStatus(409)
            ->assertJsonPath('success', false);

        $secondTicket = $this->createTicket(10);

        $adminPendingBooking = new Booking();
        $adminPendingBooking->user_id = $customer->id;
        $adminPendingBooking->ticket_id = $secondTicket->id;
        $adminPendingBooking->quantity = 1;
        $adminPendingBooking->status = 'pending';
        $adminPendingBooking->save();

        Sanctum::actingAs($admin);
        $this->putJson('/api/v1/bookings/'.$adminPendingBooking->id.'/cancel')
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'cancelled');
    }

    private function createUser(Role $role, string $email): User
    {
        $user = new User();
        $user->name = ucfirst($role->value).' User';
        $user->email = $email;
        $user->password = Hash::make('password123');
        $user->role = $role;
        $user->save();

        return $user;
    }

    private function createTicket(int $quantity): Ticket
    {
        static $organizerIndex = 0;
        $organizerIndex++;

        $organizer = $this->createUser(Role::ORGANIZER, 'booking.organizer.'.$organizerIndex.'@example.com');

        $event = new Event();
        $event->title = 'Booking Event';
        $event->description = null;
        $event->date = '2026-09-01 12:00:00';
        $event->location = 'Belgrade';
        $event->created_by = $organizer->id;
        $event->save();

        $ticket = new Ticket();
        $ticket->event_id = $event->id;
        $ticket->type = 'Standard';
        $ticket->price = 50.00;
        $ticket->quantity = $quantity;
        $ticket->save();

        return $ticket;
    }
}
