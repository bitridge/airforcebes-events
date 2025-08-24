<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\CheckInController;
use App\Http\Controllers\Admin\EventController as AdminEventController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AttendeeController;
use App\Http\Controllers\Admin\RegistrationController as AdminRegistrationController;
use App\Http\Controllers\Admin\ReportingController;
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
               Route::get('/registrations', [AdminRegistrationController::class, 'adminIndex'])->name('registrations.index');
           Route::get('/events/{event}/registrations', [AdminRegistrationController::class, 'eventRegistrations'])->name('events.registrations');
           
           // Registration management routes
           Route::get('/registrations/{registration}', [AdminRegistrationController::class, 'show'])->name('registrations.show');
           Route::get('/registrations/{registration}/edit', [AdminRegistrationController::class, 'edit'])->name('registrations.edit');
           Route::put('/registrations/{registration}', [AdminRegistrationController::class, 'update'])->name('registrations.update');
           Route::delete('/registrations/{registration}', [AdminRegistrationController::class, 'destroy'])->name('registrations.destroy');
           Route::post('/registrations/{registration}/resend-email', [AdminRegistrationController::class, 'resendEmail'])->name('registrations.resend-email');
           Route::post('/registrations/bulk-action', [AdminRegistrationController::class, 'bulkAction'])->name('registrations.bulk-action');
           Route::post('/registrations/export-csv', [AdminRegistrationController::class, 'exportCsv'])->name('registrations.export-csv');
           Route::post('/registrations/export-pdf', [AdminRegistrationController::class, 'exportPdf'])->name('registrations.export-pdf');
           
                       // QR Code routes
            Route::get('/registrations/{registration}/qr-view', [AdminRegistrationController::class, 'showQrCode'])->name('registrations.qr-view');
            Route::get('/registrations/{registration}/qr-code', [AdminRegistrationController::class, 'downloadQrCode'])->name('registrations.qr-code');
            Route::get('/registrations/{registration}/qr-print', [AdminRegistrationController::class, 'printQrCode'])->name('registrations.qr-print');
            Route::get('/registrations/{registration}/print-card', [AdminRegistrationController::class, 'printRegistrationCard'])->name('registrations.print-card');
            
            // Settings management routes
            Route::get('/settings', [App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('settings.index');
            Route::post('/settings/{group}', [App\Http\Controllers\Admin\SettingsController::class, 'updateGroup'])->name('settings.update-group');
            Route::put('/settings/{key}', [App\Http\Controllers\Admin\SettingsController::class, 'updateSetting'])->name('settings.update');
            Route::post('/settings/test-smtp', [App\Http\Controllers\Admin\SettingsController::class, 'testSmtp'])->name('settings.test-smtp');
            Route::post('/settings/export', [App\Http\Controllers\Admin\SettingsController::class, 'export'])->name('settings.export');
            Route::post('/settings/import', [App\Http\Controllers\Admin\SettingsController::class, 'import'])->name('settings.import');
            Route::post('/settings/backup', [App\Http\Controllers\Admin\SettingsController::class, 'backup'])->name('settings.backup');
            Route::post('/settings/restore', [App\Http\Controllers\Admin\SettingsController::class, 'restore'])->name('settings.restore');
            Route::post('/settings/reset-defaults', [App\Http\Controllers\Admin\SettingsController::class, 'resetToDefaults'])->name('settings.reset-defaults');
            Route::post('/settings/clear-cache', [App\Http\Controllers\Admin\SettingsController::class, 'clearCache'])->name('settings.clear-cache');
            Route::get('/settings/cache-stats', [App\Http\Controllers\Admin\SettingsController::class, 'getCacheStats'])->name('settings.cache-stats');
            Route::get('/settings/backups', [App\Http\Controllers\Admin\SettingsController::class, 'getBackups'])->name('settings.backups');
            Route::get('/settings/backup/{filename}/download', [App\Http\Controllers\Admin\SettingsController::class, 'downloadBackup'])->name('settings.download-backup');
           
           // Attendee management routes
           Route::get('/attendees', [AttendeeController::class, 'index'])->name('attendees.index');
           Route::get('/attendees/{attendee}', [AttendeeController::class, 'show'])->name('attendees.show');
           Route::get('/attendees/{attendee}/edit', [AttendeeController::class, 'edit'])->name('attendees.edit');
           Route::put('/attendees/{attendee}', [AttendeeController::class, 'update'])->name('attendees.update');
           Route::post('/attendees/{attendee}/communication', [AttendeeController::class, 'sendCommunication'])->name('attendees.communication');
           Route::post('/attendees/bulk-communication', [AttendeeController::class, 'bulkCommunication'])->name('attendees.bulk-communication');
           Route::post('/attendees/export-csv', [AttendeeController::class, 'exportCsv'])->name('attendees.export-csv');
           
           // Reporting routes
           Route::get('/reports', [ReportingController::class, 'index'])->name('reports.index');
           Route::get('/reports/events', [ReportingController::class, 'eventReports'])->name('reports.events');
           Route::get('/reports/attendee-analytics', [ReportingController::class, 'attendeeAnalytics'])->name('reports.attendee-analytics');
           Route::get('/reports/dashboard-widgets', [ReportingController::class, 'dashboardWidgets'])->name('reports.dashboard-widgets');
           Route::post('/reports/export-event', [ReportingController::class, 'exportEventReport'])->name('reports.export-event');
           Route::post('/reports/export-attendee-analytics', [ReportingController::class, 'exportAttendeeAnalytics'])->name('reports.export-attendee-analytics');
    
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
