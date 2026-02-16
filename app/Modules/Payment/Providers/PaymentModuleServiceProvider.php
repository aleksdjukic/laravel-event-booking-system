<?php

namespace App\Modules\Payment\Providers;

use App\Modules\Payment\Application\Contracts\PaymentTransactionServiceInterface;
use App\Modules\Payment\Application\Services\PaymentTransactionService;
use App\Modules\Payment\Domain\Models\Payment;
use App\Modules\Payment\Domain\Policies\PaymentPolicy;
use App\Modules\Payment\Domain\Repositories\PaymentIdempotencyRepositoryInterface;
use App\Modules\Payment\Domain\Repositories\PaymentRepositoryInterface;
use App\Modules\Payment\Domain\Services\PaymentGatewayInterface;
use App\Modules\Payment\Infrastructure\PaymentGatewayService;
use App\Modules\Payment\Infrastructure\Persistence\Eloquent\PaymentIdempotencyRepository;
use App\Modules\Payment\Infrastructure\Persistence\Eloquent\PaymentRepository;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class PaymentModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PaymentRepositoryInterface::class, PaymentRepository::class);
        $this->app->bind(PaymentIdempotencyRepositoryInterface::class, PaymentIdempotencyRepository::class);
        $this->app->bind(PaymentGatewayInterface::class, PaymentGatewayService::class);
        $this->app->bind(PaymentTransactionServiceInterface::class, PaymentTransactionService::class);
    }

    public function boot(): void
    {
        Gate::policy(Payment::class, PaymentPolicy::class);
    }
}
