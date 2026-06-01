<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DisputeController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\RenewalController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SearchAlertController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\DisputeController as AdminDisputeController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;

// ─── LOCALE ───────────────────────────────────────────────────────────────────
Route::post('/language/{locale}', [LocaleController::class, 'change'])->name('language.change');
Route::post('/currency/{code}', function (string $code) {
    $allowed = array_keys(\App\Helpers\Currency::$currencies);
    if (!in_array($code, $allowed)) abort(422);
    session(['currency' => $code]);
    if (auth()->check()) auth()->user()->update(['currency' => $code]);
    return back();
})->name('currency.change');

// ─── PUBLIC ROUTES ────────────────────────────────────────────────────────────

Route::get('/', function () {
    $featured = \App\Models\Property::active()
        ->featured()
        ->with(['photos', 'amenities'])
        ->withCount('reviews')
        ->orderByDesc('rating_avg')
        ->take(6)
        ->get();

    $cities = \App\Models\Property::active()
        ->selectRaw('city, COUNT(*) as count')
        ->groupBy('city')
        ->orderByDesc('count')
        ->take(8)
        ->get();

    return view('home', compact('featured', 'cities'));
})->name('home');

Route::get('/properties', [PropertyController::class, 'index'])->name('properties.index');
Route::get('/properties/map', [PropertyController::class, 'mapView'])->name('properties.map');
Route::get('/properties/map-data', [PropertyController::class, 'mapData'])->name('properties.map-data');
Route::get('/properties/{property}', [PropertyController::class, 'show'])->name('properties.show');

// ─── SOCIAL AUTH (OAuth pour mobile WebView) ─────────────────────────────────
Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])
    ->name('social.redirect')
    ->where('provider', 'google|facebook|github');

Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])
    ->name('social.callback')
    ->where('provider', 'google|facebook|github');

Route::get('/about',   fn() => view('pages.about'))->name('about');
Route::get('/contact', fn() => view('pages.contact'))->name('contact');
Route::get('/terms',   fn() => view('pages.terms'))->name('terms');
Route::get('/privacy', fn() => view('pages.privacy'))->name('privacy');
Route::get('/faq',     fn() => view('pages.faq'))->name('faq');

// ─── PLANS / ABONNEMENTS ─────────────────────────────────────────────────────
Route::get('/plans', [SubscriptionController::class, 'plans'])->name('subscriptions.plans');
Route::post('/subscriptions/notify', [SubscriptionController::class, 'notify'])->name('subscriptions.notify')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

