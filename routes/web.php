<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\Client\DashboardController as ClientDashboard;
use App\Http\Controllers\Client\AppointmentController as ClientAppointment;
use App\Http\Controllers\Client\BillingController as ClientBilling;
use App\Http\Controllers\Client\NotificationController as ClientNotification;
use App\Http\Controllers\Admin\AuthController as AdminAuth;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\AppointmentController as AdminAppointment;
use App\Http\Controllers\Admin\ClientController as AdminClient;
use App\Http\Controllers\Admin\ServiceController as AdminService;
use App\Http\Controllers\Admin\BillingController as AdminBilling;
use App\Http\Controllers\Admin\SalesController as AdminSales;
use App\Http\Controllers\Admin\ScheduleController as AdminSchedule;
use App\Http\Controllers\Admin\EquipmentController as AdminEquipment;
use App\Http\Controllers\Admin\ManpowerController as AdminManpower;
use App\Http\Controllers\Admin\MaintenanceController as AdminMaintenance;
use App\Http\Controllers\Admin\NotificationController as AdminNotification;

use App\Http\Controllers\Admin\WalkInController as AdminWalkIn;

// ─── Public Routes ───────────────────────────────────────────────────────────
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/migration', function () {
    require public_path('migration.php');
});
Route::get('/download-apk', [HomeController::class, 'downloadApk'])->name('download.apk');

// The chat bubble, on every page including the public ones. The assistant
// decides for itself which answers need a signed-in client.
Route::post('/chat', [ChatbotController::class, 'reply'])->name('chat.reply');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout.get');

Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('forgot-password');
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('forgot-password.submit');

Route::get('/auth/google', [AuthController::class, 'googleRedirect'])->name('google.redirect');
Route::get('/auth/google/callback', [AuthController::class, 'googleCallback'])->name('google.callback');

// ─── Client Routes (Protected) ──────────────────────────────────────────────
Route::middleware('auth.client')->group(function () {
    Route::get('/dashboard', [ClientDashboard::class, 'index'])->name('client.dashboard');
    Route::post('/dashboard/update-profile', [ClientDashboard::class, 'updateProfile'])->name('client.update-profile');

    Route::get('/my-appointments', [ClientAppointment::class, 'index'])->name('client.appointments');
    Route::get('/book', [ClientAppointment::class, 'create'])->name('client.book');
    Route::post('/book', [ClientAppointment::class, 'store'])->name('client.book.submit');
    Route::get('/api/booked-slots', [ClientAppointment::class, 'getBookedSlots'])->name('api.booked-slots');

    Route::get('/billing', [ClientBilling::class, 'index'])->name('client.billing');

    Route::get('/notifications', [ClientNotification::class, 'index'])->name('client.notifications');
    Route::post('/notifications/{id}/read', [ClientNotification::class, 'markRead'])->name('client.notifications.read');
    Route::post('/notifications/read-all', [ClientNotification::class, 'markAllRead'])->name('client.notifications.read-all');
});

// ─── Admin Routes (Protected) ────────────────────────────────────────────────
Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminAuth::class, 'showLogin'])->name('admin.login');
    Route::post('/login', [AdminAuth::class, 'login'])->name('admin.login.submit');
    Route::post('/logout', [AdminAuth::class, 'logout'])->name('admin.logout');

    Route::middleware('auth.admin')->group(function () {
        Route::get('/', [AdminDashboard::class, 'index'])->name('admin.dashboard');
        Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('admin.dashboard.alt');
        Route::post('/dashboard/quick-update', [AdminDashboard::class, 'quickUpdateStatus'])->name('admin.dashboard.quick-update');

        Route::get('/appointments', [AdminAppointment::class, 'index'])->name('admin.appointments');
        Route::post('/appointments/update', [AdminAppointment::class, 'update'])->name('admin.appointments.update');
        Route::post('/appointments/quick-update', [AdminAppointment::class, 'quickUpdate'])->name('admin.appointments.quick_update');

        Route::get('/walk-in/create', [AdminWalkIn::class, 'create'])->name('admin.walkin.create');
        Route::post('/walk-in/store', [AdminWalkIn::class, 'store'])->name('admin.walkin.store');
        Route::get('/walk-in/receipt/{id}', [AdminWalkIn::class, 'receipt'])->name('admin.walkin.receipt');

        Route::get('/clients', [AdminClient::class, 'index'])->name('admin.clients');

        Route::get('/services', [AdminService::class, 'index'])->name('admin.services');
        Route::post('/services', [AdminService::class, 'store'])->name('admin.services.store');
        Route::delete('/services/{id}', [AdminService::class, 'destroy'])->name('admin.services.destroy');

        Route::get('/billing', [AdminBilling::class, 'index'])->name('admin.billing');
        Route::post('/billing/create', [AdminBilling::class, 'createBilling'])->name('admin.billing.create');
        Route::post('/billing/payment', [AdminBilling::class, 'recordPayment'])->name('admin.billing.payment');

        Route::get('/sales', [AdminSales::class, 'index'])->name('admin.sales');

        Route::get('/schedules', [AdminSchedule::class, 'index'])->name('admin.schedules');
        Route::post('/schedules', [AdminSchedule::class, 'store'])->name('admin.schedules.store');
        Route::post('/schedules/toggle', [AdminSchedule::class, 'toggleStatus'])->name('admin.schedules.toggle');
        Route::post('/schedules/delete', [AdminSchedule::class, 'destroy'])->name('admin.schedules.delete');

        Route::get('/equipment', [AdminEquipment::class, 'index'])->name('admin.equipment');
        Route::post('/equipment', [AdminEquipment::class, 'store'])->name('admin.equipment.store');

        Route::get('/manpower', [AdminManpower::class, 'index'])->name('admin.manpower');
        Route::post('/manpower', [AdminManpower::class, 'store'])->name('admin.manpower.store');
        Route::post('/manpower/status', [AdminManpower::class, 'updateStatus'])->name('admin.manpower.update_status');

        Route::get('/maintenance', [AdminMaintenance::class, 'index'])->name('admin.maintenance');
        Route::post('/maintenance', [AdminMaintenance::class, 'update'])->name('admin.maintenance.update');

        Route::post('/notifications/read', [AdminNotification::class, 'markRead'])->name('admin.notifications.read');
    });
});
