<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Registration;
use App\Models\CheckIn;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        // Key metrics
        $metrics = [
            'total_events' => Event::published()->count(),
            'total_registrations' => Registration::count(),
            'checkins_today' => CheckIn::whereDate('checked_in_at', today())->count(),
            'upcoming_events' => Event::published()->upcoming()->count(),
        ];

        // Recent activity (registrations and check-ins)
        $recentRegistrations = Registration::with(['user', 'event'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $recentCheckIns = CheckIn::with(['registration.user', 'registration.event', 'checkedInBy'])
            ->orderBy('checked_in_at', 'desc')
            ->limit(10)
            ->get();

        // Upcoming events widget
        $upcomingEvents = Event::published()->upcoming()
            ->withCount(['confirmedRegistrations', 'checkIns'])
            ->orderBy('start_date', 'asc')
            ->limit(6)
            ->get();

        // Charts datasets
        $registrationTrends = $this->getRegistrationTrends();
        $capacityUtilization = $this->getCapacityUtilization();
        $checkInRates = $this->getCheckInRates();
        $popularEvents = $this->getPopularEvents();

        // Consolidate chart data for the view
        $chartData = [
            'labels' => $registrationTrends['labels'],
            'registrations' => $registrationTrends['values'],
            'event_names' => $capacityUtilization['labels'],
            'checkin_rates' => $checkInRates['values'],
            'capacity_used' => $capacityUtilization['values'],
            'capacity_available' => $capacityUtilization['labels']->map(function($label, $index) use ($capacityUtilization) {
                return 100 - $capacityUtilization['values'][$index];
            })->toArray(),
            'popular_event_names' => $popularEvents['labels'],
            'popular_event_registrations' => $popularEvents['values'],
        ];

        return view('admin.dashboard', compact(
            'metrics',
            'recentRegistrations',
            'recentCheckIns',
            'upcomingEvents',
            'chartData'
        ));
    }

    private function getRegistrationTrends(): array
    {
        // Last 14 days
        $days = collect(range(13, 0))->map(function ($i) {
            return today()->subDays($i);
        });

        $data = $days->map(function ($day) {
            $count = Registration::whereDate('created_at', $day)->count();
            return [
                'label' => $day->format('M j'),
                'value' => $count,
            ];
        });

        return [
            'labels' => $data->pluck('label'),
            'values' => $data->pluck('value'),
        ];
    }

    private function getCapacityUtilization(): array
    {
        $events = Event::published()
            ->withCount('confirmedRegistrations')
            ->orderBy('start_date', 'asc')
            ->limit(8)
            ->get();

        $labels = $events->pluck('title');
        $values = $events->map(function ($event) {
            if (!$event->max_capacity || $event->max_capacity == 0) {
                return 0;
            }
            return round(($event->confirmed_registrations_count / $event->max_capacity) * 100, 1);
        });

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    private function getCheckInRates(): array
    {
        $events = Event::published()
            ->withCount(['confirmedRegistrations', 'checkIns'])
            ->orderBy('start_date', 'desc')
            ->limit(8)
            ->get();

        $labels = $events->pluck('title');
        $values = $events->map(function ($event) {
            $total = max(1, $event->confirmed_registrations_count);
            return round(($event->check_ins_count / $total) * 100, 1);
        });

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    private function getPopularEvents(): array
    {
        $events = Event::published()
            ->withCount(['confirmedRegistrations'])
            ->orderBy('confirmed_registrations_count', 'desc')
            ->limit(5)
            ->get();

        return [
            'labels' => $events->pluck('title'),
            'values' => $events->pluck('confirmed_registrations_count'),
        ];
    }
}
