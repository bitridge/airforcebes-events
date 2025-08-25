<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Models\CheckIn;
use App\Services\CheckInService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SelfCheckInController extends Controller
{
    protected $checkInService;

    public function __construct(CheckInService $checkInService)
    {
        $this->checkInService = $checkInService;
    }

    /**
     * Show the self-check-in page
     */
    public function index()
    {
        $user = auth()->user();
        
        // Get user's confirmed registrations
        $registrations = $user->registrations()
            ->where('status', 'confirmed')
            ->with(['event', 'checkIn'])
            ->get();

        return view('self-checkin.index', compact('registrations'));
    }

    /**
     * Process QR code check-in
     */
    public function processQrCode(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string',
        ]);

        try {
            $qrCode = $request->qr_code;
            
            // Find registration by QR code
            $registration = Registration::where('qr_code_data', $qrCode)
                ->orWhere('registration_code', $qrCode)
                ->with(['event', 'user', 'checkIn'])
                ->first();

            if (!$registration) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid QR code or registration not found.'
                ], 404);
            }

            // Check if user owns this registration
            if ($registration->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only check in to your own registrations.'
                ], 403);
            }

            // Check if already checked in
            if ($registration->checkIn) {
                return response()->json([
                    'success' => false,
                    'message' => 'Already checked in to this event.',
                    'checkin_time' => $registration->checkIn->checked_in_at->format('M j, Y g:i A')
                ], 400);
            }

            // Check if registration is confirmed
            if ($registration->status !== 'confirmed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Registration must be confirmed before check-in.'
                ], 400);
            }

            // Process check-in
            DB::beginTransaction();

            $checkIn = $this->checkInService->checkInUser(
                $registration,
                'qr_code',
                'Self check-in via QR code'
            );

            DB::commit();

            Log::info('Self check-in successful', [
                'registration_id' => $registration->id,
                'user_id' => auth()->id(),
                'event_title' => $registration->event->title,
                'checkin_time' => $checkIn->checked_in_at
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Successfully checked in to ' . $registration->event->title,
                'checkin_time' => $checkIn->checked_in_at->format('M j, Y g:i A'),
                'event_title' => $registration->event->title
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Self check-in failed', [
                'user_id' => auth()->id(),
                'qr_code' => $request->qr_code,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Check-in failed. Please try again.'
            ], 500);
        }
    }

    /**
     * Manual check-in by registration code
     */
    public function manualCheckIn(Request $request)
    {
        $request->validate([
            'registration_code' => 'required|string',
        ]);

        try {
            $registrationCode = $request->registration_code;
            
            // Find registration by code
            $registration = Registration::where('registration_code', $registrationCode)
                ->with(['event', 'user', 'checkIn'])
                ->first();

            if (!$registration) {
                return response()->json([
                    'success' => false,
                    'message' => 'Registration code not found.'
                ], 404);
            }

            // Check if user owns this registration
            if ($registration->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only check in to your own registrations.'
                ], 403);
            }

            // Check if already checked in
            if ($registration->checkIn) {
                return response()->json([
                    'success' => false,
                    'message' => 'Already checked in to this event.',
                    'checkin_time' => $registration->checkIn->checked_in_at->format('M j, Y g:i A')
                ], 400);
            }

            // Check if registration is confirmed
            if ($registration->status !== 'confirmed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Registration must be confirmed before check-in.'
                ], 400);
            }

            // Process check-in
            DB::beginTransaction();

            $checkIn = $this->checkInService->checkInUser(
                $registration,
                'manual',
                'Self check-in via registration code'
            );

            DB::commit();

            Log::info('Manual self check-in successful', [
                'registration_id' => $registration->id,
                'user_id' => auth()->id(),
                'event_title' => $registration->event->title,
                'checkin_time' => $checkIn->checked_in_at
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Successfully checked in to ' . $registration->event->title,
                'checkin_time' => $checkIn->checked_in_at->format('M j, Y g:i A'),
                'event_title' => $registration->event->title
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Manual self check-in failed', [
                'user_id' => auth()->id(),
                'registration_code' => $request->registration_code,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Check-in failed. Please try again.'
            ], 500);
        }
    }
}
