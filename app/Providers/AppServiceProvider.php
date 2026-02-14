<?php

namespace App\Providers;

use App\Contracts\Repositories\EventRepositoryInterface;
use App\Contracts\Services\AuthServiceInterface;
use App\Contracts\Services\BookingServiceInterface;
use App\Contracts\Services\EventServiceInterface;
use App\Contracts\Services\PaymentTransactionServiceInterface;
use App\Contracts\Services\TicketServiceInterface;
use App\Models\Event;
use App\Observers\EventObserver;
use App\Services\Auth\AuthService;
use App\Services\Booking\BookingService;
use App\Services\Event\EventService;
use App\Services\Payment\PaymentTransactionService;
use App\Services\Ticket\TicketService;
use App\Repositories\Eloquent\EventRepository;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(EventRepositoryInterface::class, EventRepository::class);
        $this->app->bind(EventServiceInterface::class, EventService::class);
        $this->app->bind(TicketServiceInterface::class, TicketService::class);
        $this->app->bind(BookingServiceInterface::class, BookingService::class);
        $this->app->bind(PaymentTransactionServiceInterface::class, PaymentTransactionService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        JsonResource::withoutWrapping();
        Event::observe(EventObserver::class);
    }
}
