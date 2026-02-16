<?php

namespace App\Modules\Auth\Providers;

use App\Modules\Auth\Application\Contracts\AuthServiceInterface;
use App\Modules\Auth\Application\Services\AuthService;
use Illuminate\Support\ServiceProvider;

class AuthModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
    }
}
