<?php

namespace Tests\Feature\Api\V1;

use App\Domain\Booking\Enums\BookingStatus;
use App\Domain\Booking\Models\Booking;
use App\Domain\Event\Models\Event;
use App\Domain\Payment\Enums\PaymentStatus;
use App\Domain\Ticket\Models\Ticket;
use App\Domain\User\Enums\Role;
use App\Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class PaymentEdgeFeatureTest extends TestCase
{
    use RefreshDatabase;
    use CreatesUsers;

    public function test_duplicate_payment_returns_409(): void
    {
        Notification::fake();

        $customer = $this->createUser(Role::CUSTOMER, 'payment.duplicate.customer@example.com');
        $booking = $this->createPendingBooking($customer, 10, 2);

        Sanctum::actingAs($customer);

        $this->postJson('/api/v1/bookings/'.$booking->id.'/payment', [
            'force_success' => true,
        ])->assertStatus(201);

        $this->postJson('/api/v1/bookings/'.$booking->id.'/payment', [
            'force_success' => true,
        ])->assertStatus(409)
            ->assertJsonPath('success', false);
    }

    public function test_sold_out_and_not_enough_inventory_return_409(): void
    {
        Notification::fake();

        $customer = $this->createUser(Role::CUSTOMER, 'payment.inventory.customer@example.com');

        $soldOutBooking = $this->createPendingBooking($customer, 0, 1);
        $notEnoughBooking = $this->createPendingBooking($customer, 1, 2);

        Sanctum::actingAs($customer);

        $this->postJson('/api/v1/bookings/'.$soldOutBooking->id.'/payment', [
            'force_success' => true,
        ])->assertStatus(409)
            ->assertJsonPath('message', 'Ticket is sold out.');

        $this->postJson('/api/v1/bookings/'.$notEnoughBooking->id.'/payment', [
            'force_success' => true,
        ])->assertStatus(409)
            ->assertJsonPath('message', 'Not enough ticket inventory.');
    }

    public function test_customer_cannot_pay_another_customers_booking(): void
    {
        Notification::fake();

        $customerA = $this->createUser(Role::CUSTOMER, 'payment.customer.a@example.com');
        $customerB = $this->createUser(Role::CUSTOMER, 'payment.customer.b@example.com');

        $booking = $this->createPendingBooking($customerA, 10, 2);

        Sanctum::actingAs($customerB);

        $this->postJson('/api/v1/bookings/'.$booking->id.'/payment', [
            'force_success' => true,
        ])->assertStatus(403)
            ->assertJsonPath('success', false);
    }

    public function test_payment_force_success_false_cancels_booking_and_keeps_inventory(): void
    {
        Notification::fake();

        $customer = $this->createUser(Role::CUSTOMER, 'payment.force.fail.customer@example.com');
        $booking = $this->createPendingBooking($customer, 7, 3);

        $ticketId = $booking->ticket_id;

        Sanctum::actingAs($customer);

        $this->postJson('/api/v1/bookings/'.$booking->id.'/payment', [
            'force_success' => false,
        ])->assertStatus(201)
            ->assertJsonPath('data.status', PaymentStatus::FAILED->value);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => BookingStatus::CANCELLED->value,
        ]);

        $this->assertDatabaseHas('payments', [
            'booking_id' => $booking->id,
            'status' => PaymentStatus::FAILED->value,
            'amount' => '240.00',
        ]);

        $this->assertDatabaseHas('tickets', [
            'id' => $ticketId,
            'quantity' => 7,
        ]);
    }

    public function test_payment_show_policy_enforced_for_customer_and_admin(): void
    {
        Notification::fake();

        $customerA = $this->createUser(Role::CUSTOMER, 'payment.show.customer.a@example.com');
        $customerB = $this->createUser(Role::CUSTOMER, 'payment.show.customer.b@example.com');
        $admin = $this->createUser(Role::ADMIN, 'payment.show.admin@example.com');

        $booking = $this->createPendingBooking($customerA, 10, 2);

        Sanctum::actingAs($customerA);
        $paymentResponse = $this->postJson('/api/v1/bookings/'.$booking->id.'/payment', [
            'force_success' => true,
        ])->assertStatus(201);

        $paymentId = (int) $paymentResponse->json('data.id');

        Sanctum::actingAs($customerB);
        $this->getJson('/api/v1/payments/'.$paymentId)->assertStatus(403);

        Sanctum::actingAs($admin);
        $this->getJson('/api/v1/payments/'.$paymentId)
            ->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $paymentId);
    }

    private function createPendingBooking(User $customer, int $ticketQuantity, int $bookingQuantity): Booking
    {
        static $organizerIndex = 0;
        $organizerIndex++;

        $organizer = $this->createUser(Role::ORGANIZER, 'payment.organizer.'.$organizerIndex.'@example.com');

        $event = new Event();
        $event->title = 'Payment Edge Event';
        $event->description = null;
        $event->date = '2026-10-01 10:00:00';
        $event->location = 'Novi Sad';
        $event->created_by = $organizer->id;
        $event->save();

        $ticket = new Ticket();
        $ticket->event_id = $event->id;
        $ticket->type = 'Standard';
        $ticket->price = 80.00;
        $ticket->quantity = $ticketQuantity;
        $ticket->save();

        $booking = new Booking();
        $booking->user_id = $customer->id;
        $booking->ticket_id = $ticket->id;
        $booking->quantity = $bookingQuantity;
        $booking->status = BookingStatus::PENDING;
        $booking->save();

        return $booking;
    }
}
