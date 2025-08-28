<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use App\Models\CheckIn;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ReportingController extends Controller
{
    public function index(): View
    {
        // Get overview statistics
        $overview = [
            'total_events' => Event::published()->count(),
            'total_registrations' => Registration::count(),
            'total_attendees' => User::where('role', 'attendee')->count(),
            'checkin_rate' => $this->getCheckInRate(),
            'avg_capacity' => $this->getAverageCapacityUtilization(),
        ];

        // Get chart data
        $chartData = [
            'labels' => $this->getRegistrationTrendLabels(),
            'registrations' => $this->getRegistrationTrendValues(),
            'event_names' => $this->getEventNames(),
            'event_registrations' => $this->getEventRegistrationCounts(),
            'event_checkins' => $this->getEventCheckInCounts(),
        ];

        return view('admin.reports.index', compact('overview', 'chartData'));
    }

    public function eventReports(Request $request): View
    {
        $eventId = $request->get('event_id');
        $dateRange = $request->get('date_range', '30');
        
        if ($eventId) {
            $event = Event::with(['registrations.user', 'checkIns.registration.user'])->findOrFail($eventId);
            $stats = $this->getEventStatistics($event);
            $demographics = $this->getEventDemographics($event);
            $checkInPatterns = $this->getCheckInPatterns($event);
            $capacityUtilization = $this->getCapacityUtilization($event);
            
            return view('admin.reports.event-detail', compact('event', 'stats', 'demographics', 'checkInPatterns', 'capacityUtilization'));
        }

        $events = Event::withCount(['registrations', 'checkIns'])
            ->orderBy('start_date', 'desc')
            ->paginate(15);

        return view('admin.reports.events', compact('events', 'dateRange'));
    }

    public function attendeeAnalytics(Request $request): View
    {
        $dateRange = $request->get('date_range', '90');
        $startDate = Carbon::now()->subDays($dateRange);
        
        $analytics = [
            'repeatAttendees' => $this->getRepeatAttendees($startDate),
            'attendancePatterns' => $this->getAttendancePatterns($startDate),
            'geographicDistribution' => $this->getGeographicDistribution($startDate),
            'engagementMetrics' => $this->getEngagementMetrics($startDate),
        ];

        return view('admin.reports.attendee-analytics', compact('analytics', 'dateRange'));
    }

    public function dashboardWidgets(): JsonResponse
    {
        $cacheKey = 'dashboard_widgets_' . date('Y-m-d');
        
        $data = Cache::remember($cacheKey, 300, function () {
            return [
                'realTimeStats' => $this->getRealTimeStats(),
                'registrationTrends' => $this->getRegistrationTrends(),
                'topPerformingEvents' => $this->getTopPerformingEvents(),
                'checkInRateComparison' => $this->getCheckInRateComparison(),
            ];
        });

        return response()->json($data);
    }

    public function exportEventReport(Request $request)
    {
        $eventId = $request->get('event_id');
        $format = $request->get('format', 'csv');
        
        if (!$eventId) {
            return back()->with('error', 'Event ID is required');
        }

        $event = Event::with(['registrations.user', 'checkIns.registration.user'])->findOrFail($eventId);
        
        switch ($format) {
            case 'csv':
                return $this->exportEventCsv($event);
            case 'excel':
                return $this->exportEventExcel($event);
            case 'pdf':
                return $this->exportEventPdf($event);
            default:
                return back()->with('error', 'Invalid export format');
        }
    }

    public function exportAttendeeAnalytics(Request $request)
    {
        $dateRange = $request->get('date_range', '90');
        $format = $request->get('format', 'csv');
        $startDate = Carbon::now()->subDays($dateRange);
        
        switch ($format) {
            case 'csv':
                return $this->exportAttendeeAnalyticsCsv($startDate);
            case 'excel':
                return $this->exportAttendeeAnalyticsExcel($startDate);
            case 'pdf':
                return $this->exportAttendeeAnalyticsPdf($startDate);
            default:
                return back()->with('error', 'Invalid export format');
        }
    }

    private function getEventStatistics(Event $event): array
    {
        $totalRegistrations = $event->registrations()->count();
        $confirmedRegistrations = $event->registrations()->where('status', 'confirmed')->count();
        $checkIns = $event->checkIns()->count();
        $capacityUtilization = $event->max_capacity ? round(($confirmedRegistrations / $event->max_capacity) * 100, 1) : 0;
        
        return [
            'total_registrations' => $totalRegistrations,
            'confirmed_registrations' => $confirmedRegistrations,
            'pending_registrations' => $event->registrations()->where('status', 'pending')->count(),
            'cancelled_registrations' => $event->registrations()->where('status', 'cancelled')->count(),
            'check_ins' => $checkIns,
            'check_in_rate' => $confirmedRegistrations > 0 ? round(($checkIns / $confirmedRegistrations) * 100, 1) : 0,
            'capacity_utilization' => $capacityUtilization,
            'no_shows' => $confirmedRegistrations - $checkIns,
        ];
    }

    private function getEventDemographics(Event $event): array
    {
        $registrations = $event->registrations()->with('user')->where('status', 'confirmed')->get();
        
        $demographics = [
            'organizations' => $registrations->groupBy('user.organization')->map->count()->sortDesc()->take(10),
            'roles' => $registrations->groupBy('user.role')->map->count(),
            'registration_timeline' => $registrations->groupBy(function ($reg) {
                return $reg->created_at->format('Y-m-d');
            })->map->count()->sortKeys(),
            'check_in_timeline' => $event->checkIns()->get()->groupBy(function ($checkIn) {
                return $checkIn->checked_in_at->format('Y-m-d H:i');
            })->map->count()->sortKeys(),
        ];

        return $demographics;
    }

    private function getCheckInPatterns(Event $event): array
    {
        $checkIns = $event->checkIns()->with('registration.user')->get();
        
        $patterns = [
            'by_method' => $checkIns->groupBy('check_in_method')->map->count(),
            'by_hour' => $checkIns->groupBy(function ($checkIn) {
                return $checkIn->checked_in_at->format('H:00');
            })->map->count()->sortKeys(),
            'by_day' => $checkIns->groupBy(function ($checkIn) {
                return $checkIn->checked_in_at->format('Y-m-d');
            })->map->count()->sortKeys(),
            'average_check_in_time' => $checkIns->avg(function ($checkIn) {
                return $checkIn->checked_in_at->diffInMinutes($checkIn->registration->created_at);
            }),
        ];

        return $patterns;
    }

    private function getCapacityUtilization(Event $event): array
    {
        if (!$event->max_capacity) {
            return ['message' => 'No capacity limit set for this event'];
        }

        $registrations = $event->registrations()->where('status', 'confirmed')->get();
        $checkIns = $event->checkIns()->count();
        
        $utilization = [
            'max_capacity' => $event->max_capacity,
            'confirmed_registrations' => $registrations->count(),
            'check_ins' => $checkIns,
            'utilization_percentage' => round(($registrations->count() / $event->max_capacity) * 100, 1),
            'check_in_percentage' => round(($checkIns / $event->max_capacity) * 100, 1),
            'available_spots' => $event->max_capacity - $registrations->count(),
            'overbooking' => max(0, $registrations->count() - $event->max_capacity),
        ];

        return $utilization;
    }

    private function getRepeatAttendees(Carbon $startDate): array
    {
        $repeatAttendees = User::whereHas('registrations.event', function ($query) use ($startDate) {
            $query->where('start_date', '>=', $startDate);
        })
        ->withCount(['registrations' => function ($query) use ($startDate) {
            $query->whereHas('event', function ($q) use ($startDate) {
                $q->where('start_date', '>=', $startDate);
            });
        }])
        ->having('registrations_count', '>', 1)
        ->orderBy('registrations_count', 'desc')
        ->limit(20)
        ->get();

        $repeatStats = [
            'total_repeat_attendees' => $repeatAttendees->count(),
            'top_repeat_attendees' => $repeatAttendees->take(10),
            'repeat_attendance_distribution' => $repeatAttendees->groupBy('registrations_count')->map->count(),
        ];

        return $repeatStats;
    }

    private function getAttendancePatterns(Carbon $startDate): array
    {
        $events = Event::published()->where('start_date', '>=', $startDate)
            ->withCount(['registrations', 'checkIns'])
            ->orderBy('start_date')
            ->get();

        $patterns = [
            'events_over_time' => $events->map(function ($event) {
                return [
                    'date' => $event->start_date->format('Y-m-d'),
                    'registrations' => $event->registrations_count,
                    'check_ins' => $event->check_ins_count,
                    'check_in_rate' => $event->registrations_count > 0 ? 
                        round(($event->check_ins_count / $event->registrations_count) * 100, 1) : 0,
                ];
            }),
            'average_check_in_rate' => $events->avg(function ($event) {
                return $event->registrations_count > 0 ? 
                    ($event->check_ins_count / $event->registrations_count) * 100 : 0;
            }),
            'total_events' => $events->count(),
            'total_registrations' => $events->sum('registrations_count'),
            'total_check_ins' => $events->sum('check_ins_count'),
        ];

        return $patterns;
    }

    private function getGeographicDistribution(Carbon $startDate): array
    {
        $users = User::whereHas('registrations.event', function ($query) use ($startDate) {
            $query->where('start_date', '>=', $startDate);
        })
        ->whereNotNull('organization')
        ->select('organization', DB::raw('count(*) as count'))
        ->groupBy('organization')
        ->orderBy('count', 'desc')
        ->limit(20)
        ->get();

        $distribution = [
            'organizations' => $users,
            'total_unique_organizations' => User::whereHas('registrations.event', function ($query) use ($startDate) {
                $query->where('start_date', '>=', $startDate);
            })->distinct('organization')->count(),
            'top_organizations' => $users->take(10),
        ];

        return $distribution;
    }

    private function getEngagementMetrics(Carbon $startDate): array
    {
        $metrics = [
            'total_active_users' => User::whereHas('registrations.event', function ($query) use ($startDate) {
                $query->where('start_date', '>=', $startDate);
            })->count(),
            'average_registrations_per_user' => User::whereHas('registrations.event', function ($query) use ($startDate) {
                $query->where('start_date', '>=', $startDate);
            })->withCount(['registrations' => function ($q) use ($startDate) {
                $q->whereHas('event', function ($eventQuery) use ($startDate) {
                    $eventQuery->where('start_date', '>=', $startDate);
                });
            }])->avg('registrations_count'),
            'check_in_completion_rate' => $this->calculateCheckInCompletionRate($startDate),
            'user_retention_rate' => $this->calculateUserRetentionRate($startDate),
        ];

        return $metrics;
    }

    private function getRealTimeStats(): array
    {
        $today = Carbon::today();
        
        return [
            'today_registrations' => Registration::whereDate('created_at', $today)->count(),
            'today_check_ins' => CheckIn::whereDate('checked_in_at', $today)->count(),
            'upcoming_events' => Event::published()->where('start_date', '>=', $today)->count(),
            'active_events' => Event::published()->where('start_date', '<=', $today)
                ->where('end_date', '>=', $today)
                ->count(),
        ];
    }

    private function getRegistrationTrends(): array
    {
        $last30Days = collect(range(29, 0))->map(function ($days) {
            $date = Carbon::now()->subDays($days);
            return [
                'date' => $date->format('Y-m-d'),
                'registrations' => Registration::whereDate('created_at', $date)->count(),
                'check_ins' => CheckIn::whereDate('checked_in_at', $date)->count(),
            ];
        });

        return $last30Days->toArray();
    }

    private function getTopPerformingEvents(): array
    {
        return Event::published()->withCount(['registrations', 'checkIns'])
            ->where('start_date', '>=', Carbon::now()->subDays(90))
            ->orderBy('registrations_count', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($event) {
                return [
                    'title' => $event->title,
                    'registrations' => $event->registrations_count,
                    'check_ins' => $event->check_ins_count,
                    'check_in_rate' => $event->registrations_count > 0 ? 
                        round(($event->check_ins_count / $event->registrations_count) * 100, 1) : 0,
                ];
            })
            ->toArray();
    }

    private function getCheckInRateComparison(): array
    {
        $events = Event::withCount(['registrations', 'checkIns'])
            ->where('start_date', '>=', Carbon::now()->subDays(30))
            ->get();

        $comparison = [
            'high_performance' => $events->filter(function ($event) {
                return $event->registrations_count > 0 && 
                       ($event->check_ins_count / $event->registrations_count) >= 0.8;
            })->count(),
            'medium_performance' => $events->filter(function ($event) {
                return $event->registrations_count > 0 && 
                       ($event->check_ins_count / $event->registrations_count) >= 0.6 &&
                       ($event->check_ins_count / $event->registrations_count) < 0.8;
            })->count(),
            'low_performance' => $events->filter(function ($event) {
                return $event->registrations_count > 0 && 
                       ($event->check_ins_count / $event->registrations_count) < 0.6;
            })->count(),
        ];

        return $comparison;
    }

    private function calculateCheckInCompletionRate(Carbon $startDate): float
    {
        $totalConfirmed = Registration::whereHas('event', function ($query) use ($startDate) {
            $query->where('start_date', '>=', $startDate);
        })->where('status', 'confirmed')->count();

        $totalCheckIns = CheckIn::whereHas('registration.event', function ($query) use ($startDate) {
            $query->where('start_date', '>=', $startDate);
        })->count();

        return $totalConfirmed > 0 ? round(($totalCheckIns / $totalConfirmed) * 100, 1) : 0;
    }

    private function calculateUserRetentionRate(Carbon $startDate): float
    {
        $totalUsers = User::whereHas('registrations.event', function ($query) use ($startDate) {
            $query->where('start_date', '>=', $startDate);
        })->count();

        $repeatUsers = User::whereHas('registrations.event', function ($query) use ($startDate) {
            $query->where('start_date', '>=', $startDate);
        })
        ->withCount(['registrations' => function ($q) use ($startDate) {
            $q->whereHas('event', function ($eventQuery) use ($startDate) {
                $eventQuery->where('start_date', '>=', $startDate);
            });
        }])
        ->having('registrations_count', '>', 1)
        ->count();

        return $totalUsers > 0 ? round(($repeatUsers / $totalUsers) * 100, 1) : 0;
    }

    private function exportEventCsv(Event $event)
    {
        $filename = "event_{$event->slug}_report_" . now()->format('Y-m-d_H-i-s') . ".csv";
        $filepath = storage_path("app/temp/{$filename}");

        $file = fopen($filepath, 'w');
        
        // Summary sheet
        fputcsv($file, ['Event Report Summary']);
        fputcsv($file, ['Event', $event->title]);
        fputcsv($file, ['Date', $event->formatted_date_range]);
        fputcsv($file, ['Venue', $event->venue]);
        fputcsv($file, ['']);
        
        $stats = $this->getEventStatistics($event);
        fputcsv($file, ['Total Registrations', $stats['total_registrations']]);
        fputcsv($file, ['Confirmed Registrations', $stats['confirmed_registrations']]);
        fputcsv($file, ['Check-ins', $stats['check_ins']]);
        fputcsv($file, ['Check-in Rate', $stats['check_in_rate'] . '%']);
        fputcsv($file, ['Capacity Utilization', $stats['capacity_utilization'] . '%']);
        fputcsv($file, ['']);
        
        // Registrations detail
        fputcsv($file, ['Registration Details']);
        fputcsv($file, ['Name', 'Email', 'Phone', 'Organization', 'Status', 'Registration Date', 'Check-in Status', 'Check-in Time']);
        
        foreach ($event->registrations as $registration) {
            fputcsv($file, [
                $registration->user->full_name,
                $registration->user->email,
                $registration->user->phone ?? '',
                $registration->user->organization ?? '',
                $registration->status,
                $registration->registration_date->format('Y-m-d H:i:s'),
                $registration->checkIn ? 'Checked In' : 'Not Checked In',
                $registration->checkIn ? $registration->checkIn->checked_in_at->format('Y-m-d H:i:s') : '',
            ]);
        }
        
        fclose($file);
        return response()->download($filepath)->deleteFileAfterSend();
    }

    private function exportEventExcel(Event $event)
    {
        // This would use Laravel Excel for multi-sheet Excel export
        // For now, return a placeholder
        return response('Excel export not yet implemented', 501);
    }

    private function exportEventPdf(Event $event)
    {
        // This would use a PDF library like DomPDF
        // For now, return a placeholder
        return response('PDF export not yet implemented', 501);
    }

    private function exportAttendeeAnalyticsCsv(Carbon $startDate)
    {
        $filename = "attendee_analytics_" . now()->format('Y-m-d_H-i-s') . ".csv";
        $filepath = storage_path("app/temp/{$filename}");

        $file = fopen($filepath, 'w');
        
        // Repeat attendees
        fputcsv($file, ['Repeat Attendees Analysis']);
        fputcsv($file, ['Name', 'Email', 'Organization', 'Total Registrations', 'Events Attended']);
        
        $repeatAttendees = $this->getRepeatAttendees($startDate)['top_repeat_attendees'];
        foreach ($repeatAttendees as $attendee) {
            fputcsv($file, [
                $attendee->full_name,
                $attendee->email,
                $attendee->organization ?? '',
                $attendee->registrations_count,
                $attendee->registrations_count,
            ]);
        }
        
        fputcsv($file, ['']);
        
        // Attendance patterns
        fputcsv($file, ['Attendance Patterns']);
        fputcsv($file, ['Date', 'Registrations', 'Check-ins', 'Check-in Rate (%)']);
        
        $patterns = $this->getAttendancePatterns($startDate)['events_over_time'];
        foreach ($patterns as $pattern) {
            fputcsv($file, [
                $pattern['date'],
                $pattern['registrations'],
                $pattern['check_ins'],
                $pattern['check_in_rate'],
            ]);
        }
        
        fclose($file);
        return response()->download($filepath)->deleteFileAfterSend();
    }

    private function exportAttendeeAnalyticsExcel(Carbon $startDate)
    {
        // This would use Laravel Excel for multi-sheet Excel export
        // For now, return a placeholder
        return response('Excel export not yet implemented', 501);
    }

    private function exportAttendeeAnalyticsPdf(Carbon $startDate)
    {
        // This would use a PDF library like DomPDF
        // For now, return a placeholder
        return response('PDF export not yet implemented', 501);
    }

    // Helper methods for reports index
    private function getCheckInRate(): float
    {
        $totalRegistrations = Registration::count();
        $totalCheckIns = CheckIn::count();
        
        if ($totalRegistrations === 0) {
            return 0;
        }
        
        return round(($totalCheckIns / $totalRegistrations) * 100, 1);
    }

    private function getAverageCapacityUtilization(): float
    {
        $events = Event::published()->where('max_capacity', '>', 0)->get();
        
        if ($events->isEmpty()) {
            return 0;
        }
        
        $totalUtilization = 0;
        foreach ($events as $event) {
            $registrations = $event->registrations()->where('status', 'confirmed')->count();
            $utilization = ($registrations / $event->max_capacity) * 100;
            $totalUtilization += $utilization;
        }
        
        return round($totalUtilization / $events->count(), 1);
    }

    private function getRegistrationTrendLabels(): array
    {
        $labels = [];
        for ($i = 29; $i >= 0; $i--) {
            $labels[] = Carbon::now()->subDays($i)->format('M j');
        }
        return $labels;
    }

    private function getRegistrationTrendValues(): array
    {
        $values = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $count = Registration::whereDate('created_at', $date)->count();
            $values[] = $count;
        }
        return $values;
    }

    private function getEventNames(): array
    {
        return Event::published()->orderBy('start_date', 'desc')
            ->limit(5)
            ->pluck('title')
            ->toArray();
    }

    private function getEventRegistrationCounts(): array
    {
        return Event::published()->orderBy('start_date', 'desc')
            ->limit(5)
            ->withCount('registrations')
            ->pluck('registrations_count')
            ->toArray();
    }

    private function getEventCheckInCounts(): array
    {
        return Event::published()->orderBy('start_date', 'desc')
            ->limit(5)
            ->withCount('checkIns')
            ->pluck('check_ins_count')
            ->toArray();
    }
}
