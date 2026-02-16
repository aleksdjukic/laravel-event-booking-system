<?php

namespace App\Modules\Ticket\Providers;

use App\Modules\Ticket\Application\Contracts\TicketServiceInterface;
use App\Modules\Ticket\Application\Services\TicketService;
use App\Domain\Ticket\Models\Ticket;
use App\Domain\Ticket\Policies\TicketPolicy;
use App\Domain\Ticket\Repositories\TicketRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\TicketRepository;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class TicketModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TicketRepositoryInterface::class, TicketRepository::class);
        $this->app->bind(TicketServiceInterface::class, TicketService::class);
    }

    public function boot(): void
    {
        Gate::policy(Ticket::class, TicketPolicy::class);
    }
}
