<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/ping', function () {
        return response()->json([
            'success' => true,
            'message' => 'ok',
            'data' => null,
        ]);
    });
});
