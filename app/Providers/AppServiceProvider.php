<?php

namespace App\Providers;

use App\Domain\Event\Repositories\EventRepositoryInterface;
use App\Application\Contracts\Services\AuthServiceInterface;
use App\Application\Contracts\Services\BookingServiceInterface;
use App\Application\Contracts\Services\EventServiceInterface;
use App\Application\Contracts\Services\PaymentTransactionServiceInterface;
use App\Application\Contracts\Services\TicketServiceInterface;
use App\Domain\Booking\Repositories\BookingRepositoryInterface;
use App\Domain\Payment\Repositories\PaymentIdempotencyRepositoryInterface;
use App\Domain\Payment\Repositories\PaymentRepositoryInterface;
use App\Domain\Payment\Services\PaymentGatewayInterface;
use App\Domain\Ticket\Repositories\TicketRepositoryInterface;
use App\Domain\Event\Models\Event;
use App\Infrastructure\Persistence\Eloquent\Observers\EventObserver;
use App\Infrastructure\Persistence\Eloquent\BookingRepository;
use App\Application\Services\Auth\AuthService;
use App\Application\Services\Booking\BookingService;
use App\Application\Services\Event\EventService;
use App\Infrastructure\Persistence\Eloquent\PaymentRepository;
use App\Infrastructure\Persistence\Eloquent\PaymentIdempotencyRepository;
use App\Infrastructure\Payment\PaymentGatewayService;
use App\Application\Services\Payment\PaymentTransactionService;
use App\Infrastructure\Persistence\Eloquent\TicketRepository;
use App\Application\Services\Ticket\TicketService;
use App\Infrastructure\Persistence\Eloquent\EventRepository;
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
        $this->app->bind(BookingRepositoryInterface::class, BookingRepository::class);
        $this->app->bind(TicketRepositoryInterface::class, TicketRepository::class);
        $this->app->bind(PaymentRepositoryInterface::class, PaymentRepository::class);
        $this->app->bind(PaymentIdempotencyRepositoryInterface::class, PaymentIdempotencyRepository::class);
        $this->app->bind(PaymentGatewayInterface::class, PaymentGatewayService::class);
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
