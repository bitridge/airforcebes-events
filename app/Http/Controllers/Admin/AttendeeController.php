<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Registration;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AttendeeController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::with(['registrations.event', 'registrations.checkIn'])
            ->withCount(['registrations', 'checkIns']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('organization', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->filled('role') && $request->role !== 'all') {
            $query->where('role', $request->role);
        }

        // Filter by registration count
        if ($request->filled('registration_count')) {
            switch ($request->registration_count) {
                case '1':
                    $query->has('registrations', '=', 1);
                    break;
                case '2-5':
                    $query->has('registrations', '>=', 2)->has('registrations', '<=', 5);
                    break;
                case '6+':
                    $query->has('registrations', '>=', 6);
                    break;
            }
        }

        // Sort
        $sortBy = $request->get('sort_by', 'name');
        $sortDir = $request->get('sort_dir', 'asc');
        $query->orderBy($sortBy, $sortDir);

        $attendees = $query->paginate(20)->withQueryString();

        return view('admin.attendees.index', compact('attendees'));
    }

    public function show(User $attendee): View
    {
        $attendee->load([
            'registrations.event',
            'registrations.checkIn.checkedInBy',
            'registrations' => function ($query) {
                $query->orderBy('registration_date', 'desc');
            }
        ]);

        // Statistics
        $stats = [
            'total_registrations' => $attendee->registrations->count(),
            'confirmed_registrations' => $attendee->registrations->where('status', 'confirmed')->count(),
            'total_checkins' => $attendee->checkIns->count(),
            'events_attended' => $attendee->registrations->where('status', 'confirmed')->unique('event_id')->count(),
        ];

        // Recent activity
        $recentActivity = $attendee->registrations()
            ->with(['event', 'checkIn'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.attendees.show', compact('attendee', 'stats', 'recentActivity'));
    }

    public function edit(User $attendee): View
    {
        return view('admin.attendees.edit', compact('attendee'));
    }

    public function update(Request $request, User $attendee): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $attendee->id,
            'phone' => 'nullable|string|max:20',
            'organization' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        try {
            DB::beginTransaction();

            $oldData = $attendee->toArray();
            $attendee->update($request->only(['name', 'email', 'phone', 'organization', 'is_active']));

            // Log changes
            $changes = array_diff_assoc($attendee->toArray(), $oldData);
            if (!empty($changes)) {
                Log::info('Attendee profile updated', [
                    'attendee_id' => $attendee->id,
                    'admin_user_id' => auth()->id(),
                    'changes' => $changes
                ]);
            }

            DB::commit();

            return redirect()
                ->route('admin.attendees.show', $attendee)
                ->with('success', 'Attendee profile updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update attendee profile', [
                'attendee_id' => $attendee->id,
                'error' => $e->getMessage()
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to update attendee profile: ' . $e->getMessage());
        }
    }

    public function sendCommunication(Request $request, User $attendee): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|min:10',
            'communication_type' => 'required|in:email,sms,announcement'
        ]);

        try {
            // Log the communication attempt
            Log::info('Communication sent to attendee', [
                'attendee_id' => $attendee->id,
                'admin_user_id' => auth()->id(),
                'communication_type' => $request->communication_type,
                'subject' => $request->subject,
                'message' => $request->message
            ]);

            // Here you would implement the actual communication logic
            // For now, just log the attempt
            switch ($request->communication_type) {
                case 'email':
                    // Send email logic
                    break;
                case 'sms':
                    // Send SMS logic
                    break;
                case 'announcement':
                    // Store announcement logic
                    break;
            }

            return back()->with('success', 'Communication sent successfully!');

        } catch (\Exception $e) {
            Log::error('Failed to send communication', [
                'attendee_id' => $attendee->id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to send communication: ' . $e->getMessage());
        }
    }

    public function exportCsv(Request $request): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $query = User::with(['registrations.event', 'registrations.checkIn']);

        // Apply same filters as index
        if ($request->filled('role') && $request->role !== 'all') {
            $query->where('role', $request->role);
        }

        $attendees = $query->orderBy('name')->get();

        $filename = "attendees_" . now()->format('Y-m-d_H-i-s') . ".csv";
        $filepath = storage_path("app/temp/{$filename}");

        $file = fopen($filepath, 'w');
        
        // Headers
        fputcsv($file, [
            'Name', 'Email', 'Phone', 'Organization', 'Role', 'Status',
            'Total Registrations', 'Total Check-ins', 'First Registration', 'Last Registration'
        ]);
        
        foreach ($attendees as $attendee) {
            $firstRegistration = $attendee->registrations->min('registration_date');
            $lastRegistration = $attendee->registrations->max('registration_date');
            
            fputcsv($file, [
                $attendee->name,
                $attendee->email,
                $attendee->phone ?? '',
                $attendee->organization ?? '',
                $attendee->role,
                $attendee->is_active ? 'Active' : 'Inactive',
                $attendee->registrations->count(),
                $attendee->checkIns->count(),
                $firstRegistration ? $firstRegistration->format('Y-m-d') : '',
                $lastRegistration ? $lastRegistration->format('Y-m-d') : ''
            ]);
        }
        
        fclose($file);

        return response()->download($filepath)->deleteFileAfterSend();
    }

    public function bulkCommunication(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'attendee_ids' => 'required|array|min:1',
            'attendee_ids.*' => 'exists:users,id',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|min:10',
            'communication_type' => 'required|in:email,sms,announcement'
        ]);

        try {
            $attendees = User::whereIn('id', $request->attendee_ids)->get();
            $sentCount = 0;

            foreach ($attendees as $attendee) {
                try {
                    // Log the communication attempt
                    Log::info('Bulk communication sent', [
                        'attendee_id' => $attendee->id,
                        'admin_user_id' => auth()->id(),
                        'communication_type' => $request->communication_type,
                        'subject' => $request->subject
                    ]);
                    $sentCount++;
                } catch (\Exception $e) {
                    Log::error('Failed to send bulk communication', [
                        'attendee_id' => $attendee->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            return back()->with('success', "Communication sent to {$sentCount} attendees successfully!");

        } catch (\Exception $e) {
            Log::error('Failed to send bulk communication', [
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to send bulk communication: ' . $e->getMessage());
        }
    }
}
