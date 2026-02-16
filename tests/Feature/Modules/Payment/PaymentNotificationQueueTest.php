<?php

namespace Tests\Feature\Modules\Payment;

use App\Modules\Booking\Domain\Enums\BookingStatus;
use App\Modules\Booking\Domain\Models\Booking;
use App\Modules\Event\Domain\Models\Event;
use App\Modules\Payment\Domain\Enums\PaymentStatus;
use App\Modules\Payment\Presentation\Http\Requests\CreatePaymentRequest;
use App\Modules\Ticket\Domain\Models\Ticket;
use App\Modules\User\Domain\Enums\Role;
use App\Modules\User\Domain\Models\User;
use App\Modules\Booking\Infrastructure\Notifications\BookingConfirmedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\Concerns\CreatesUsers;
use Tests\TestCase;

class PaymentNotificationQueueTest extends TestCase
{
    use RefreshDatabase;
    use CreatesUsers;

    public function test_successful_payment_queues_booking_confirmed_notification_job(): void
    {
        Queue::fake();

        $customer = $this->createUser(Role::CUSTOMER, 'queue.success.customer@example.com');
        $booking = $this->createPendingBooking($customer, 10, 2);

        Sanctum::actingAs($customer);

        $this->postJson('/api/v1/bookings/'.$booking->{Booking::COL_ID}.'/payment', [
            CreatePaymentRequest::INPUT_FORCE_SUCCESS => true,
        ])->assertStatus(201)
            ->assertJsonPath('data.status', PaymentStatus::SUCCESS->value);

        Queue::assertPushed(SendQueuedNotifications::class, function (SendQueuedNotifications $job) use ($customer): bool {
            return $job->notification instanceof BookingConfirmedNotification
                && $job->notifiables->contains(
                    fn ($notifiable) => (int) $notifiable->{User::COL_ID} === (int) $customer->{User::COL_ID}
                );
        });
    }

    public function test_failed_payment_does_not_queue_booking_confirmed_notification_job(): void
    {
        Queue::fake();

        $customer = $this->createUser(Role::CUSTOMER, 'queue.failed.customer@example.com');
        $booking = $this->createPendingBooking($customer, 10, 2);

        Sanctum::actingAs($customer);

        $this->postJson('/api/v1/bookings/'.$booking->{Booking::COL_ID}.'/payment', [
            CreatePaymentRequest::INPUT_FORCE_SUCCESS => false,
        ])->assertStatus(201)
            ->assertJsonPath('data.status', PaymentStatus::FAILED->value);

        Queue::assertNotPushed(SendQueuedNotifications::class, function (SendQueuedNotifications $job): bool {
            return $job->notification instanceof BookingConfirmedNotification;
        });
    }

    private function createPendingBooking(User $customer, int $ticketQuantity, int $bookingQuantity): Booking
    {
        static $organizerIndex = 0;
        $organizerIndex++;

        $organizer = $this->createUser(Role::ORGANIZER, 'queue.organizer.'.$organizerIndex.'@example.com');

        $event = new Event();
        $event->{Event::COL_TITLE} = 'Queue Event';
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
