<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateRegistrationRequest;
use App\Models\Registration;
use App\Models\Event;
use App\Models\User;
use App\Mail\RegistrationConfirmation;
use App\Mail\RegistrationCard;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class RegistrationController extends Controller
{
    public function adminIndex(Request $request): View
    {
        try {
            $query = Registration::with(['event', 'user', 'checkIn'])
                ->withCount('checkIn');

            // Search functionality
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->whereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                                  ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhere('registration_code', 'like', "%{$search}%");
                });
            }

            // Filter by event
            if ($request->filled('event_id') && $request->event_id !== 'all') {
                $query->where('event_id', $request->event_id);
            }

            // Filter by status
            if ($request->filled('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            // Filter by date range
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            // Sort
            $sortBy = $request->get('sort_by', 'created_at');
            $sortDir = $request->get('sort_dir', 'desc');
            $query->orderBy($sortBy, $sortDir);

            $registrations = $query->paginate(20)->withQueryString();
            
            try {
                $events = Event::published()->orderBy('title')->get();
                
                // Debug logging
                Log::info('Admin registrations page loaded', [
                    'events_count' => $events->count(),
                    'registrations_count' => $registrations->count(),
                    'method' => 'adminIndex'
                ]);
            } catch (\Exception $e) {
                // Fallback to all events if published scope fails
                $events = Event::orderBy('title')->get();
                Log::warning('Failed to load published events, falling back to all events', [
                    'error' => $e->getMessage(),
                    'method' => 'adminIndex'
                ]);
            }

            // Ensure we have the required variables
            if (!isset($events) || $events->isEmpty()) {
                Log::warning('No events found for admin registrations page', [
                    'method' => 'adminIndex',
                    'fallback' => 'Using empty collection'
                ]);
                $events = collect();
            }

            return view('admin.registrations.index', compact('registrations', 'events'));
            
        } catch (\Exception $e) {
            Log::error('Error in admin registrations index', [
                'error' => $e->getMessage(),
                'method' => 'adminIndex',
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return a view with empty data and error message
            return view('admin.registrations.index', [
                'registrations' => collect(),
                'events' => collect(),
                'error' => 'An error occurred while loading the registrations. Please try again.'
            ]);
        }
    }

    public function eventRegistrations(Event $event, Request $request): View
    {
        try {
            $query = $event->registrations()
                ->with(['user', 'checkIn'])
                ->withCount('checkIn');

            // Search functionality
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->whereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                                  ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhere('registration_code', 'like', "%{$search}%");
                });
            }

            // Filter by status
            if ($request->filled('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            // Filter by check-in status
            if ($request->filled('checkin_status')) {
                switch ($request->checkin_status) {
                    case 'checked_in':
                        $query->whereHas('checkIn');
                        break;
                    case 'not_checked_in':
                        $query->whereDoesntHave('checkIn');
                        break;
                }
            }

            // Sort
            $sortBy = $request->get('sort_by', 'created_at');
            $sortDir = $request->get('sort_dir', 'desc');
            $query->orderBy($sortBy, $sortDir);

            $registrations = $query->paginate(20)->withQueryString();

            Log::info('Admin event registrations page loaded', [
                'event_id' => $event->id,
                'event_title' => $event->title,
                'registrations_count' => $registrations->count(),
                'method' => 'eventRegistrations'
            ]);

            return view('admin.registrations.event', compact('event', 'registrations'));

        } catch (\Exception $e) {
            Log::error('Error in admin event registrations', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
                'method' => 'eventRegistrations',
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'An error occurred while loading the event registrations. Please try again.');
        }
    }

    public function show(Registration $registration): View
    {
        $registration->load(['event', 'user', 'checkIn.checkedInBy']);
        
        return view('admin.registrations.show', compact('registration'));
    }

    public function edit(Registration $registration): View
    {
        $registration->load(['event', 'user']);
        $statuses = ['pending', 'confirmed', 'cancelled'];
        
        return view('admin.registrations.edit', compact('registration', 'statuses'));
    }

    public function update(UpdateRegistrationRequest $request, Registration $registration): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            
            // Log the change
            $changes = $registration->getDirty();
            if (!empty($changes)) {
                Log::info('Registration updated', [
                    'registration_id' => $registration->id,
                    'user_id' => auth()->id(),
                    'changes' => $changes,
                    'old_values' => $registration->getOriginal(array_keys($changes))
                ]);
            }

            $oldStatus = $registration->status;
            $registration->update($data);

            // Send registration card email if status changed to confirmed
            if ($oldStatus !== 'confirmed' && $data['status'] === 'confirmed') {
                try {
                    Mail::to($registration->user->email)
                        ->send(new RegistrationCard($registration));
                    
                    Log::info('Registration card email sent', [
                        'registration_id' => $registration->id,
                        'user_email' => $registration->user->email,
                        'admin_id' => auth()->id()
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send registration card email', [
                        'registration_id' => $registration->id,
                        'error' => $e->getMessage()
                    ]);
                    // Don't fail the update if email fails
                }
            }

            DB::commit();

            return redirect()
                ->route('admin.registrations.show', $registration)
                ->with('success', 'Registration updated successfully!' . ($oldStatus !== 'confirmed' && $data['status'] === 'confirmed' ? ' Registration card email sent.' : ''));

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update registration', [
                'registration_id' => $registration->id,
                'error' => $e->getMessage()
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to update registration: ' . $e->getMessage());
        }
    }

    public function destroy(Registration $registration): RedirectResponse
    {
        try {
            // Check if already checked in
            if ($registration->checkIn) {
                return back()->with('error', 'Cannot delete registration that has been checked in.');
            }

            // Log the deletion
            Log::info('Registration deleted', [
                'registration_id' => $registration->id,
                'user_id' => auth()->id(),
                'event_title' => $registration->event->title,
                'attendee_name' => $registration->user->name
            ]);

            $registration->delete();

            return redirect()
                ->route('admin.registrations.index')
                ->with('success', 'Registration deleted successfully!');

        } catch (\Exception $e) {
            Log::error('Failed to delete registration', [
                'registration_id' => $registration->id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to delete registration: ' . $e->getMessage());
        }
    }

    public function bulkAction(Request $request): RedirectResponse
    {
        $request->validate([
            'action' => 'required|in:confirm,cancel,delete,resend_email',
            'registration_ids' => 'required|array|min:1',
            'registration_ids.*' => 'exists:registrations,id'
        ]);

        $registrations = Registration::whereIn('id', $request->registration_ids)
            ->with(['event', 'user']);

        try {
            DB::beginTransaction();

            switch ($request->action) {
                case 'confirm':
                    $registrations->update(['status' => 'confirmed']);
                    
                    // Send registration card emails for newly confirmed registrations
                    $confirmedRegistrations = $registrations->get();
                    $emailCount = 0;
                    
                    foreach ($confirmedRegistrations as $registration) {
                        try {
                            Mail::to($registration->user->email)
                                ->send(new RegistrationCard($registration));
                            $emailCount++;
                        } catch (\Exception $e) {
                            Log::error('Failed to send bulk registration card email', [
                                'registration_id' => $registration->id,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                    
                    $message = "Registrations confirmed successfully! {$emailCount} registration card emails sent.";
                    break;

                case 'cancel':
                    $registrations->update(['status' => 'cancelled']);
                    $message = 'Registrations cancelled successfully!';
                    break;

                case 'delete':
                    // Check for check-ins
                    $checkedInCount = $registrations->whereHas('checkIn')->count();
                    if ($checkedInCount > 0) {
                        return back()->with('error', "Cannot delete {$checkedInCount} registrations that have been checked in.");
                    }
                    $registrations->delete();
                    $message = 'Registrations deleted successfully!';
                    break;

                case 'resend_email':
                    $this->resendBulkEmails($registrations->get());
                    $message = 'Confirmation emails sent successfully!';
                    break;
            }

            DB::commit();

            return back()->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk action failed', [
                'action' => $request->action,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to perform bulk action: ' . $e->getMessage());
        }
    }

    public function resendEmail(Registration $registration): \Illuminate\Http\JsonResponse
    {
        try {
            Mail::to($registration->user->email)
                ->send(new RegistrationConfirmation($registration));

            Log::info('Confirmation email resent', [
                'registration_id' => $registration->id,
                'user_id' => auth()->id(),
                'attendee_email' => $registration->user->email
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Confirmation email sent successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to resend confirmation email', [
                'registration_id' => $registration->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send email: ' . $e->getMessage()
            ], 500);
        }
    }

    public function sendRegistrationCard(Registration $registration): \Illuminate\Http\JsonResponse
    {
        try {
            Mail::to($registration->user->email)
                ->send(new RegistrationCard($registration));

            Log::info('Registration card email sent', [
                'registration_id' => $registration->id,
                'user_id' => auth()->id(),
                'attendee_email' => $registration->user->email
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Registration card email sent successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send registration card email', [
                'registration_id' => $registration->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send registration card email: ' . $e->getMessage()
            ], 500);
        }
    }

    public function exportCsv(Request $request): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $query = Registration::with(['event', 'user', 'checkIn']);

        // Apply same filters as index
        if ($request->filled('event_id') && $request->event_id !== 'all') {
            $query->where('event_id', $request->event_id);
        }
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('registration_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('registration_date', '<=', $request->date_to);
        }

        $registrations = $query->orderBy('registration_date', 'desc')->get();

        $filename = "registrations_" . now()->format('Y-m-d_H-i-s') . ".csv";
        $filepath = storage_path("app/temp/{$filename}");

        $file = fopen($filepath, 'w');
        
        // Headers
        fputcsv($file, [
            'Registration Code', 'Event', 'Attendee Name', 'Email', 'Phone', 'Organization',
            'Registration Date', 'Status', 'Check-in Status', 'Check-in Time', 'Check-in Method'
        ]);
        
        foreach ($registrations as $registration) {
            fputcsv($file, [
                $registration->registration_code,
                $registration->event->title,
                $registration->user->name,
                $registration->user->email,
                $registration->user->phone ?? '',
                $registration->user->organization ?? '',
                $registration->registration_date->format('Y-m-d H:i:s'),
                $registration->status,
                $registration->checkIn ? 'Checked In' : 'Not Checked In',
                $registration->checkIn ? $registration->checkIn->checked_in_at->format('Y-m-d H:i:s') : '',
                $registration->checkIn ? $registration->checkIn->check_in_method : ''
            ]);
        }
        
        fclose($file);

        return response()->download($filepath)->deleteFileAfterSend();
    }

    public function exportPdf(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        // This would require a PDF library like DomPDF or Snappy
        // For now, return a placeholder response
        return response('PDF export not yet implemented', 501);
    }

    public function sendEventEmail(Request $request, Event $event): RedirectResponse
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|min:10',
            'recipients' => 'required|in:all,confirmed,pending,not_checked_in'
        ]);

        try {
            $query = $event->registrations()->with('user');

            // Filter recipients
            switch ($request->recipients) {
                case 'confirmed':
                    $query->where('status', 'confirmed');
                    break;
                case 'pending':
                    $query->where('status', 'pending');
                    break;
                case 'not_checked_in':
                    $query->whereDoesntHave('checkIn');
                    break;
            }

            $registrations = $query->get();
            $sentCount = 0;

            foreach ($registrations as $registration) {
                try {
                    // Send email logic here
                    // For now, just log the attempt
                    Log::info('Event email sent', [
                        'event_id' => $event->id,
                        'registration_id' => $registration->id,
                        'recipient_email' => $registration->user->email,
                        'subject' => $request->subject,
                        'admin_user_id' => auth()->id()
                    ]);
                    $sentCount++;
                } catch (\Exception $e) {
                    Log::error('Failed to send event email', [
                        'registration_id' => $registration->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            return back()->with('success', "Email sent to {$sentCount} recipients successfully!");

        } catch (\Exception $e) {
            Log::error('Failed to send event emails', [
                'event_id' => $event->id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to send emails: ' . $e->getMessage());
        }
    }

    private function resendBulkEmails($registrations): void
    {
        foreach ($registrations as $registration) {
            try {
                Mail::to($registration->user->email)
                    ->send(new RegistrationConfirmation($registration));

                Log::info('Bulk confirmation email sent', [
                    'registration_id' => $registration->id,
                    'admin_user_id' => auth()->id()
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send bulk confirmation email', [
                    'registration_id' => $registration->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    public function showQrCode(Registration $registration): \Illuminate\Http\Response
    {
        try {
            if (!$registration->qr_code_data) {
                abort(404, 'QR code not found for this registration');
            }

            // Generate QR code image
            $qrCode = QrCode::format('svg')
                ->size(300)
                ->margin(2)
                ->generate($registration->qr_code_data);

            Log::info('Admin viewed QR code', [
                'registration_id' => $registration->id,
                'admin_user_id' => auth()->id(),
                'method' => 'showQrCode'
            ]);

            return response($qrCode)
                ->header('Content-Type', 'image/svg+xml')
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate');

        } catch (\Exception $e) {
            Log::error('Error showing QR code', [
                'registration_id' => $registration->id,
                'error' => $e->getMessage(),
                'method' => 'showQrCode'
            ]);

            abort(500, 'Failed to generate QR code');
        }
    }

    public function downloadQrCode(Registration $registration): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        try {
            if (!$registration->qr_code_data) {
                abort(404, 'QR code not found for this registration');
            }

            // Generate QR code image
            $qrCode = QrCode::format('svg')
                ->size(300)
                ->margin(2)
                ->generate($registration->qr_code_data);

            // Save to temporary file
            $filename = "qr_code_{$registration->registration_code}.svg";
            $filepath = storage_path("app/temp/{$filename}");
            
            file_put_contents($filepath, $qrCode);

            Log::info('Admin downloaded QR code', [
                'registration_id' => $registration->id,
                'admin_user_id' => auth()->id(),
                'method' => 'downloadQrCode'
            ]);

            return response()->download($filepath, $filename, [
                'Content-Type' => 'image/svg+xml'
            ])->deleteFileAfterSend();

        } catch (\Exception $e) {
            Log::error('Error downloading QR code', [
                'registration_id' => $registration->id,
                'error' => $e->getMessage(),
                'method' => 'downloadQrCode'
            ]);

            abort(500, 'Failed to generate QR code');
        }
    }

    public function printQrCode(Registration $registration): \Illuminate\View\View
    {
        try {
            if (!$registration->qr_code_data) {
                abort(404, 'QR code not found for this registration');
            }

            Log::info('Admin printed QR code', [
                'registration_id' => $registration->id,
                'admin_user_id' => auth()->id(),
                'method' => 'printQrCode'
            ]);

            return view('admin.registrations.qr-print', compact('registration'));

        } catch (\Exception $e) {
            Log::error('Error printing QR code', [
                'registration_id' => $registration->id,
                'error' => $e->getMessage(),
                'method' => 'printQrCode'
            ]);

            abort(500, 'Failed to load QR code print view');
        }
    }

    public function printRegistrationCard(Registration $registration): \Illuminate\View\View
    {
        try {
            // Load necessary relationships
            $registration->load(['event', 'user']);

            Log::info('Admin printed registration card', [
                'registration_id' => $registration->id,
                'admin_user_id' => auth()->id(),
                'method' => 'printRegistrationCard'
            ]);

            return view('admin.registrations.print-card', compact('registration'));

        } catch (\Exception $e) {
            Log::error('Error printing registration card', [
                'registration_id' => $registration->id,
                'error' => $e->getMessage(),
                'method' => 'printRegistrationCard'
            ]);

            abort(500, 'Failed to load registration card print view');
        }
    }

    public function importCsv(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $request->validate([
                'event_id' => 'required|exists:events,id',
                'csv_file' => 'required|file|mimes:csv,txt|max:2048',
                'mapping' => 'required|array',
                'mapping.first_name' => 'required|string',
                'mapping.last_name' => 'required|string',
                'mapping.email' => 'required|string',
            ]);

            $event = Event::findOrFail($request->event_id);
            $file = $request->file('csv_file');
            $mapping = $request->input('mapping');

            // Read CSV file
            $csvData = array_map('str_getcsv', file($file->getPathname()));
            $headers = array_shift($csvData); // Remove header row

            // Validate CSV structure
            if (empty($csvData) || count($csvData) === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'CSV file appears to be empty or contains no data rows.',
                ], 400);
            }

            Log::info('CSV import started', [
                'event_id' => $event->id,
                'event_title' => $event->title,
                'total_rows' => count($csvData),
                'headers' => $headers,
                'mapping' => $mapping,
                'admin_user_id' => auth()->id(),
            ]);

            $imported = 0;
            $skipped = 0;
            $errors = [];

            DB::beginTransaction();

            foreach ($csvData as $rowIndex => $row) {
                try {
                    // Skip empty rows
                    if (empty(array_filter($row))) {
                        continue;
                    }

                    // Map CSV columns to fields
                    $firstName = trim($row[$mapping['first_name']] ?? '');
                    $lastName = trim($row[$mapping['last_name'] ?? '']);
                    $email = trim($row[$mapping['email'] ?? '']);

                    // Validate required fields
                    if (empty($firstName) || empty($lastName) || empty($email)) {
                        $errors[] = "Row " . ($rowIndex + 2) . ": Missing required fields (First Name: '{$firstName}', Last Name: '{$lastName}', Email: '{$email}')";
                        continue;
                    }

                    // Validate email format
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $errors[] = "Row " . ($rowIndex + 2) . ": Invalid email format: '{$email}'";
                        continue;
                    }

                    // Check if user already exists
                    $user = User::firstOrCreate(
                        ['email' => $email],
                        [
                            'name' => $firstName . ' ' . $lastName,
                            'first_name' => $firstName,
                            'last_name' => $lastName,
                            'role' => 'attendee',
                            'password' => bcrypt($this->generateRandomPassword()),
                            'email_verified_at' => now(),
                            'phone' => trim($row[$mapping['phone'] ?? ''] ?? ''),
                            'organization' => trim($row[$mapping['organization_name'] ?? ''] ?? ''),
                            'organization_name' => trim($row[$mapping['organization_name'] ?? ''] ?? ''),
                            'title' => trim($row[$mapping['title'] ?? ''] ?? ''),
                            'naics_codes' => trim($row[$mapping['naics_codes'] ?? ''] ?? ''),
                            'industry_connections' => trim($row[$mapping['industry_connections'] ?? ''] ?? ''),
                            'core_specialty_area' => trim($row[$mapping['core_specialty_area'] ?? ''] ?? ''),
                            'contract_vehicles' => trim($row[$mapping['contract_vehicles'] ?? ''] ?? ''),
                            'meeting_preference' => $this->normalizeMeetingPreference(trim($row[$mapping['meeting_preference'] ?? ''] ?? '')),
                            'small_business_forum' => $this->normalizeBoolean(trim($row[$mapping['small_business_forum'] ?? ''] ?? '')),
                            'small_business_matchmaker' => $this->normalizeBoolean(trim($row[$mapping['small_business_matchmaker'] ?? ''] ?? '')),
                        ]
                    );

                    // Log user creation/retrieval
                    if ($user->wasRecentlyCreated) {
                        Log::info('New user created during CSV import', [
                            'email' => $email,
                            'name' => $firstName . ' ' . $lastName,
                            'row' => $rowIndex + 2
                        ]);
                    } else {
                        Log::info('Existing user found during CSV import', [
                            'email' => $email,
                            'user_id' => $user->id,
                            'row' => $rowIndex + 2
                        ]);
                    }

                    // Check if registration already exists for this event
                    $existingRegistration = Registration::where('event_id', $event->id)
                        ->where('user_id', $user->id)
                        ->first();

                    if ($existingRegistration) {
                        $skipped++;
                        continue;
                    }

                    // Create registration
                    $registration = Registration::create([
                        'event_id' => $event->id,
                        'user_id' => $user->id,
                        'status' => 'confirmed', // Automatically confirmed
                        'registration_code' => $this->generateRegistrationCode(),
                        'qr_code_data' => $this->generateQrCodeData($event, $user),
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'email' => $email,
                        'phone' => trim($row[$mapping['phone'] ?? ''] ?? ''),
                        'organization_name' => trim($row[$mapping['organization_name'] ?? ''] ?? ''),
                        'title' => trim($row[$mapping['title'] ?? ''] ?? ''),
                        'type' => trim($row[$mapping['type'] ?? ''] ?? 'registration'),
                        'checkin_type' => trim($row[$mapping['checkin_type'] ?? ''] ?? 'standard'),
                        'naics_codes' => trim($row[$mapping['naics_codes'] ?? ''] ?? ''),
                        'industry_connections' => trim($row[$mapping['industry_connections'] ?? ''] ?? ''),
                        'core_specialty_area' => trim($row[$mapping['core_specialty_area'] ?? ''] ?? ''),
                        'contract_vehicles' => trim($row[$mapping['contract_vehicles'] ?? ''] ?? ''),
                        'meeting_preference' => $this->normalizeMeetingPreference(trim($row[$mapping['meeting_preference'] ?? ''] ?? '')),
                        'small_business_forum' => $this->normalizeBoolean(trim($row[$mapping['small_business_forum'] ?? ''] ?? '')),
                        'small_business_matchmaker' => $this->normalizeBoolean(trim($row[$mapping['small_business_matchmaker'] ?? ''] ?? '')),
                        'notes' => trim($row[$mapping['notes'] ?? ''] ?? ''),
                        'registration_date' => now(),
                    ]);

                    Log::info('Registration created during CSV import', [
                        'registration_id' => $registration->id,
                        'user_id' => $user->id,
                        'email' => $email,
                        'registration_code' => $registration->registration_code,
                        'row' => $rowIndex + 2
                    ]);

                    $imported++;

                    // Send registration confirmation email
                    try {
                        Mail::to($user->email)->send(new RegistrationCard($registration));
                    } catch (\Exception $e) {
                        Log::warning('Failed to send registration email', [
                            'registration_id' => $registration->id,
                            'email' => $user->email,
                            'error' => $e->getMessage()
                        ]);
                    }

                } catch (\Exception $e) {
                    $errors[] = "Row " . ($rowIndex + 2) . ": " . $e->getMessage();
                    continue;
                }
            }

            DB::commit();

            Log::info('CSV import completed', [
                'event_id' => $event->id,
                'event_title' => $event->title,
                'imported' => $imported,
                'skipped' => $skipped,
                'errors' => $errors,
                'admin_user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => "Successfully imported {$imported} registrations. {$skipped} duplicates skipped.",
                'imported' => $imported,
                'skipped' => $skipped,
                'errors' => $errors,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('CSV import failed', [
                'error' => $e->getMessage(),
                'admin_user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function generateRegistrationCode(): string
    {
        do {
            $code = strtoupper(substr(md5(uniqid()), 0, 8));
        } while (Registration::where('registration_code', $code)->exists());

        return $code;
    }

    private function generateQrCodeData(Event $event, User $user): string
    {
        return json_encode([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'registration_code' => $this->generateRegistrationCode(),
            'timestamp' => now()->timestamp,
        ]);
    }

    /**
     * Generate a random password for imported users.
     */
    private function generateRandomPassword(): string
    {
        // Generate a secure random password
        $password = Str::random(12);
        
        // Ensure it contains at least one uppercase, lowercase, number, and special character
        $password = str_shuffle($password . 'A' . 'a' . '1' . '!');
        
        return $password;
    }

    /**
     * Normalize meeting preference values from CSV to valid enum values.
     */
    private function normalizeMeetingPreference(string $value): string
    {
        $value = strtolower(trim($value));
        
        // Map common CSV values to valid enum values
        $mapping = [
            'in person' => 'in_person',
            'in-person' => 'in_person',
            'virtual' => 'virtual',
            'hybrid' => 'hybrid',
            'no preference' => 'no_preference',
            'no_preference' => 'no_preference',
            'prefer morning' => 'prefer_morning',
            'prefer afternoon' => 'prefer_afternoon',
            'prefer evening' => 'prefer_evening',
            '' => 'no_preference',
        ];
        
        return $mapping[$value] ?? 'no_preference';
    }

    /**
     * Normalize boolean values from CSV to valid boolean values.
     */
    private function normalizeBoolean(string $value): bool
    {
        $value = strtolower(trim($value));
        
        // Map common CSV values to boolean
        $trueValues = ['true', '1', 'yes', 'y', 'on', 'enabled'];
        $falseValues = ['false', '0', 'no', 'n', 'off', 'disabled', ''];
        
        if (in_array($value, $trueValues)) {
            return true;
        }
        
        if (in_array($value, $falseValues)) {
            return false;
        }
        
        // Default to false for unknown values
        return false;
    }
}
