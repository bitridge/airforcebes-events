<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Registration;
use App\Models\CheckIn;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Display the homepage.
     */
    public function index(): View
    {
        // Get featured upcoming events (max 6)
        $featuredEvents = Event::published()
            ->upcoming()
            ->with(['creator', 'confirmedRegistrations'])
            ->orderBy('start_date', 'asc')
            ->limit(6)
            ->get();

        // Get statistics for the homepage
        $statistics = [
            'total_events' => Event::count(),
            'upcoming_events' => Event::published()->upcoming()->count(),
            'total_registrations' => Registration::confirmed()->count(),
            'total_attendees' => CheckIn::select('registration_id')->distinct()->count(),
        ];

        return view('welcome', compact('featuredEvents', 'statistics'));
    }
}
