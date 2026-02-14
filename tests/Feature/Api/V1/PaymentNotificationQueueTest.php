<?php

namespace Tests\Feature\Api\V1;

use App\Domain\Booking\Models\Booking;
use App\Domain\Event\Models\Event;
use App\Domain\Ticket\Models\Ticket;
use App\Domain\User\Models\User;
use App\Notifications\BookingConfirmedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PaymentNotificationQueueTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_payment_queues_booking_confirmed_notification_job(): void
    {
        Queue::fake();

        $customer = $this->createUser('customer', 'queue.success.customer@example.com');
        $booking = $this->createPendingBooking($customer, 10, 2);

        Sanctum::actingAs($customer);

        $this->postJson('/api/v1/bookings/'.$booking->id.'/payment', [
            'force_success' => true,
        ])->assertStatus(201)
            ->assertJsonPath('data.status', 'success');

        Queue::assertPushed(SendQueuedNotifications::class, function (SendQueuedNotifications $job) use ($customer): bool {
            return $job->notification instanceof BookingConfirmedNotification
                && $job->notifiables->contains(fn ($notifiable) => (int) $notifiable->id === (int) $customer->id);
        });
    }

    public function test_failed_payment_does_not_queue_booking_confirmed_notification_job(): void
    {
        Queue::fake();

        $customer = $this->createUser('customer', 'queue.failed.customer@example.com');
        $booking = $this->createPendingBooking($customer, 10, 2);

        Sanctum::actingAs($customer);

        $this->postJson('/api/v1/bookings/'.$booking->id.'/payment', [
            'force_success' => false,
        ])->assertStatus(201)
            ->assertJsonPath('data.status', 'failed');

        Queue::assertNotPushed(SendQueuedNotifications::class, function (SendQueuedNotifications $job): bool {
            return $job->notification instanceof BookingConfirmedNotification;
        });
    }

    private function createUser(string $role, string $email): User
    {
        $user = new User();
        $user->name = ucfirst($role).' User';
        $user->email = $email;
        $user->password = Hash::make('password123');
        $user->role = $role;
        $user->save();

        return $user;
    }

    private function createPendingBooking(User $customer, int $ticketQuantity, int $bookingQuantity): Booking
    {
        static $organizerIndex = 0;
        $organizerIndex++;

        $organizer = $this->createUser('organizer', 'queue.organizer.'.$organizerIndex.'@example.com');

        $event = new Event();
        $event->title = 'Queue Event';
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
        $booking->status = 'pending';
        $booking->save();

        return $booking;
    }
}
