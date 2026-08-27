<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\AppointmentController;
use App\Http\Controllers\Api\V1\ServiceController;
use App\Http\Controllers\Api\V1\BillingController;
use App\Http\Controllers\Api\V1\MaintenanceController;
use App\Http\Controllers\Api\V1\ChatbotController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ─── API v1 ──────────────────────────────────────────────────────────────────
Route::prefix('v1')->group(function () {

    // ── Auth (public) ─────────────────────────────────────────────────────
    Route::post('/auth/login',    [AuthController::class, 'login']);
    Route::post('/auth/google',   [AuthController::class, 'google']);
    Route::post('/auth/register', [AuthController::class, 'register']);

    // ── Protected (requires Bearer token via Sanctum) ─────────────────────
    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // Profile
        Route::get('/profile',  [ProfileController::class, 'show']);
        Route::put('/profile',  [ProfileController::class, 'update']);

        // Appointments
        Route::get('/appointments',           [AppointmentController::class, 'index']);
        Route::post('/appointments',          [AppointmentController::class, 'store']);
        Route::delete('/appointments/{id}',   [AppointmentController::class, 'destroy']);
        Route::get('/appointments/slots',     [AppointmentController::class, 'slots']);

        // Services
        Route::get('/services', [ServiceController::class, 'index']);

        // Billing
        Route::get('/billing', [BillingController::class, 'index']);

        // Maintenance
        Route::get('/maintenance',  [MaintenanceController::class, 'index']);
        Route::post('/maintenance', [MaintenanceController::class, 'store']);

        // Assistant — the same rules the website's chat bubble runs on
        Route::post('/chat', [ChatbotController::class, 'reply']);
    });
});
