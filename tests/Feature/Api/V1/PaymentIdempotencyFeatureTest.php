<?php

namespace Tests\Feature\Api\V1;

use App\Domain\Booking\Enums\BookingStatus;
use App\Domain\Booking\Models\Booking;
use App\Domain\Event\Models\Event;
use App\Domain\Ticket\Models\Ticket;
use App\Domain\User\Enums\Role;
use App\Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class PaymentIdempotencyFeatureTest extends TestCase
{
    use RefreshDatabase;
    use CreatesUsers;

    public function test_same_idempotency_key_returns_existing_payment(): void
    {
        Notification::fake();

        $customer = $this->createUser(Role::CUSTOMER, 'idempotency.customer@example.com');
        $booking = $this->createPendingBooking($customer, 2, 100.0, 50);

        Sanctum::actingAs($customer);

        $firstResponse = $this->withHeader('Idempotency-Key', 'idem-key-1')
            ->postJson('/api/v1/bookings/'.$booking->id.'/payment', [
                'force_success' => true,
            ]);

        $firstResponse->assertStatus(201)->assertJsonPath('success', true);
        $firstPaymentId = $firstResponse->json('data.id');

        $secondResponse = $this->withHeader('Idempotency-Key', 'idem-key-1')
            ->postJson('/api/v1/bookings/'.$booking->id.'/payment', [
                'force_success' => true,
            ]);

        $secondResponse->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $firstPaymentId);

        $this->assertDatabaseCount('payments', 1);
    }

    public function test_idempotency_key_cannot_be_reused_for_another_booking(): void
    {
        Notification::fake();

        $customer = $this->createUser(Role::CUSTOMER, 'idempotency.reuse@example.com');
        $firstBooking = $this->createPendingBooking($customer, 1, 90.0, 50);
        $secondBooking = $this->createPendingBooking($customer, 1, 90.0, 50);

        Sanctum::actingAs($customer);

        $this->withHeader('Idempotency-Key', 'idem-key-2')
            ->postJson('/api/v1/bookings/'.$firstBooking->id.'/payment', [
                'force_success' => true,
            ])
            ->assertStatus(201)
            ->assertJsonPath('success', true);

        $this->withHeader('Idempotency-Key', 'idem-key-2')
            ->postJson('/api/v1/bookings/'.$secondBooking->id.'/payment', [
                'force_success' => true,
            ])
            ->assertStatus(409)
            ->assertJsonPath('success', false);
    }

    public function test_idempotency_key_longer_than_128_characters_returns_422(): void
    {
        Notification::fake();

        $customer = $this->createUser(Role::CUSTOMER, 'idempotency.validation@example.com');
        $booking = $this->createPendingBooking($customer, 1, 90.0, 50);

        Sanctum::actingAs($customer);

        $this->withHeader('Idempotency-Key', str_repeat('a', 129))
            ->postJson('/api/v1/bookings/'.$booking->id.'/payment', [
                'force_success' => true,
            ])
            ->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_idempotency_key_is_trimmed_from_header(): void
    {
        Notification::fake();

        $customer = $this->createUser(Role::CUSTOMER, 'idempotency.trim@example.com');
        $booking = $this->createPendingBooking($customer, 1, 100.0, 50);

        Sanctum::actingAs($customer);

        $firstResponse = $this->withHeader('Idempotency-Key', '  idem-key-trimmed  ')
            ->postJson('/api/v1/bookings/'.$booking->id.'/payment', [
                'force_success' => true,
            ]);

        $firstResponse->assertStatus(201)->assertJsonPath('success', true);
        $firstPaymentId = $firstResponse->json('data.id');

        $secondResponse = $this->withHeader('Idempotency-Key', 'idem-key-trimmed')
            ->postJson('/api/v1/bookings/'.$booking->id.'/payment', [
                'force_success' => true,
            ]);

        $secondResponse->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $firstPaymentId);
    }

    private function createPendingBooking(User $customer, int $quantity, float $price, int $stock): Booking
    {
        static $organizerIndex = 0;
        $organizerIndex++;

        $organizer = $this->createUser(Role::ORGANIZER, 'idempotency.organizer.'.$organizerIndex.'@example.com');

        $event = new Event();
        $event->title = 'Idempotency Event '.$organizerIndex;
        $event->description = null;
        $event->date = '2026-11-01 10:00:00';
        $event->location = 'Belgrade';
        $event->created_by = $organizer->id;
        $event->save();

        $ticket = new Ticket();
        $ticket->event_id = $event->id;
        $ticket->type = 'VIP';
        $ticket->price = $price;
        $ticket->quantity = $stock;
        $ticket->save();

        $booking = new Booking();
        $booking->user_id = $customer->id;
        $booking->ticket_id = $ticket->id;
        $booking->quantity = $quantity;
        $booking->status = BookingStatus::PENDING;
        $booking->save();

        return $booking;
    }
}
