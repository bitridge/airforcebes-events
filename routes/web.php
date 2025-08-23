<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\CheckInController;
use App\Http\Controllers\Admin\EventController as AdminEventController;
use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('welcome');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Public routes
Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/events/{event:slug}', [EventController::class, 'show'])->name('events.show');

// QR Code verification (public endpoint)
Route::get('/qr/verify/{hash}', function ($hash) {
    $registration = \App\Models\Registration::where('qr_security_hash', $hash)->first();
    
    if (!$registration) {
        abort(404, 'QR code not found');
    }
    
    return view('qr.verify', compact('registration'));
})->name('qr.verify');

// Authenticated user routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Registration routes
    Route::post('/events/{event}/register', [RegistrationController::class, 'store'])->name('registrations.store');
    Route::get('/my-registrations', [RegistrationController::class, 'index'])->name('registrations.index');
    Route::get('/registrations/{registration}/qr-code', [RegistrationController::class, 'downloadQrCode'])->name('registrations.qr-code');
    Route::get('/registrations/{registration}/qr-view', [RegistrationController::class, 'showQrCode'])->name('registrations.qr-view');
    Route::get('/registrations/{registration}/qr-print', [RegistrationController::class, 'printQrCode'])->name('registrations.qr-print');
    Route::delete('/registrations/{registration}', [RegistrationController::class, 'destroy'])->name('registrations.destroy');

    // Check-in routes
    Route::get('/check-in', [CheckInController::class, 'index'])->name('checkin.index');
    Route::post('/check-in', [CheckInController::class, 'store'])->name('checkin.store');
});

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
               Route::resource('events', AdminEventController::class);
           Route::post('/events/{event}/duplicate', [AdminEventController::class, 'duplicate'])->name('events.duplicate');
           Route::post('/events/{event}/export-attendees', [AdminEventController::class, 'exportAttendees'])->name('events.export-attendees');
           Route::post('/events/{event}/export-checkins', [AdminEventController::class, 'exportCheckInReport'])->name('events.export-checkins');
           Route::post('/events/bulk-action', [AdminEventController::class, 'bulkAction'])->name('events.bulk-action');
    Route::get('/registrations', [RegistrationController::class, 'adminIndex'])->name('registrations.index');
    Route::get('/events/{event}/registrations', [RegistrationController::class, 'eventRegistrations'])->name('events.registrations');
    
    // Check-in routes
    Route::get('/check-in', [CheckInController::class, 'index'])->name('check-in.index');
    Route::get('/check-in/manual', [CheckInController::class, 'manual'])->name('check-in.manual');
    Route::post('/check-in/scan', [CheckInController::class, 'scanQrCode'])->name('check-in.scan');
    Route::post('/check-in/code', [CheckInController::class, 'checkInByCode'])->name('check-in.code');
    Route::post('/check-in/bulk', [CheckInController::class, 'bulkCheckIn'])->name('check-in.bulk');
    Route::get('/check-in/search', [CheckInController::class, 'search'])->name('check-in.search');
    Route::get('/events/{event}/stats', [CheckInController::class, 'eventStats'])->name('events.stats');
    Route::get('/events/{event}/export', [CheckInController::class, 'exportReport'])->name('events.export');
});

require __DIR__.'/auth.php';
