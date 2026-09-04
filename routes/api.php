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
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\PaymentMethodController;

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

        // Notifications
        Route::get('/notifications',           [NotificationController::class, 'index']);
        Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead']);
        Route::post('/notifications/read-all',  [NotificationController::class, 'markAllRead']);

        // Appointments
        Route::get('/appointments',                      [AppointmentController::class, 'index']);
        Route::post('/appointments',                     [AppointmentController::class, 'store']);
        Route::post('/appointments/{id}/payment-method', [AppointmentController::class, 'updatePaymentMethod']);
        Route::delete('/appointments/{id}',              [AppointmentController::class, 'destroy']);
        Route::get('/appointments/slots',                [AppointmentController::class, 'slots']);

        // Payment Methods
        Route::get('/payment-methods',                      [PaymentMethodController::class, 'index']);
        Route::post('/payment-methods',                     [PaymentMethodController::class, 'store']);
        Route::put('/payment-methods/{id}',                 [PaymentMethodController::class, 'update']);
        Route::delete('/payment-methods/{id}',              [PaymentMethodController::class, 'destroy']);
        Route::post('/payment-methods/{id}/set-default',    [PaymentMethodController::class, 'setDefault']);

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
