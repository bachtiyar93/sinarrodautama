<?php

use App\Http\Controllers\Api\v1\TaskController;
use App\Http\Controllers\Api\v1\UserController;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

// Rate Limiting configuration
RateLimiter::for('api', function (Request $request) {
    return $request->user()
        ? Limit::perMinute(60)->by($request->user()->id)
        : Limit::perMinute(5)->by($request->ip());
});

Route::prefix('v1')->middleware(['api', 'throttle:api'])->group(function () {
    Route::apiResource('tasks', TaskController::class);
    Route::apiResource('users', UserController::class);

    Route::get('/user', function (Request $request) {
        return $request->user();
    })->middleware('auth:sanctum');
});
