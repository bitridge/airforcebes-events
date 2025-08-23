<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\CheckInController;
use App\Http\Controllers\Admin\EventController as AdminEventController;
use Illuminate\Support\Facades\Route;

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('welcome');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Public routes
Route::get('/events', [EventController::class, 'index'])->name('events.index');
Route::get('/events/{event:slug}', [EventController::class, 'show'])->name('events.show');

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
    Route::delete('/registrations/{registration}', [RegistrationController::class, 'destroy'])->name('registrations.destroy');

    // Check-in routes
    Route::get('/check-in', [CheckInController::class, 'index'])->name('checkin.index');
    Route::post('/check-in', [CheckInController::class, 'store'])->name('checkin.store');
});

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');
    
    Route::resource('events', AdminEventController::class);
    Route::get('/registrations', [RegistrationController::class, 'adminIndex'])->name('registrations.index');
    Route::get('/events/{event}/registrations', [RegistrationController::class, 'eventRegistrations'])->name('events.registrations');
    Route::get('/events/{event}/check-ins', [CheckInController::class, 'eventCheckIns'])->name('events.checkins');
});

require __DIR__.'/auth.php';
