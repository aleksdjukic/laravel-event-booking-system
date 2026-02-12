<?php

namespace App\Providers;

use App\Models\Booking;
use App\Models\Event;
use App\Models\Payment;
use App\Models\Ticket;
use App\Policies\BookingPolicy;
use App\Policies\EventPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\TicketPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Event::class => EventPolicy::class,
        Ticket::class => TicketPolicy::class,
        Booking::class => BookingPolicy::class,
        Payment::class => PaymentPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
