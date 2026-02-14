<?php

namespace App\Providers;

use App\Domain\Booking\Models\Booking;
use App\Domain\Event\Models\Event;
use App\Domain\Payment\Models\Payment;
use App\Domain\Ticket\Models\Ticket;
use App\Domain\Booking\Policies\BookingPolicy;
use App\Domain\Event\Policies\EventPolicy;
use App\Domain\Payment\Policies\PaymentPolicy;
use App\Domain\Ticket\Policies\TicketPolicy;
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
