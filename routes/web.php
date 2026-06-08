<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProviderController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ServiceController;
use Illuminate\Support\Facades\Route;

// ─── Public Routes ────────────────────────────────────────────────────────────

Route::get('/', [HomeController::class, 'index'])->name('home');

// Services (public read)
Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
Route::get('/services/{service}', [ServiceController::class, 'show'])->name('services.show');

// Provider public profile (read-only)
Route::get('/providers/{provider}', [ProviderController::class, 'profile'])->name('provider.profile');

// ─── Auth Routes ──────────────────────────────────────────────────────────────

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ─── Customer Routes ──────────────────────────────────────────────────────────

Route::middleware(['auth', 'role:customer'])->prefix('customer')->name('customer.')->group(function () {

    // Bookings
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings');
    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');

    // Reviews
    Route::post('/reviews', [ReviewController::class, 'store'])->name('reviews.store');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.readAll');
});

// ─── Provider Routes ──────────────────────────────────────────────────────────

Route::middleware(['auth', 'role:provider'])->prefix('provider')->name('provider.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [ProviderController::class, 'dashboard'])->name('dashboard');

    // Services (CRUD)
    Route::post('/services', [ServiceController::class, 'store'])->name('services.store');
    Route::put('/services/{service}', [ServiceController::class, 'update'])->name('services.update');
    Route::delete('/services/{service}', [ServiceController::class, 'destroy'])->name('services.destroy');

    // Bookings
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings');
    Route::patch('/bookings/{booking}/status', [BookingController::class, 'updateStatus'])->name('bookings.updateStatus');
    Route::patch('/bookings/{booking}/complete', [BookingController::class, 'complete'])->name('bookings.complete');

    // Availability
    Route::post('/availability', [ProviderController::class, 'setAvailability'])->name('availability.set');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.readAll');
});

// ─── Admin Routes ─────────────────────────────────────────────────────────────

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Provider approvals
    Route::get('/providers', [AdminController::class, 'pendingProviders'])->name('providers');
    Route::post('/providers/{provider}/approve', [AdminController::class, 'approveProvider'])->name('providers.approve');

    // Categories
    Route::get('/categories', [AdminController::class, 'manageCategories'])->name('categories');
    Route::post('/categories', [AdminController::class, 'storeCategory'])->name('categories.store');
    Route::put('/categories/{category}', [AdminController::class, 'updateCategory'])->name('categories.update');
    Route::delete('/categories/{category}', [AdminController::class, 'destroyCategory'])->name('categories.destroy');

    // All bookings
    Route::get('/bookings', [AdminController::class, 'allBookings'])->name('bookings');
    Route::patch('/bookings/{booking}/status', [BookingController::class, 'updateStatus'])->name('bookings.updateStatus');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.readAll');
});
