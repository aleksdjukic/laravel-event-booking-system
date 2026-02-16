<?php

namespace App\Modules\Event\Providers;

use App\Modules\Event\Application\Contracts\EventServiceInterface;
use App\Modules\Event\Application\Services\EventService;
use App\Modules\Event\Domain\Models\Event;
use App\Modules\Event\Domain\Policies\EventPolicy;
use App\Modules\Event\Domain\Repositories\EventRepositoryInterface;
use App\Modules\Event\Infrastructure\Persistence\Eloquent\EventRepository;
use App\Modules\Event\Infrastructure\Persistence\Eloquent\Observers\EventObserver;
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
