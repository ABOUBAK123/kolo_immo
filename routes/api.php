<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\OwnerPropertyController;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - KOLO IMMO v1
|--------------------------------------------------------------------------
|
| All API routes are prefixed with /api/v1
|
*/

Route::prefix('v1')->name('api.v1.')->group(function () {

    // ─── PAYMENT LOGOS (Public) ──────────────────────────────────────────────
    Route::get('/payment-logos', function () {
        $methods = ['orange_money', 'wave', 'mtn_momo', 'moov_money'];
        $dir     = public_path('payment_logos');
        $logos   = [];

        foreach ($methods as $method) {
            $files = is_dir($dir) ? glob("{$dir}/{$method}.*") : [];
            if (!empty($files)) {
                $logos[$method] = url('payment_logos/' . basename($files[0]));
            }
        }

        return response()->json(['data' => $logos]);
    })->name('payment-logos');

    // ─── AUTH (Public) ────────────────────────────────────────────────────────
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('/register', [AuthController::class, 'register'])->name('register');
        Route::post('/login', [AuthController::class, 'login'])->name('login');
        Route::post('/verify-otp', [AuthController::class, 'verifyOtp'])->name('verify-otp');
    });

    // ─── AUTH (Protected) ─────────────────────────────────────────────────────
    Route::middleware('auth:sanctum')->prefix('auth')->name('auth.')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/me', [AuthController::class, 'me'])->name('me');
        Route::post('/profile', [AuthController::class, 'updateProfile'])->name('profile');
    });

    // ─── PROPERTIES (Public) ──────────────────────────────────────────────────
    Route::prefix('properties')->name('properties.')->group(function () {
        Route::get('/', [PropertyController::class, 'index'])->name('index');
        Route::get('/featured', [PropertyController::class, 'featured'])->name('featured');
        Route::get('/{property}', [PropertyController::class, 'show'])->name('show');
    });

    // ─── BOOKINGS (Protected) ─────────────────────────────────────────────────
    Route::middleware('auth:sanctum')->prefix('bookings')->name('bookings.')->group(function () {
        Route::post('/', [BookingController::class, 'store'])->name('store');
        Route::get('/', [BookingController::class, 'index'])->name('index');
        Route::get('/{booking}', [BookingController::class, 'show'])->name('show');
        Route::post('/{booking}/cancel', [BookingController::class, 'cancel'])->name('cancel');
        Route::post('/{booking}/confirm', [BookingController::class, 'confirm'])->name('confirm');
        Route::post('/{booking}/reject', [BookingController::class, 'reject'])->name('reject');
    });

    // ─── OWNER PROPERTY MANAGEMENT (Protected) ───────────────────────────────
    Route::middleware('auth:sanctum')->prefix('owner/properties')->name('owner.properties.')->group(function () {
        Route::get('/', [OwnerPropertyController::class, 'index'])->name('index');
        Route::post('/', [OwnerPropertyController::class, 'store'])->name('store');
        Route::get('/{property}', [OwnerPropertyController::class, 'show'])->name('show');
        Route::post('/{property}', [OwnerPropertyController::class, 'update'])->name('update');
        Route::delete('/{property}', [OwnerPropertyController::class, 'destroy'])->name('destroy');
        Route::post('/{property}/toggle-status', [OwnerPropertyController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/{property}/photos', [OwnerPropertyController::class, 'uploadPhotos'])->name('photos.upload');
        Route::delete('/{property}/photos/{photo}', [OwnerPropertyController::class, 'deletePhoto'])->name('photos.delete');
    });

    // ─── OWNER COMMISSIONS ───────────────────────────────────────────────────
    Route::middleware('auth:sanctum')->get('/owner/commissions', function (\Illuminate\Http\Request $request) {
        $user  = $request->user();
        $rate  = 0.03;

        $bookings = \App\Models\Booking::whereHas('property', fn($q) => $q->where('owner_id', $user->id))
            ->whereIn('status', ['confirmed', 'completed'])
            ->with(['property:id,title', 'tenant:id,name'])
            ->orderByDesc('created_at')
            ->get();

        $rows = $bookings->map(fn($b) => [
            'id'         => $b->id,
            'reference'  => $b->reference,
            'property'   => $b->property?->title,
            'tenant'     => $b->tenant?->name,
            'check_in'   => $b->check_in,
            'check_out'  => $b->check_out,
            'nights'     => $b->nights,
            'total'      => (float) $b->total_amount,
            'commission' => round((float) $b->total_amount * $rate),
            'status'     => $b->status,
        ]);

        $now = now();
        return response()->json([
            'data' => [
                'commission_rate'  => $rate,
                'total_commission' => $rows->sum('commission'),
                'total_revenue'    => $rows->sum('total'),
                'monthly_commission' => $rows->filter(fn($r) => substr($r['check_in'], 0, 7) === $now->format('Y-m'))->sum('commission'),
                'monthly_revenue'    => $rows->filter(fn($r) => substr($r['check_in'], 0, 7) === $now->format('Y-m'))->sum('total'),
                'bookings_count'   => $rows->count(),
                'rows'             => $rows->values(),
            ],
        ]);
    })->name('owner.commissions');

    // ─── PAYMENTS ─────────────────────────────────────────────────────────────
    Route::middleware('auth:sanctum')->prefix('payments')->name('payments.')->group(function () {
        Route::post('/initiate/{booking}', [PaymentController::class, 'process'])->name('initiate');
    });

    // CinetPay Webhook (public, no auth)
    Route::post('/payments/notify', [PaymentController::class, 'notify'])->name('payments.notify');
});
