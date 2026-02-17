<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\SocialLinkController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public Routes
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/refresh', [AuthController::class, 'refresh']);

// Public Data
Route::get('/offices', [OfficeController::class, 'index']); // Public list of offices
Route::get('/faqs', [FaqController::class, 'index']);
Route::get('/social-links', [SocialLinkController::class, 'index']);
Route::get('/payment-methods', [PaymentMethodController::class, 'index']);
Route::get('/settings/{key}', [SettingController::class, 'show']); // Get specific setting (e.g., general)

// Public Shipment Tracking
Route::get('/shipments/track/{code}', [ShipmentController::class, 'track']);

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // User Profile
    Route::get('/me', function (Request $request) {
        return $request->user()->load('driverProfile');
    });

    // Users
    Route::apiResource('users', UserController::class);

    // Offices (Admin management)
    Route::apiResource('offices', OfficeController::class)->except(['index']); // Index is public

    // Drivers
    Route::apiResource('drivers', DriverController::class);

    // Shipments
    Route::apiResource('shipments', ShipmentController::class);

    // Invoices
    Route::apiResource('invoices', InvoiceController::class);

    // Settings (Admin only usually, but for now open to auth)
    Route::get('/settings', [SettingController::class, 'index']);
    Route::post('/settings', [SettingController::class, 'store']);

    // Social Links & FAQs (Admin management)
    Route::apiResource('social-links', SocialLinkController::class)->except(['index']);
    Route::apiResource('faqs', FaqController::class)->except(['index']);
    Route::apiResource('payment-methods', PaymentMethodController::class)->except(['index']);
});
