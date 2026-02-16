<?php

namespace App\Modules\Booking\Providers;

use App\Modules\Booking\Application\Contracts\BookingServiceInterface;
use App\Modules\Booking\Application\Services\BookingService;
use App\Modules\Booking\Domain\Models\Booking;
use App\Modules\Booking\Domain\Policies\BookingPolicy;
use App\Modules\Booking\Domain\Repositories\BookingRepositoryInterface;
use App\Modules\Booking\Infrastructure\Persistence\Eloquent\BookingRepository;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class BookingModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(BookingRepositoryInterface::class, BookingRepository::class);
        $this->app->bind(BookingServiceInterface::class, BookingService::class);
    }

    public function boot(): void
    {
        Gate::policy(Booking::class, BookingPolicy::class);
    }
}
