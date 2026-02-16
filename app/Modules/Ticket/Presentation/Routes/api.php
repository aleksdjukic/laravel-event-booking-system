<?php

use App\Http\Controllers\Api\V1\Ticket\TicketController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::middleware(['auth:sanctum', 'role:admin,organizer'])->group(function (): void {
        Route::post('/events/{event}/tickets', [TicketController::class, 'store']);
        Route::put('/tickets/{ticket}', [TicketController::class, 'update']);
        Route::delete('/tickets/{ticket}', [TicketController::class, 'destroy']);
    });
});
