<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Registration;
use App\Models\CheckIn;
use App\Services\QRCodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CheckInController extends Controller
{
    /**
     * Display the QR code scanner check-in interface.
     */
    public function index(): View
    {
        // Get today's events for context
        $todaysEvents = Event::published()
            ->whereDate('start_date', '<=', today())
            ->whereDate('end_date', '>=', today())
            ->with(['confirmedRegistrations', 'checkIns'])
            ->get();

        // Get recent check-ins for activity feed
        $recentCheckIns = CheckIn::with(['registration.user', 'registration.event', 'checkedInBy'])
            ->orderBy('checked_in_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.check-in.index', compact('todaysEvents', 'recentCheckIns'));
    }

    /**
     * Display the manual check-in interface.
     */
    public function manual(): View
    {
        // Get events happening today or recently for context
        $events = Event::published()
            ->where(function ($query) {
                $query->whereDate('start_date', '<=', today()->addDays(1))
                      ->whereDate('end_date', '>=', today()->subDays(1));
            })
            ->with(['confirmedRegistrations', 'checkIns'])
            ->orderBy('start_date', 'asc')
            ->get();

        return view('admin.check-in.manual', compact('events'));
    }

    /**
     * Process check-in via QR code scan.
     */
    public function scanQrCode(Request $request): JsonResponse
    {
        $request->validate([
            'qr_data' => 'required|string',
        ]);

        try {
            $qrCodeService = new QRCodeService();
            $validation = $qrCodeService->validateQRCode($request->qr_data);

            if (!$validation['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $validation['message'],
                    'validation_error' => true,
                ], 400);
            }

            $registration = $validation['registration'];
            
            return $this->processCheckIn($registration->registration_code, 'qr');

        } catch (\Exception $e) {
            \Log::error('QR code scan failed', [
                'qr_data' => $request->qr_data,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process QR code. Please try manual check-in.',
            ], 500);
        }
    }

    /**
     * Process check-in via registration code.
     */
    public function checkInByCode(Request $request): JsonResponse
    {
        $request->validate([
            'registration_code' => 'required|string',
        ]);

        return $this->processCheckIn($request->registration_code, 'manual');
    }

    /**
     * Search for registrations to check in manually.
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:2',
            'event_id' => 'nullable|exists:events,id',
        ]);

        $query = $request->query;
        $eventId = $request->event_id;

        // Build the search query
        $registrations = Registration::with(['user', 'event', 'checkIn'])
            ->where('status', 'confirmed')
            ->where(function ($q) use ($query) {
                $q->where('registration_code', 'like', "%{$query}%")
                  ->orWhereHas('user', function ($userQuery) use ($query) {
                      $userQuery->where('name', 'like', "%{$query}%")
                               ->orWhere('email', 'like', "%{$query}%");
                  })
                  ->orWhereHas('event', function ($eventQuery) use ($query) {
                      $eventQuery->where('title', 'like', "%{$query}%");
                  });
            });

        // Filter by event if specified
        if ($eventId) {
            $registrations->where('event_id', $eventId);
        }

        $results = $registrations->limit(20)->get();

        return response()->json([
            'success' => true,
            'registrations' => $results->map(function ($registration) {
                return [
                    'id' => $registration->id,
                    'registration_code' => $registration->registration_code,
                    'user_name' => $registration->user->full_name,
                    'user_email' => $registration->user->email,
                    'event_title' => $registration->event->title,
                    'event_date' => $registration->event->formatted_date_range,
                    'checked_in' => $registration->isCheckedIn(),
                    'checked_in_at' => $registration->checkIn?->formatted_checked_in_at,
                    'can_check_in' => $registration->canCheckIn(),
                ];
            }),
        ]);
    }

    /**
     * Bulk check-in multiple registrations.
     */
    public function bulkCheckIn(Request $request): JsonResponse
    {
        $request->validate([
            'registration_ids' => 'required|array|min:1',
            'registration_ids.*' => 'exists:registrations,id',
        ]);

        $results = [];
        $successCount = 0;
        $errorCount = 0;

        foreach ($request->registration_ids as $registrationId) {
            $registration = Registration::find($registrationId);
            
            try {
                $result = $this->processCheckIn($registration->registration_code, 'manual');
                $resultData = json_decode($result->getContent(), true);
                
                if ($resultData['success']) {
                    $successCount++;
                } else {
                    $errorCount++;
                }
                
                $results[] = [
                    'registration_id' => $registrationId,
                    'registration_code' => $registration->registration_code,
                    'user_name' => $registration->user->full_name,
                    'success' => $resultData['success'],
                    'message' => $resultData['message'],
                ];
                
            } catch (\Exception $e) {
                $errorCount++;
                $results[] = [
                    'registration_id' => $registrationId,
                    'registration_code' => $registration->registration_code,
                    'user_name' => $registration->user->full_name,
                    'success' => false,
                    'message' => 'Check-in failed: ' . $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'success' => true,
            'summary' => [
                'total' => count($request->registration_ids),
                'successful' => $successCount,
                'failed' => $errorCount,
            ],
            'results' => $results,
        ]);
    }

    /**
     * Export check-in report for an event.
     */
    public function exportReport(Event $event)
    {
        $registrations = $event->registrations()
            ->with(['user', 'checkIn.checkedInBy'])
            ->orderBy('registration_date', 'asc')
            ->get();

        $csvData = [];
        $csvData[] = [
            'Registration Code',
            'User Name', 
            'User Email',
            'Registration Date',
            'Check-in Status',
            'Check-in Time',
            'Check-in Method',
            'Checked In By',
        ];

        foreach ($registrations as $registration) {
            $csvData[] = [
                $registration->registration_code,
                $registration->user->full_name,
                $registration->user->email,
                $registration->registration_date->format('Y-m-d H:i:s'),
                $registration->isCheckedIn() ? 'Checked In' : 'Not Checked In',
                $registration->checkIn?->checked_in_at?->format('Y-m-d H:i:s') ?? '',
                $registration->checkIn?->check_in_method_display_name ?? '',
                $registration->checkIn?->checkedInBy?->full_name ?? '',
            ];
        }

        $filename = "check-in-report-{$event->slug}-" . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($csvData) {
            $handle = fopen('php://output', 'w');
            foreach ($csvData as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Process a check-in request.
     */
    private function processCheckIn(string $registrationCode, string $method = 'manual'): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Find the registration
            $registration = Registration::where('registration_code', $registrationCode)
                ->with(['user', 'event', 'checkIn'])
                ->first();

            if (!$registration) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Registration not found.',
                ], 404);
            }

            // Check if already checked in
            if ($registration->isCheckedIn()) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'This registration is already checked in.',
                    'registration' => [
                        'code' => $registration->registration_code,
                        'user_name' => $registration->user->full_name,
                        'event_title' => $registration->event->title,
                        'checked_in_at' => $registration->checkIn->formatted_checked_in_at,
                    ],
                ], 400);
            }

            // Record the check-in
            $checkIn = CheckIn::recordCheckIn($registration, $method, auth()->user());

            if (!$checkIn) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to record check-in. Please try again.',
                ], 500);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Check-in successful!',
                'registration' => [
                    'id' => $registration->id,
                    'code' => $registration->registration_code,
                    'user_name' => $registration->user->full_name,
                    'user_email' => $registration->user->email,
                    'event_title' => $registration->event->title,
                    'event_date' => $registration->event->formatted_date_range,
                    'checked_in_at' => $checkIn->formatted_checked_in_at,
                    'check_in_method' => $checkIn->check_in_method_display_name,
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Check-in failed', [
                'registration_code' => $registrationCode,
                'method' => $method,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Check-in failed. Please try again.',
            ], 500);
        }
    }
}