Route::middleware('auth')->group(function () {
    Route::get('/my-subscription', [SubscriptionController::class, 'mySubscription'])->name('subscriptions.my');
    Route::post('/subscriptions/subscribe', [SubscriptionController::class, 'subscribe'])->name('subscriptions.subscribe');
    Route::get('/subscriptions/callback/{sub}', [SubscriptionController::class, 'callback'])->name('subscriptions.callback');
    Route::post('/subscriptions/cancel', [SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');
});

// ─── AUTH ROUTES ──────────────────────────────────────────────────────────────

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');

    // Mot de passe oublié
    Route::get('/forgot-password', [ForgotPasswordController::class, 'show'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendOtp'])->name('password.send-otp');
    Route::get('/forgot-password/verify', [ForgotPasswordController::class, 'showVerify'])->name('password.verify');
    Route::post('/forgot-password/reset', [ForgotPasswordController::class, 'reset'])->name('password.reset');
    Route::post('/forgot-password/resend', [ForgotPasswordController::class, 'resend'])->name('password.resend');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/verify-phone', [AuthController::class, 'showPhoneVerify'])->name('verify.phone');
    Route::post('/verify-phone', [AuthController::class, 'verifyPhone'])->name('verify.phone.post');
    Route::post('/verify-phone/resend', [AuthController::class, 'resendOtp'])->name('verify.phone.resend');
});

// ─── TENANT / GENERAL AUTH ROUTES ────────────────────────────────────────────

Route::middleware('auth')->group(function () {
    // Tenant Dashboard
    Route::get('/dashboard', [DashboardController::class, 'tenantDashboard'])->name('dashboard');

    // Bookings (tenant)
    Route::get('/bookings', [BookingController::class, 'myBookings'])->name('bookings.my-bookings');
    Route::get('/bookings/create', [BookingController::class, 'create'])->name('bookings.create');
    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::patch('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
    Route::post('/bookings/{booking}/terminate', [RenewalController::class, 'terminate'])->name('bookings.terminate');

    // Renewals
    Route::get('/renewals/{renewal}', [RenewalController::class, 'show'])->name('renewals.show');
    Route::get('/bookings/{booking}/renew', [RenewalController::class, 'create'])->name('renewals.create');
    Route::post('/bookings/{booking}/renew', [RenewalController::class, 'store'])->name('renewals.store');
    Route::patch('/renewals/{renewal}/accept', [RenewalController::class, 'accept'])->name('renewals.accept');
    Route::patch('/renewals/{renewal}/reject', [RenewalController::class, 'reject'])->name('renewals.reject');

    // Payments
    Route::get('/payments/{booking}/initiate', [PaymentController::class, 'initiate'])->name('payments.initiate');
    Route::post('/payments/{booking}/process', [PaymentController::class, 'process'])->name('payments.process');
    Route::get('/payments/{bookingId}/success', [PaymentController::class, 'success'])->name('payments.success');
    Route::get('/payments/{bookingId}/failed', [PaymentController::class, 'failed'])->name('payments.failed');

    // Contracts
    Route::get('/contracts/{contract}', [ContractController::class, 'show'])->name('contracts.show');
    Route::post('/contracts/{contract}/request-sign', [ContractController::class, 'requestSign'])->name('contracts.request-sign');
    Route::post('/contracts/{contract}/sign', [ContractController::class, 'sign'])->name('contracts.sign');
    Route::get('/contracts/{contract}/download', [ContractController::class, 'download'])->name('contracts.download');
    Route::get('/contracts/{contract}/inspection/{type}', [ContractController::class, 'inspectionForm'])->name('contracts.inspection');
    Route::post('/contracts/{contract}/inspection/{type}', [ContractController::class, 'saveInspection'])->name('contracts.inspection.save');

    // Messages
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/{conversation}', [MessageController::class, 'show'])->name('messages.show');
    Route::post('/messages/{conversation}/send', [MessageController::class, 'send'])->name('messages.send');
    Route::post('/messages/{conversation}/read', [MessageController::class, 'markRead'])->name('messages.read');
    // Polling : retourne les messages depuis un ID donné
    Route::get('/messages/{conversation}/poll', [MessageController::class, 'poll'])->name('messages.poll');

    // Reviews (tenant → property)
    Route::get('/reviews/create/{booking}', [ReviewController::class, 'create'])->name('reviews.create');
    Route::post('/reviews/{booking}', [ReviewController::class, 'store'])->name('reviews.store');
    Route::post('/reviews/{review}/reply', [ReviewController::class, 'reply'])->name('reviews.reply');
    Route::post('/reviews/{review}/flag', [ReviewController::class, 'flag'])->name('reviews.flag');

    // Disputes
    Route::get('/disputes/create/{booking}', [DisputeController::class, 'create'])->name('disputes.create');
    Route::post('/disputes/{booking}', [DisputeController::class, 'store'])->name('disputes.store');
    Route::get('/disputes/{dispute}', [DisputeController::class, 'show'])->name('disputes.show');

    // Favorites
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/favorites/{property}/toggle', [FavoriteController::class, 'toggle'])->name('favorites.toggle');

    // Wallet
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
    Route::post('/wallet/topup', [WalletController::class, 'topup'])->name('wallet.topup');
    Route::get('/wallet/topup/success', [WalletController::class, 'topupSuccess'])->name('wallet.topup.success');
    Route::post('/wallet/{booking}/pay', [WalletController::class, 'payBooking'])->name('wallet.pay-booking');

    // Search alerts
    Route::get('/search-alerts', [SearchAlertController::class, 'index'])->name('search-alerts.index');
    Route::post('/search-alerts', [SearchAlertController::class, 'store'])->name('search-alerts.store');
    Route::patch('/search-alerts/{alert}/toggle', [SearchAlertController::class, 'toggle'])->name('search-alerts.toggle');
    Route::delete('/search-alerts/{alert}', [SearchAlertController::class, 'destroy'])->name('search-alerts.destroy');

    // Profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'updateInfo'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');

    // KYC Profile
    Route::get('/profile/kyc', function () {
        return view('profile.kyc');
    })->name('profile.kyc');

    Route::post('/profile/kyc', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'type'          => ['required', 'in:cni,passport,residence_permit,title_deed,lease'],
            'document_file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'selfie_file'   => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ]);

        $user = auth()->user();

        $docPath    = $request->file('document_file')->store('kyc/documents', 'public');
        $selfiePath = $request->hasFile('selfie_file')
            ? $request->file('selfie_file')->store('kyc/selfies', 'public')
            : null;

        \App\Models\KycDocument::create([
            'user_id'       => $user->id,
            'type'          => $request->type,
            'document_path' => $docPath,
            'selfie_path'   => $selfiePath,
            'status'        => 'pending',
        ]);

        $user->update(['kyc_status' => 'pending']);

        return back()->with('success', 'Vos documents ont été soumis. Vous serez notifié une fois la vérification effectuée.');
    })->name('profile.kyc.submit');
});

// ─── OWNER ROUTES ─────────────────────────────────────────────────────────────

Route::middleware(['auth', 'owner'])->prefix('owner')->name('owner.')->group(function () {
    // Owner dashboard
    Route::get('/dashboard', [DashboardController::class, 'ownerDashboard'])->name('dashboard');

    // Properties management
    Route::get('/properties', [PropertyController::class, 'ownerIndex'])->name('properties.index');
    Route::get('/properties/create', [PropertyController::class, 'create'])->name('properties.create');
    Route::post('/properties', [PropertyController::class, 'store'])->name('properties.store');
    Route::get('/properties/{property}/edit', [PropertyController::class, 'edit'])->name('properties.edit');
    Route::put('/properties/{property}', [PropertyController::class, 'update'])->name('properties.update');
    Route::delete('/properties/{property}', [PropertyController::class, 'destroy'])->name('properties.destroy');
    Route::post('/properties/{property}/photos', [PropertyController::class, 'uploadPhotos'])->name('properties.photos.upload');
    Route::delete('/properties/{property}/photos/{photo}', [PropertyController::class, 'deletePhoto'])->name('properties.photos.delete');
    Route::get('/properties/{property}/availability', [PropertyController::class, 'manageAvailability'])->name('properties.availability');
    Route::post('/properties/{property}/availability', [PropertyController::class, 'saveBlockedDates'])->name('properties.availability.save');
    Route::delete('/properties/{property}/availability/{blockedDate}', [PropertyController::class, 'deleteBlockedDate'])->name('properties.availability.delete');
    Route::post('/properties/{property}/toggle-status', [PropertyController::class, 'toggleStatus'])->name('properties.toggle-status');

    // Commissions
    Route::get('/commissions', [DashboardController::class, 'ownerCommissions'])->name('commissions');

    // Analytics exports
    Route::get('/analytics/csv', [DashboardController::class, 'exportCsv'])->name('analytics.csv');
    Route::get('/analytics/pdf', [DashboardController::class, 'exportPdf'])->name('analytics.pdf');

    // Bookings management (owner)
    Route::get('/bookings', [BookingController::class, 'ownerBookings'])->name('bookings.index');
    Route::get('/bookings/pending', [BookingController::class, 'pendingBookings'])->name('bookings.pending');
    Route::patch('/bookings/{booking}/confirm', [BookingController::class, 'confirm'])->name('bookings.confirm');
    Route::patch('/bookings/{booking}/reject', [BookingController::class, 'reject'])->name('bookings.reject');

    // Reviews owner → tenant
    Route::get('/bookings/{booking}/review-tenant', [ReviewController::class, 'createOwnerReview'])->name('reviews.owner.create');
    Route::post('/bookings/{booking}/review-tenant', [ReviewController::class, 'storeOwnerReview'])->name('reviews.owner.store');
});

// ─── ADMIN ROUTES ─────────────────────────────────────────────────────────────

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Users
    Route::get('/users', [AdminController::class, 'users'])->name('users.index');
    Route::get('/users/{user}', [AdminController::class, 'showUser'])->name('users.show');
    Route::patch('/users/{user}/toggle-ban', [AdminController::class, 'toggleBan'])->name('users.toggle-ban');
    Route::patch('/users/{user}/toggle-active', [AdminController::class, 'toggleActive'])->name('users.toggle-active');

    // KYC
    Route::get('/kyc', [AdminController::class, 'kycList'])->name('kyc.index');
    Route::get('/kyc/{kycDocument}', [AdminController::class, 'showKyc'])->name('kyc.show');
    Route::post('/kyc/{kycDocument}/verify', [AdminController::class, 'verifyKyc'])->name('kyc.verify');

    // Properties
    Route::get('/properties', [AdminController::class, 'properties'])->name('properties.index');
    Route::post('/properties/{property}/toggle-featured', [AdminController::class, 'toggleFeatured'])->name('properties.toggle-featured');
    Route::post('/properties/{property}/toggle-status', [AdminController::class, 'togglePropertyStatus'])->name('properties.toggle-status');
    Route::post('/properties/{property}/suspend', [AdminController::class, 'suspendProperty'])->name('properties.suspend');
    Route::post('/properties/{property}/under-review', [AdminController::class, 'underReviewProperty'])->name('properties.under-review');
    Route::post('/properties/{property}/verify', [AdminController::class, 'verifyProperty'])->name('properties.verify');
    Route::post('/properties/{property}/reject', [AdminController::class, 'rejectProperty'])->name('properties.reject');

    // Bookings
    Route::get('/bookings', [AdminController::class, 'bookings'])->name('bookings.index');

    // Payouts (distribution des fonds)
    Route::get('/payouts', [\App\Http\Controllers\Admin\PayoutController::class, 'index'])->name('payouts.index');
    Route::post('/payouts/{booking}/release', [\App\Http\Controllers\Admin\PayoutController::class, 'release'])->name('payouts.release');
    Route::post('/payouts/bulk-release', [\App\Http\Controllers\Admin\PayoutController::class, 'bulkRelease'])->name('payouts.bulk-release');

    // Disputes
    Route::get('/disputes', [AdminDisputeController::class, 'index'])->name('disputes.index');
    Route::get('/disputes/{dispute}', [AdminDisputeController::class, 'show'])->name('disputes.show');
    Route::patch('/disputes/{dispute}/status', [AdminDisputeController::class, 'updateStatus'])->name('disputes.status');
    Route::patch('/disputes/{dispute}/resolve', [AdminDisputeController::class, 'resolve'])->name('disputes.resolve');

    // Reviews moderation
    Route::get('/reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
    Route::post('/reviews/{review}/approve', [AdminReviewController::class, 'approve'])->name('reviews.approve');
    Route::delete('/reviews/{review}', [AdminReviewController::class, 'destroy'])->name('reviews.destroy');

    // Reports
    Route::get('/reports', [AdminController::class, 'reports'])->name('reports.index');

    // Settings (API configurations)
    Route::get('/settings', [SettingsController::class, 'show'])->name('settings.show');
    Route::put('/settings/{section}', [SettingsController::class, 'update'])->name('settings.update');
    Route::post('/settings/payment-logos', [SettingsController::class, 'uploadPaymentLogo'])->name('settings.payment-logos.upload');
    Route::delete('/settings/payment-logos', [SettingsController::class, 'deletePaymentLogo'])->name('settings.payment-logos.delete');
});

// ─── PAYMENT / WALLET WEBHOOKS (public, no CSRF) ─────────────────────────────
Route::post('/payments/notify', [PaymentController::class, 'notify'])
    ->name('payments.notify')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

Route::post('/wallet/topup/webhook', [WalletController::class, 'topupWebhook'])
    ->name('wallet.topup.webhook')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
