<?php

use App\Domain\Shared\DomainError;
use App\Domain\Shared\DomainException;
use App\Http\Middleware\EnsureRole;
use App\Support\Http\ApiResponder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => EnsureRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $apiResponder = new ApiResponder();
        $apiError = static fn (string $message, int $status, mixed $errors = null) => $apiResponder->error($message, $status, $errors);

        $exceptions->render(function (DomainException $exception, Request $request) use ($apiError) {
            if (! $request->is('api/*')) {
                return null;
            }

            $status = match ($exception->error()) {
                DomainError::FORBIDDEN => 403,
                DomainError::EVENT_NOT_FOUND,
                DomainError::TICKET_NOT_FOUND,
                DomainError::BOOKING_NOT_FOUND,
                DomainError::PAYMENT_NOT_FOUND => 404,
                DomainError::DUPLICATE_TICKET_TYPE,
                DomainError::TICKET_SOLD_OUT,
                DomainError::NOT_ENOUGH_TICKET_INVENTORY,
                DomainError::BOOKING_NOT_PENDING,
                DomainError::INVALID_BOOKING_STATE_FOR_PAYMENT,
                DomainError::PAYMENT_ALREADY_EXISTS,
                DomainError::IDEMPOTENCY_KEY_REUSED,
                DomainError::ACTIVE_BOOKING_ALREADY_EXISTS => 409,
            };

            return $apiError($exception->getMessage(), $status);
        });

        $exceptions->render(function (AuthenticationException $exception, Request $request) use ($apiError) {
            if ($request->is('api/*')) {
                return $apiError('Unauthorized', 401);
            }
        });

        $exceptions->render(function (AuthorizationException $exception, Request $request) use ($apiError) {
            if ($request->is('api/*')) {
                return $apiError('Forbidden', 403);
            }
        });

        $exceptions->render(function (ValidationException $exception, Request $request) use ($apiError) {
            if ($request->is('api/*')) {
                return $apiError('The given data was invalid.', 422, $exception->errors());
            }
        });

        $exceptions->render(function (ModelNotFoundException|NotFoundHttpException $exception, Request $request) use ($apiError) {
            if ($request->is('api/*')) {
                return $apiError('Not found', 404);
            }
        });

        $exceptions->render(function (HttpExceptionInterface $exception, Request $request) use ($apiError) {
            if (! $request->is('api/*')) {
                return null;
            }

            $status = $exception->getStatusCode();
            $message = match ($status) {
                401 => 'Unauthorized',
                403 => 'Forbidden',
                404 => 'Not found',
                422 => 'The given data was invalid.',
                default => $status >= 500 ? 'Server error' : 'Request error',
            };

            return $apiError($message, $status);
        });

        $exceptions->render(function (\Throwable $exception, Request $request) use ($apiError) {
            if ($request->is('api/*')) {
                return $apiError('Server error', 500);
            }
        });
    })->create();
