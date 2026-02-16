<?php

use App\Http\Controllers\Api\V1\Health\HealthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/ping', [HealthController::class, 'ping']);
});
