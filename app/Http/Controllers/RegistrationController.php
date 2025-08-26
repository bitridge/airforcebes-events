<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRegistrationRequest;
use App\Models\Event;
use App\Models\Registration;
use App\Mail\RegistrationConfirmation;
use App\Services\QRCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    /**
     * Display the user's registrations.
     */
    public function index(): View
    {
        $user = auth()->user();
        
        $registrations = $user->registrations()
            ->with(['event', 'checkIn'])
            ->orderBy('registration_date', 'desc')
            ->paginate(10);

        return view('registrations.index', compact('registrations'));
    }

    /**
     * Store a new registration.
     */
    public function store(StoreRegistrationRequest $request, Event $event): RedirectResponse
    {
        // Log the registration attempt
        \Log::info('Registration attempt started', [
            'event_id' => $event->id,
            'event_slug' => $event->slug,
            'user_id' => auth()->id(),
            'user_email' => auth()->user()->email,
            'request_data' => $request->all(),
            'timestamp' => now(),
        ]);

        try {
            DB::beginTransaction();

            // Create the registration
            \Log::info('Creating registration record', [
                'event_id' => $event->id,
                'user_id' => auth()->id(),
                'registration_data' => [
                    'user_id' => auth()->id(),
                    'registration_code' => $this->generateUniqueRegistrationCode(),
                    'registration_date' => now(),
                    'status' => 'pending',
                    'notes' => $request->notes,
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'organization_name' => $request->organization_name,
                    'title' => $request->title,
                    'type' => $request->type,
                    'checkin_type' => $request->checkin_type,
                    'naics_codes' => $request->naics_codes,
                    'industry_connections' => $request->industry_connections,
                    'core_specialty_area' => $request->core_specialty_area,
                    'contract_vehicles' => $request->contract_vehicles,
                    'meeting_preference' => $request->meeting_preference,
                    'small_business_forum' => $request->small_business_forum,
                    'small_business_matchmaker' => $request->small_business_matchmaker,
                ]
            ]);

            $registration = $event->registrations()->create([
                'user_id' => auth()->id(),
                'registration_code' => $this->generateUniqueRegistrationCode(),
                'registration_date' => now(),
                'status' => 'pending',
                'notes' => $request->notes,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'organization_name' => $request->organization_name,
                'title' => $request->title,
                'type' => $request->type,
                'checkin_type' => $request->checkin_type,
                'naics_codes' => $request->naics_codes,
                'industry_connections' => $request->industry_connections,
                'core_specialty_area' => $request->core_specialty_area,
                'contract_vehicles' => $request->contract_vehicles,
                'meeting_preference' => $request->meeting_preference,
                'small_business_forum' => $request->small_business_forum,
                'small_business_matchmaker' => $request->small_business_matchmaker,
            ]);

            \Log::info('Registration created successfully', [
                'registration_id' => $registration->id,
                'registration_code' => $registration->registration_code,
            ]);

            // Generate QR code using service
            \Log::info('Generating QR code', [
                'registration_id' => $registration->id,
            ]);

            $qrCodeService = new QRCodeService();
            $qrResult = $qrCodeService->generateQRCode($registration);

            \Log::info('QR code generated successfully', [
                'registration_id' => $registration->id,
                'qr_result' => $qrResult,
            ]);

            DB::commit();

            \Log::info('Database transaction committed successfully', [
                'registration_id' => $registration->id,
            ]);

            // Send confirmation email
            try {
                Mail::to($registration->user->email)
                    ->send(new RegistrationConfirmation($registration));
            } catch (\Exception $e) {
                // Log email error but don't fail the registration
                \Log::error('Failed to send registration confirmation email', [
                    'registration_id' => $registration->id,
                    'error' => $e->getMessage()
                ]);
            }

            \Log::info('Registration process completed successfully', [
                'registration_id' => $registration->id,
                'redirecting_to' => route('events.show', $event->slug),
            ]);

            return redirect()
                ->route('events.show', $event->slug)
                ->with('success', 'Registration submitted successfully! Your registration is pending admin approval. You will receive an email with your registration card once approved.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Registration failed', [
                'event_id' => $event->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return redirect()
                ->route('events.show', $event->slug)
                ->with('error', 'Registration failed. Please try again.');
        }
    }

    /**
     * Cancel a registration.
     */
    public function destroy(Registration $registration): RedirectResponse
    {
        // Check if user owns this registration
        if ($registration->user_id !== auth()->id()) {
            abort(403, 'You can only cancel your own registrations.');
        }

        // Check if registration can be cancelled
        if (!$registration->canBeCancelled()) {
            return redirect()
                ->route('registrations.index')
                ->with('error', 'This registration cannot be cancelled.');
        }

        // Check if user is already checked in
        if ($registration->isCheckedIn()) {
            return redirect()
                ->route('registrations.index')
                ->with('error', 'Cannot cancel registration after check-in.');
        }

        try {
            DB::beginTransaction();

            // Delete QR code file if it exists
            $this->deleteQrCodeFile($registration);

            // Cancel the registration
            $registration->update(['status' => 'cancelled']);

            DB::commit();

            return redirect()
                ->route('registrations.index')
                ->with('success', 'Registration cancelled successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Registration cancellation failed', [
                'registration_id' => $registration->id,
                'error' => $e->getMessage()
            ]);

            return redirect()
                ->route('registrations.index')
                ->with('error', 'Failed to cancel registration. Please try again.');
        }
    }

    /**
     * Download QR code for a registration.
     */
    public function downloadQrCode(Registration $registration): Response
    {
        // Check if user owns this registration
        if ($registration->user_id !== auth()->id()) {
            abort(403, 'You can only download your own QR codes.');
        }

        // Check if registration is confirmed
        if (!$registration->isConfirmed()) {
            abort(404, 'QR code not available for this registration.');
        }

        $qrCodeService = new QRCodeService();
        $filename = "qr_codes/registration_{$registration->id}.svg";

        if (!Storage::disk('public')->exists($filename)) {
            // Regenerate QR code if it doesn't exist
            $qrCodeService->generateQRCode($registration);
        }

        $qrCodeContent = Storage::disk('public')->get($filename);

        return response($qrCodeContent)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Content-Disposition', 'attachment; filename="registration_' . $registration->registration_code . '.svg"');
    }

    /**
     * Admin: Display all registrations.
     */
    public function adminIndex(): View
    {
        $registrations = Registration::with(['event', 'user', 'checkIn'])
            ->orderBy('registration_date', 'desc')
            ->paginate(20);

        return view('admin.registrations.index', compact('registrations'));
    }

    /**
     * Admin: Display registrations for a specific event.
     */
    public function eventRegistrations(Event $event): View
    {
        $registrations = $event->registrations()
            ->with(['user', 'checkIn'])
            ->orderBy('registration_date', 'desc')
            ->paginate(20);

        return view('admin.events.registrations', compact('event', 'registrations'));
    }

    /**
     * Display QR code for a registration.
     */
    public function showQrCode(Registration $registration)
    {
        // Check if user owns this registration
        if ($registration->user_id !== auth()->id()) {
            abort(403, 'You can only view your own QR codes.');
        }

        // Check if registration is confirmed
        if (!$registration->isConfirmed()) {
            abort(404, 'QR code not available for this registration.');
        }

        $qrCodeService = new QRCodeService();
        $qrCode = $qrCodeService->getQRCodeForDisplay($registration);

        return view('registrations.qr-code', [
            'registration' => $registration,
            'qrCode' => $qrCode,
            'downloadUrl' => $qrCodeService->getQRCodeDownloadUrl($registration),
        ]);
    }

    /**
     * Print QR code for a registration.
     */
    public function printQrCode(Registration $registration)
    {
        // Check if user owns this registration
        if ($registration->user_id !== auth()->id()) {
            abort(403, 'You can only print your own QR codes.');
        }

        // Check if registration is confirmed
        if (!$registration->isConfirmed()) {
            abort(404, 'QR code not available for this registration.');
        }

        $qrCodeService = new QRCodeService();
        $printData = $qrCodeService->getQRCodeForPrint($registration);

        return view('registrations.print-qr', $printData);
    }

    /**
     * Generate a unique registration code.
     */
    private function generateUniqueRegistrationCode(): string
    {
        do {
            $code = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8));
        } while (Registration::where('registration_code', $code)->exists());

        return $code;
    }
}
