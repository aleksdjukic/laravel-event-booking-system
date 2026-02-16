<?php

namespace Tests\Feature\Modules\Payment;

use App\Modules\Booking\Domain\Enums\BookingStatus;
use App\Modules\Booking\Domain\Models\Booking;
use App\Modules\Event\Domain\Models\Event;
use App\Modules\Payment\Domain\Models\Payment;
use App\Modules\Payment\Presentation\Http\Requests\CreatePaymentRequest;
use App\Modules\Ticket\Domain\Models\Ticket;
use App\Modules\User\Domain\Enums\Role;
use App\Modules\User\Domain\Models\User;
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
            ->postJson('/api/v1/bookings/'.$booking->{Booking::COL_ID}.'/payment', [
                CreatePaymentRequest::INPUT_FORCE_SUCCESS => true,
            ]);

        $firstResponse->assertStatus(201)->assertJsonPath('success', true);
        $firstPaymentId = $firstResponse->json('data.id');

        $secondResponse = $this->withHeader('Idempotency-Key', 'idem-key-1')
            ->postJson('/api/v1/bookings/'.$booking->{Booking::COL_ID}.'/payment', [
                CreatePaymentRequest::INPUT_FORCE_SUCCESS => true,
            ]);

        $secondResponse->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $firstPaymentId);

        $this->assertDatabaseCount(Payment::TABLE, 1);
    }

    public function test_idempotency_key_cannot_be_reused_for_another_booking(): void
    {
        Notification::fake();

        $customer = $this->createUser(Role::CUSTOMER, 'idempotency.reuse@example.com');
        $firstBooking = $this->createPendingBooking($customer, 1, 90.0, 50);
        $secondBooking = $this->createPendingBooking($customer, 1, 90.0, 50);

        Sanctum::actingAs($customer);

        $this->withHeader('Idempotency-Key', 'idem-key-2')
            ->postJson('/api/v1/bookings/'.$firstBooking->{Booking::COL_ID}.'/payment', [
                CreatePaymentRequest::INPUT_FORCE_SUCCESS => true,
            ])
            ->assertStatus(201)
            ->assertJsonPath('success', true);

        $this->withHeader('Idempotency-Key', 'idem-key-2')
            ->postJson('/api/v1/bookings/'.$secondBooking->{Booking::COL_ID}.'/payment', [
                CreatePaymentRequest::INPUT_FORCE_SUCCESS => true,
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
            ->postJson('/api/v1/bookings/'.$booking->{Booking::COL_ID}.'/payment', [
                CreatePaymentRequest::INPUT_FORCE_SUCCESS => true,
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
            ->postJson('/api/v1/bookings/'.$booking->{Booking::COL_ID}.'/payment', [
                CreatePaymentRequest::INPUT_FORCE_SUCCESS => true,
            ]);

        $firstResponse->assertStatus(201)->assertJsonPath('success', true);
        $firstPaymentId = $firstResponse->json('data.id');

        $secondResponse = $this->withHeader('Idempotency-Key', 'idem-key-trimmed')
            ->postJson('/api/v1/bookings/'.$booking->{Booking::COL_ID}.'/payment', [
                CreatePaymentRequest::INPUT_FORCE_SUCCESS => true,
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
        $event->{Event::COL_TITLE} = 'Idempotency Event '.$organizerIndex;
        $event->{Event::COL_DESCRIPTION} = null;
        $event->{Event::COL_DATE} = '2026-11-01 10:00:00';
        $event->{Event::COL_LOCATION} = 'Belgrade';
        $event->{Event::COL_CREATED_BY} = $organizer->{User::COL_ID};
        $event->save();

        $ticket = new Ticket();
        $ticket->{Ticket::COL_EVENT_ID} = $event->{Event::COL_ID};
        $ticket->{Ticket::COL_TYPE} = 'VIP';
        $ticket->{Ticket::COL_PRICE} = $price;
        $ticket->{Ticket::COL_QUANTITY} = $stock;
        $ticket->save();

        $booking = new Booking();
        $booking->{Booking::COL_USER_ID} = $customer->{User::COL_ID};
        $booking->{Booking::COL_TICKET_ID} = $ticket->{Ticket::COL_ID};
        $booking->{Booking::COL_QUANTITY} = $quantity;
        $booking->{Booking::COL_STATUS} = BookingStatus::PENDING;
        $booking->save();

        return $booking;
    }
}
