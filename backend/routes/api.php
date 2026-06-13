<?php

use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\HotelController;
use App\Http\Controllers\Api\PackageController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\TestimonialController;
use App\Http\Controllers\Api\TeamController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| LNC API Routes
|--------------------------------------------------------------------------
|
| These endpoints are consumed by the PHP frontend (data.php) and
| optionally by future mobile/SPA clients.
|
| Base: /api/v1/
|
*/

Route::prefix('v1')->group(function () {

    // ── Tour Packages ──────────────────────────────────────────────
    Route::get('/packages',           [PackageController::class, 'index']);
    Route::get('/packages/{code}',    [PackageController::class, 'show']);

    // ── Hotels ────────────────────────────────────────────────────
    Route::get('/hotels',             [HotelController::class, 'index']);

    // ── Testimonials ──────────────────────────────────────────────
    Route::get('/testimonials',       [TestimonialController::class, 'index']);

    // ── Team ──────────────────────────────────────────────────────
    Route::get('/team',               [TeamController::class, 'index']);

    // ── Bookings ──────────────────────────────────────────────────
    Route::post('/bookings',          [BookingController::class, 'store']);
    Route::get('/bookings/{ref}',     [BookingController::class, 'show']);

    // ── Payments ──────────────────────────────────────────────────
    Route::post('/payments/webhook',  [PaymentController::class, 'webhook']);

    // ── Health ────────────────────────────────────────────────────
    Route::get('/health', function () {
        return response()->json([
            'status'    => 'ok',
            'service'   => 'LNC API',
            'timestamp' => now()->toISOString(),
        ]);
    });
});
