<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\CourtController;
use App\Http\Controllers\FinancialReportController;
use App\Http\Controllers\SocialLinkController;
use App\Http\Controllers\VenueController;
use App\Models\Court;
use App\Models\SocialLink;
use App\Models\Venue;
use Laravel\Passkeys\Http\Controllers\PasskeyConfirmationController;
use Laravel\Passkeys\Http\Controllers\PasskeyLoginController;
use Laravel\Passkeys\Http\Controllers\PasskeyRegistrationController;


// Auth
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::get('/forgot-password', fn() => view('auth.forgot-password'))->name('password.request');
Route::post('/forgot-password', fn() => back()->with('status', 'Link dikirim!'))->middleware('throttle:5,1')->name('password.email');
Route::get('/reset-password/{token?}', fn($token = '') => view('auth.reset-password', compact('token')))->name('password.reset');
Route::post('/reset-password', fn() => redirect()->route('login'))->middleware('throttle:5,1')->name('password.update');
Route::get('/otp-verification', fn() => view('auth.otp-verification'))->name('otp.verification');
Route::get('/auth/google/redirect', [AuthController::class, 'redirectToGoogle'])->name('auth.google.redirect');
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
Route::get('/booking/availability', [BookingController::class, 'availability'])->name('booking.availability');
Route::post('/booking', [BookingController::class, 'store'])->middleware('throttle:10,1')->name('booking.store');
Route::get('/booking/{booking}/payment', [BookingController::class, 'payment'])->name('booking.payment');
Route::get('/upload-bukti-pembayaran', [BookingController::class, 'uploadForm'])->name('booking.upload-form');
Route::post('/upload-bukti-pembayaran', [BookingController::class, 'uploadProof'])
    ->middleware('throttle:5,1')
    ->name('booking.upload-proof');

// Passkeys
Route::get('/passkeys/login/options', [PasskeyLoginController::class, 'index'])
    ->middleware('guest:web')
    ->name('passkey.login-options');
Route::post('/passkeys/login', [PasskeyLoginController::class, 'store'])
    ->middleware('guest:web')
    ->name('passkey.login');

Route::middleware(['auth:web', 'auth.custom'])->group(function () {
    Route::get('/passkeys/confirm/options', [PasskeyConfirmationController::class, 'index'])
        ->name('passkey.confirm-options');
    Route::post('/passkeys/confirm', [PasskeyConfirmationController::class, 'store'])
        ->name('passkey.confirm');
    Route::get('/user/passkeys/options', [PasskeyRegistrationController::class, 'index'])
        ->name('passkey.registration-options');
    Route::post('/user/passkeys', [PasskeyRegistrationController::class, 'store'])
        ->name('passkey.store');
    Route::delete('/user/passkeys/{passkey}', [PasskeyRegistrationController::class, 'destroy'])
        ->name('passkey.destroy');
});

Route::get('/', function () {
    $courts = Court::active()->with(['venue.bankAccounts'])->orderBy('name')->get();
    $venues = Venue::active()->with('heroImages')->orderBy('name')->get();
    $primaryVenue = $venues->first();
    $venueMapPoints = $venues->map(fn (Venue $venue) => [
        'name' => $venue->name,
        'address' => $venue->address,
        'latitude' => $venue->latitude,
        'longitude' => $venue->longitude,
    ])->values();
    $socialLinks = SocialLink::active()->orderBy('sort_order')->orderBy('name')->get();

    return view('booking.home', compact('courts', 'venues', 'primaryVenue', 'venueMapPoints', 'socialLinks'));
})->name('home');
Route::get('/jadwal', function () {
    $courts = Court::active()->with(['venue.bankAccounts'])->orderBy('name')->get();
    $venues = Venue::active()->orderBy('name')->get();
    $primaryVenue = $venues->first();
    $scheduleCourts = $courts->map(fn (Court $court) => [
        'ulid' => $court->ulid,
        'name' => $court->name,
        'location' => $court->venue?->ulid,
        'area' => $court->venue?->address ?? 'Alamat venue belum tersedia',
        'price' => $court->price_per_hour,
        'paymentReady' => $court->venue?->is_active && ($court->venue->qris_path || $court->venue->bankAccounts->isNotEmpty()),
    ])->values();

    return view('booking.schedule', compact('courts', 'venues', 'primaryVenue', 'scheduleCourts'));
})->name('schedule');

// Protected routes
Route::middleware(['auth.custom', 'auto.logout'])->group(function () {
    Route::get('/dashboard', fn() => view('dashboard'))->name('dashboard');

    Route::middleware(['role:superadmin,staff'])->group(function () {
        Route::get('/booking-calendar', [BookingController::class, 'calendar'])->name('bookings.calendar');
        Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
        Route::get('/bookings/create', [BookingController::class, 'createStaffBooking'])->name('bookings.create');
        Route::post('/bookings', [BookingController::class, 'storeStaffBooking'])->name('bookings.store');
    });

    // Menu management - hanya admin
    Route::middleware(['role:superadmin'])->group(function () {
        Route::resource('/courts', CourtController::class)->except(['show']);
        Route::post('/bookings/{booking}/verify', [BookingController::class, 'verify'])->name('bookings.verify');
        Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
        Route::get('/bookings/{booking}/proof', [BookingController::class, 'proof'])->name('bookings.proof');
        Route::get('/financial-reports', [FinancialReportController::class, 'index'])->name('financial-reports.index');
        Route::resource('/venues', VenueController::class)->except(['show']);
        Route::resource('/social-links', SocialLinkController::class)->except(['show']);
        Route::resource('/menus', MenuController::class);
        Route::resource('/roles', RoleController::class);
        Route::post('/permissions/sync-routes', [PermissionController::class, 'syncRoutes'])->name('permissions.sync-routes');
        Route::resource('/permissions', PermissionController::class);
    });

// Keep-alive untuk reset session timeout
Route::post('/keep-alive', function () {
    session(['last_activity' => now()->timestamp]);
    return response()->json(['status' => 'ok']);
})->middleware(['auth.custom'])->name('keep.alive');

    // Modul Users (Permissions ditangani otomatis oleh Trait HasAutoPermissions di UserController)
    Route::resource('/users', UserController::class);
});
