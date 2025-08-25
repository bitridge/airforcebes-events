<?php

namespace App\Services;

use App\Models\CheckIn;
use App\Models\Registration;
use Illuminate\Support\Facades\Log;

class CheckInService
{
    /**
     * Check in user via registration code
     */
    public function checkInByCode(string $registrationCode, string $method = 'qr_code'): ?CheckIn
    {
        $registration = Registration::where('registration_code', $registrationCode)
            ->where('status', 'confirmed')
            ->first();

        if (!$registration) {
            return null;
        }

        // Check if already checked in
        if ($registration->checkIn) {
            return $registration->checkIn;
        }

        $checkIn = new CheckIn([
            'registration_id' => $registration->id,
            'checked_in_at' => now(),
            'check_in_method' => $method,
        ]);

        $checkIn->save();

        Log::info('User checked in', [
            'registration_id' => $registration->id,
            'user_id' => $registration->user_id,
            'event_id' => $registration->event_id,
            'method' => $method,
        ]);

        return $checkIn;
    }

    /**
     * Check in user manually
     */
    public function checkInManually(Registration $registration): ?CheckIn
    {
        return $this->checkInByCode($registration->registration_code, 'manual');
    }

    /**
     * Check in user via registration
     */
    public function checkInUser(Registration $registration, string $method = 'qr_code', string $notes = ''): CheckIn
    {
        // Check if already checked in
        if ($registration->checkIn) {
            return $registration->checkIn;
        }

        $checkIn = new CheckIn([
            'registration_id' => $registration->id,
            'checked_in_at' => now(),
            'check_in_method' => $method,
            'notes' => $notes,
            'checked_in_by' => auth()->id(),
        ]);

        $checkIn->save();

        Log::info('User checked in', [
            'registration_id' => $registration->id,
            'user_id' => $registration->user_id,
            'event_id' => $registration->event_id,
            'method' => $method,
            'notes' => $notes,
            'checked_in_by' => auth()->id(),
        ]);

        return $checkIn;
    }

    /**
     * Get check-in statistics for event
     */
    public function getEventCheckInStats($eventId): array
    {
        $totalRegistrations = Registration::where('event_id', $eventId)
            ->where('status', 'confirmed')
            ->count();

        $checkedIn = CheckIn::whereHas('registration', function ($query) use ($eventId) {
            $query->where('event_id', $eventId);
        })->count();

        return [
            'total_registrations' => $totalRegistrations,
            'checked_in' => $checkedIn,
            'not_checked_in' => $totalRegistrations - $checkedIn,
            'check_in_rate' => $totalRegistrations > 0 ? round(($checkedIn / $totalRegistrations) * 100, 2) : 0,
        ];
    }
}
