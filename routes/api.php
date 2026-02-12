<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\UserController;

Route::prefix('v1')->group(function (): void {
    Route::get('/ping', function () {
        return response()->json([
            'success' => true,
            'message' => 'ok',
            'data' => null,
        ]);
    });

    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/user/me', [UserController::class, 'me']);
    });

    Route::middleware(['auth:sanctum', 'role:admin,organizer'])->group(function (): void {
        // Event/Ticket write routes will be added in the next steps.
    });

    Route::middleware(['auth:sanctum', 'role:admin,customer'])->group(function (): void {
        // Booking/Payment routes will be added in the next steps.
    });
});
