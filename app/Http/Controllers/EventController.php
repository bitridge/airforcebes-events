<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Registration;
use App\Models\CheckIn;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EventController extends Controller
{
    /**
     * Display a listing of published events.
     */
    public function index(Request $request): View
    {
        $query = Event::published()
            ->upcoming()
            ->with(['creator', 'confirmedRegistrations'])
            ->orderBy('start_date', 'asc');

        // Search functionality
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%")
                  ->orWhere('venue', 'like', "%{$searchTerm}%");
            });
        }

        // Date filter
        if ($request->filled('date_from')) {
            $query->where('start_date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->where('start_date', '<=', $request->date_to);
        }

        // Venue filter
        if ($request->filled('venue')) {
            $query->where('venue', 'like', "%{$request->venue}%");
        }

        // Category filter (if implemented in future)
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $events = $query->paginate(12)->withQueryString();
        
        // Get unique venues for filter dropdown
        $venues = Event::published()
            ->upcoming()
            ->select('venue')
            ->distinct()
            ->orderBy('venue')
            ->pluck('venue');

        return view('events.index', compact('events', 'venues'));
    }

    /**
     * Display the specified event.
     */
    public function show(Event $event): View
    {
        // Only show published events to public
        if (!$event->isPublished()) {
            abort(404);
        }

        // Load relationships
        $event->load(['creator', 'confirmedRegistrations.user', 'checkIns']);

        // Check if current user is registered
        $userRegistration = null;
        if (auth()->check()) {
            $userRegistration = $event->registrations()
                ->where('user_id', auth()->id())
                ->first();
        }

        // Get registration statistics
        $registrationStats = $event->getRegistrationStats();
        $checkInStats = $event->getCheckInStats();

        // Related events (same venue or similar date)
        $relatedEvents = Event::published()
            ->upcoming()
            ->where('id', '!=', $event->id)
            ->where(function ($query) use ($event) {
                $query->where('venue', $event->venue)
                      ->orWhereBetween('start_date', [
                          $event->start_date->subDays(7),
                          $event->start_date->addDays(7)
                      ]);
            })
            ->limit(3)
            ->get();

        return view('events.show', compact(
            'event',
            'userRegistration',
            'registrationStats',
            'checkInStats',
            'relatedEvents'
        ));
    }
}
