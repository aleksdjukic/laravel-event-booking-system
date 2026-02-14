<?php

namespace Tests\Feature\Api\V1;

use App\Domain\Booking\Models\Booking;
use App\Domain\Event\Models\Event;
use App\Domain\Ticket\Models\Ticket;
use App\Domain\User\Models\User;
use App\Notifications\BookingConfirmedNotification;
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
        $customer->name = 'Customer';
        $customer->email = 'customer.payment@example.com';
        $customer->password = Hash::make('password123');
        $customer->role = 'customer';
        $customer->save();

        $organizer = new User();
        $organizer->name = 'Organizer';
        $organizer->email = 'organizer.payment@example.com';
        $organizer->password = Hash::make('password123');
        $organizer->role = 'organizer';
        $organizer->save();

        $event = new Event();
        $event->title = 'Payment Event';
        $event->description = null;
        $event->date = '2026-07-01 10:00:00';
        $event->location = 'Belgrade';
        $event->created_by = $organizer->id;
        $event->save();

        $ticket = new Ticket();
        $ticket->event_id = $event->id;
        $ticket->type = 'Standard';
        $ticket->price = 100.00;
        $ticket->quantity = 10;
        $ticket->save();

        $booking = new Booking();
        $booking->user_id = $customer->id;
        $booking->ticket_id = $ticket->id;
        $booking->quantity = 2;
        $booking->status = 'pending';
        $booking->save();

        Sanctum::actingAs($customer);

        $response = $this->postJson('/api/v1/bookings/'.$booking->id.'/payment', [
            'force_success' => true,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'success');

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'confirmed',
        ]);

        $this->assertDatabaseHas('payments', [
            'booking_id' => $booking->id,
            'status' => 'success',
            'amount' => '200.00',
        ]);

        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'quantity' => 8,
        ]);

        Notification::assertSentTo($customer, BookingConfirmedNotification::class);
    }
}
