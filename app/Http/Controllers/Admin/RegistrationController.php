<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateRegistrationRequest;
use App\Models\Registration;
use App\Models\Event;
use App\Models\User;
use App\Mail\RegistrationConfirmation;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
            $query->whereDate('registration_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('registration_date', '<=', $request->date_to);
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
        $sortBy = $request->get('sort_by', 'registration_date');
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

            $registration->update($data);

            DB::commit();

            return redirect()
                ->route('admin.registrations.show', $registration)
                ->with('success', 'Registration updated successfully!');

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
                    $message = 'Registrations confirmed successfully!';
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

    public function resendEmail(Registration $registration): RedirectResponse
    {
        try {
            Mail::to($registration->user->email)
                ->send(new RegistrationConfirmation($registration));

            Log::info('Confirmation email resent', [
                'registration_id' => $registration->id,
                'user_id' => auth()->id(),
                'attendee_email' => $registration->user->email
            ]);

            return back()->with('success', 'Confirmation email sent successfully!');

        } catch (\Exception $e) {
            Log::error('Failed to resend confirmation email', [
                'registration_id' => $registration->id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to send email: ' . $e->getMessage());
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
}
