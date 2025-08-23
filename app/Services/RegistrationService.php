<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Support\Str;

class RegistrationService
{
    /**
     * Register user for event
     */
    public function registerUser(Event $event, User $user): Registration
    {
        $registration = new Registration([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'registration_code' => $this->generateRegistrationCode(),
            'status' => 'confirmed',
            'registered_at' => now(),
        ]);

        $registration->save();

        // Here you would typically send confirmation email
        // and generate QR code

        return $registration;
    }

    /**
     * Generate unique registration code
     */
    protected function generateRegistrationCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (Registration::where('registration_code', $code)->exists());

        return $code;
    }

    /**
     * Cancel registration
     */
    public function cancelRegistration(Registration $registration): bool
    {
        $registration->status = 'cancelled';
        $registration->cancelled_at = now();
        
        return $registration->save();
    }

    /**
     * Get user registrations
     */
    public function getUserRegistrations(User $user)
    {
        return $user->registrations()
            ->with('event')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
