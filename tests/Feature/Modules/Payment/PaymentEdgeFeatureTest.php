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

        $this->postJson('/api/v1/bookings/'.$booking->{Booking::COL_ID}.'/payment', [
            CreatePaymentRequest::INPUT_FORCE_SUCCESS => true,
        ])->assertStatus(201);

        $this->postJson('/api/v1/bookings/'.$booking->{Booking::COL_ID}.'/payment', [
            CreatePaymentRequest::INPUT_FORCE_SUCCESS => true,
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

        $this->postJson('/api/v1/bookings/'.$soldOutBooking->{Booking::COL_ID}.'/payment', [
            CreatePaymentRequest::INPUT_FORCE_SUCCESS => true,
        ])->assertStatus(409)
            ->assertJsonPath('message', 'Ticket is sold out.');

        $this->postJson('/api/v1/bookings/'.$notEnoughBooking->{Booking::COL_ID}.'/payment', [
            CreatePaymentRequest::INPUT_FORCE_SUCCESS => true,
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

        $this->postJson('/api/v1/bookings/'.$booking->{Booking::COL_ID}.'/payment', [
            CreatePaymentRequest::INPUT_FORCE_SUCCESS => true,
        ])->assertStatus(403)
            ->assertJsonPath('success', false);
    }

    public function test_payment_force_success_false_cancels_booking_and_keeps_inventory(): void
    {
        Notification::fake();

        $customer = $this->createUser(Role::CUSTOMER, 'payment.force.fail.customer@example.com');
        $booking = $this->createPendingBooking($customer, 7, 3);

        $ticketId = (int) $booking->{Booking::COL_TICKET_ID};

        Sanctum::actingAs($customer);

        $this->postJson('/api/v1/bookings/'.$booking->{Booking::COL_ID}.'/payment', [
            CreatePaymentRequest::INPUT_FORCE_SUCCESS => false,
        ])->assertStatus(201)
            ->assertJsonPath('data.status', PaymentStatus::FAILED->value);

        $this->assertDatabaseHas(Booking::TABLE, [
            Booking::COL_ID => $booking->{Booking::COL_ID},
            Booking::COL_STATUS => BookingStatus::CANCELLED->value,
        ]);

        $this->assertDatabaseHas(Payment::TABLE, [
            Payment::COL_BOOKING_ID => $booking->{Booking::COL_ID},
            Payment::COL_STATUS => PaymentStatus::FAILED->value,
            Payment::COL_AMOUNT => '240.00',
        ]);

        $this->assertDatabaseHas(Ticket::TABLE, [
            Ticket::COL_ID => $ticketId,
            Ticket::COL_QUANTITY => 7,
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
        $paymentResponse = $this->postJson('/api/v1/bookings/'.$booking->{Booking::COL_ID}.'/payment', [
            CreatePaymentRequest::INPUT_FORCE_SUCCESS => true,
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
        $event->{Event::COL_TITLE} = 'Payment Edge Event';
        $event->{Event::COL_DESCRIPTION} = null;
        $event->{Event::COL_DATE} = '2026-10-01 10:00:00';
        $event->{Event::COL_LOCATION} = 'Novi Sad';
        $event->{Event::COL_CREATED_BY} = $organizer->{User::COL_ID};
        $event->save();

        $ticket = new Ticket();
        $ticket->{Ticket::COL_EVENT_ID} = $event->{Event::COL_ID};
        $ticket->{Ticket::COL_TYPE} = 'Standard';
        $ticket->{Ticket::COL_PRICE} = 80.00;
        $ticket->{Ticket::COL_QUANTITY} = $ticketQuantity;
        $ticket->save();

        $booking = new Booking();
        $booking->{Booking::COL_USER_ID} = $customer->{User::COL_ID};
        $booking->{Booking::COL_TICKET_ID} = $ticket->{Ticket::COL_ID};
        $booking->{Booking::COL_QUANTITY} = $bookingQuantity;
        $booking->{Booking::COL_STATUS} = BookingStatus::PENDING;
        $booking->save();

        return $booking;
    }
}
