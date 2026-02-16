<?php

use App\Http\Controllers\Api\V1\Booking\BookingController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::middleware(['auth:sanctum', 'role:customer'])->group(function (): void {
        Route::post('/tickets/{ticket}/bookings', [BookingController::class, 'store']);
    });

    Route::middleware(['auth:sanctum', 'role:admin,customer'])->group(function (): void {
        Route::get('/bookings', [BookingController::class, 'index']);
        Route::put('/bookings/{booking}/cancel', [BookingController::class, 'cancel']);
    });
});
