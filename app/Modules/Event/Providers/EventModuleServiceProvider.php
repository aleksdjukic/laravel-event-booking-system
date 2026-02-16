<?php

namespace App\Modules\Event\Providers;

use App\Modules\Event\Application\Contracts\EventServiceInterface;
use App\Modules\Event\Application\Services\EventService;
use App\Domain\Event\Models\Event;
use App\Domain\Event\Policies\EventPolicy;
use App\Domain\Event\Repositories\EventRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\EventRepository;
use App\Infrastructure\Persistence\Eloquent\Observers\EventObserver;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class EventModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(EventRepositoryInterface::class, EventRepository::class);
        $this->app->bind(EventServiceInterface::class, EventService::class);
    }

    public function boot(): void
    {
        Gate::policy(Event::class, EventPolicy::class);
        Event::observe(EventObserver::class);
    }
}
