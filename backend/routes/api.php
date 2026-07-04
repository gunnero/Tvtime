<?php

use App\Http\Controllers\Api\V1\AlertController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\InviteAcceptanceController;
use App\Http\Controllers\Api\V1\StatusController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('web')->group(function (): void {
    Route::get('/status', StatusController::class);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/invites/accept', InviteAcceptanceController::class);

    Route::middleware('auth')->group(function (): void {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/dashboard', DashboardController::class);
        Route::post('/alerts/{alert}/read', [AlertController::class, 'read']);
        Route::post('/alerts/read-all', [AlertController::class, 'readAll']);
    });
});
