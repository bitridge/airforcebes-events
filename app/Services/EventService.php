<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Registration;

class EventService
{
    /**
     * Get all published events
     */
    public function getPublishedEvents()
    {
        return Event::where('status', 'published')
            ->where('event_date', '>=', now())
            ->orderBy('event_date')
            ->get();
    }

    /**
     * Check if user can register for event
     */
    public function canUserRegister(Event $event, $userId): bool
    {
        // Check if registration is open
        if ($event->registration_deadline && $event->registration_deadline < now()) {
            return false;
        }

        // Check if user already registered
        if ($this->isUserRegistered($event, $userId)) {
            return false;
        }

        // Check capacity
        if ($event->max_capacity && $event->registrations()->count() >= $event->max_capacity) {
            return false;
        }

        return true;
    }

    /**
     * Check if user is already registered
     */
    public function isUserRegistered(Event $event, $userId): bool
    {
        return $event->registrations()->where('user_id', $userId)->exists();
    }

    /**
     * Get available spots for event
     */
    public function getAvailableSpots(Event $event): ?int
    {
        if (!$event->max_capacity) {
            return null; // Unlimited
        }

        return max(0, $event->max_capacity - $event->registrations()->count());
    }
}
