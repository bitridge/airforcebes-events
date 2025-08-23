<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRegistrationRequest;
use App\Models\Event;
use App\Models\Registration;
use App\Mail\RegistrationConfirmation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

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
        try {
            DB::beginTransaction();

            // Create the registration
            $registration = $event->registrations()->create([
                'user_id' => auth()->id(),
                'registration_code' => $this->generateUniqueRegistrationCode(),
                'registration_date' => now(),
                'status' => 'confirmed',
            ]);

            // Generate QR code data and store
            $qrData = $this->generateQrCodeData($registration);
            $registration->update(['qr_code_data' => $qrData]);

            // Generate and store QR code image
            $this->generateAndStoreQrCode($registration);

            DB::commit();

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

            return redirect()
                ->route('events.show', $event->slug)
                ->with('success', 'Registration successful! Check your email for confirmation details.');

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

        $filename = "qr_codes/registration_{$registration->id}.svg";

        if (!Storage::disk('public')->exists($filename)) {
            // Regenerate QR code if it doesn't exist
            $this->generateAndStoreQrCode($registration);
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
     * Generate a unique registration code.
     */
    private function generateUniqueRegistrationCode(): string
    {
        do {
            $code = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8));
        } while (Registration::where('registration_code', $code)->exists());

        return $code;
    }

    /**
     * Generate QR code data for a registration.
     */
    private function generateQrCodeData(Registration $registration): string
    {
        return json_encode([
            'type' => 'event_registration',
            'registration_id' => $registration->id,
            'registration_code' => $registration->registration_code,
            'event_id' => $registration->event_id,
            'user_id' => $registration->user_id,
            'event_title' => $registration->event->title,
            'user_name' => $registration->user->name,
            'registration_date' => $registration->registration_date->toISOString(),
            'check_in_url' => route('checkin.index', ['code' => $registration->registration_code]),
        ]);
    }

    /**
     * Generate and store QR code image.
     */
    private function generateAndStoreQrCode(Registration $registration): void
    {
        $qrCodeSvg = QrCode::size(300)
            ->format('svg')
            ->generate($registration->qr_code_data);

        $filename = "qr_codes/registration_{$registration->id}.svg";
        Storage::disk('public')->put($filename, $qrCodeSvg);
    }

    /**
     * Delete QR code file.
     */
    private function deleteQrCodeFile(Registration $registration): void
    {
        $filename = "qr_codes/registration_{$registration->id}.svg";
        
        if (Storage::disk('public')->exists($filename)) {
            Storage::disk('public')->delete($filename);
        }
    }
}
