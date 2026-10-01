<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DeveloperFinancialController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — F Loafinwatch & External Mobile Integrations
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    // Public Authentication
    Route::post('/auth/login', [AuthController::class, 'login']);

    // Authenticated Routes
    Route::middleware('api.token')->group(function () {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // Developer Financial Endpoints (Role: ryu_dev)
        Route::prefix('developer')->group(function () {
            Route::get('/dashboard', [DeveloperFinancialController::class, 'dashboard']);
            Route::get('/transactions', [DeveloperFinancialController::class, 'transactions']);
            Route::get('/payouts', [DeveloperFinancialController::class, 'payouts']);
        });
    });
});
