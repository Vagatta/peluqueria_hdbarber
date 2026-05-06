<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\StripeWebhookController;
use Illuminate\Support\Facades\Route;

// Stripe webhook (no CSRF, no auth) — must be POST raw
Route::post('/webhooks/stripe', StripeWebhookController::class)
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);

// Public
Route::get('/services', [ServiceController::class, 'index']);
Route::get('/services/{service}', [ServiceController::class, 'show']);
Route::get('/availability', [AppointmentController::class, 'availability']);

// Auth (sin CSRF para cross-origin - usamos stateless tokens)
Route::post('/auth/register', [AuthController::class, 'register'])
    ->middleware(['throttle:login'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);
    
Route::post('/auth/login', [AuthController::class, 'login'])
    ->middleware(['throttle:login'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);
    
Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword'])
    ->middleware('throttle:login');
    
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword'])
    ->middleware('throttle:login');

// Authenticated (Sanctum SPA via cookies)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::patch('/auth/profile', [AuthController::class, 'updateProfile']);

    Route::get('/appointments', [AppointmentController::class, 'index']);
    Route::post('/appointments', [AppointmentController::class, 'store']);
    Route::get('/appointments/{appointment}', [AppointmentController::class, 'show']);
    Route::post('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel']);

    Route::post('/appointments/{appointment}/checkout', [PaymentController::class, 'checkout']);
    Route::post('/appointments/{appointment}/intent', [PaymentController::class, 'intent']);
    Route::get('/payments', [PaymentController::class, 'index']);

    // Admin
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard']);
        Route::get('/clients', [AdminController::class, 'clients']);

        Route::post('/appointments', [AppointmentController::class, 'adminStore']);
        Route::patch('/appointments/{appointment}', [AppointmentController::class, 'update']);

        Route::post('/services', [ServiceController::class, 'store']);
        Route::patch('/services/{service}', [ServiceController::class, 'update']);
        Route::delete('/services/{service}', [ServiceController::class, 'destroy']);

        Route::get('/employees', [AdminController::class, 'employeesIndex']);
        Route::post('/employees', [AdminController::class, 'employeesStore']);
        Route::patch('/employees/{employee}', [AdminController::class, 'employeesUpdate']);
        Route::delete('/employees/{employee}', [AdminController::class, 'employeesDestroy']);
    });
});
