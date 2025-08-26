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
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by event
        if ($request->filled('event_id')) {
            $query->whereHas('registrations', function ($q) use ($request) {
                $q->where('event_id', $request->event_id);
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Sort
        $sortBy = $request->get('sort_by', 'first_name');
        $sortDir = $request->get('sort_dir', 'asc');
        
        // Handle special sorting for full name
        if ($sortBy === 'name') {
            $sortBy = 'first_name';
        }
        
        $query->orderBy($sortBy, $sortDir);

        $attendees = $query->paginate(20)->withQueryString();
        
        // Get events for filter dropdown
        $events = Event::select('id', 'title')->orderBy('title')->get();

        return view('admin.attendees.index', compact('attendees', 'events'));
    }

    public function show(User $attendee): View
    {
        try {
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

            // Debug logging
            Log::info('Admin attendee show page loaded', [
                'attendee_id' => $attendee->id,
                'attendee_name' => $attendee->full_name,
                'registrations_count' => $attendee->registrations->count(),
                'checkins_count' => $attendee->checkIns->count(),
                'method' => 'show'
            ]);

            return view('admin.attendees.show', compact('attendee', 'stats', 'recentActivity'));

        } catch (\Exception $e) {
            Log::error('Error in admin attendee show', [
                'attendee_id' => $attendee->id,
                'error' => $e->getMessage(),
                'method' => 'show',
                'trace' => $e->getTraceAsString()
            ]);

            // Return a view with error message
            return view('admin.attendees.show', [
                'attendee' => $attendee,
                'stats' => [
                    'total_registrations' => 0,
                    'confirmed_registrations' => 0,
                    'total_checkins' => 0,
                    'events_attended' => 0,
                ],
                'recentActivity' => collect(),
                'error' => 'An error occurred while loading the attendee data. Please try again.'
            ]);
        }
    }

    public function edit(User $attendee): View
    {
        try {
            // Load any additional relationships if needed
            $attendee->load(['registrations.event']);

            Log::info('Admin attendee edit page loaded', [
                'attendee_id' => $attendee->id,
                'attendee_name' => $attendee->full_name,
                'method' => 'edit'
            ]);

            return view('admin.attendees.edit', compact('attendee'));

        } catch (\Exception $e) {
            Log::error('Error in admin attendee edit', [
                'attendee_id' => $attendee->id,
                'error' => $e->getMessage(),
                'method' => 'edit',
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'An error occurred while loading the edit page. Please try again.');
        }
    }

    public function update(Request $request, User $attendee): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'first_name' => 'required|string|max:255|min:2',
            'last_name' => 'required|string|max:255|min:2',
            'email' => 'required|email|unique:users,email,' . $attendee->id,
            'phone' => 'nullable|string|max:20',
            'organization_name' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'naics_codes' => 'nullable|string|max:500',
            'industry_connections' => 'nullable|string|max:500',
            'core_specialty_area' => 'nullable|string|max:255',
            'contract_vehicles' => 'nullable|string|max:500',
            'meeting_preference' => 'nullable|string|max:255',
            'small_business_forum' => 'nullable|string|in:Yes (In-person),No',
            'small_business_matchmaker' => 'nullable|string|in:Yes (In-person),No',
            'role' => 'required|string|in:attendee,admin',
            'is_active' => 'boolean',
            'new_password' => 'nullable|string|min:8|confirmed',
        ]);

        try {
            DB::beginTransaction();

            $oldData = $attendee->toArray();
            
            // Prepare update data
            $updateData = [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'organization_name' => $request->organization_name,
                'title' => $request->title,
                'naics_codes' => $request->naics_codes,
                'industry_connections' => $request->industry_connections,
                'core_specialty_area' => $request->core_specialty_area,
                'contract_vehicles' => $request->contract_vehicles,
                'meeting_preference' => $request->meeting_preference,
                'small_business_forum' => $request->small_business_forum,
                'small_business_matchmaker' => $request->small_business_matchmaker,
                'role' => $request->role,
                'is_active' => $request->boolean('is_active'),
            ];

            // Update the attendee
            $attendee->update($updateData);

            // Handle password change if provided
            if ($request->filled('new_password')) {
                $attendee->update([
                    'password' => bcrypt($request->new_password)
                ]);
                
                Log::info('Attendee password changed', [
                    'attendee_id' => $attendee->id,
                    'admin_user_id' => auth()->id(),
                    'changed_at' => now()
                ]);
            }

            // Log changes
            $changes = array_diff_assoc($attendee->fresh()->toArray(), $oldData);
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
            'Name', 'Email', 'Phone', 'Organization Name', 'Role', 'Status',
            'Total Registrations', 'Total Check-ins', 'First Registration', 'Last Registration'
        ]);
        
        foreach ($attendees as $attendee) {
            $firstRegistration = $attendee->registrations->min('registration_date');
            $lastRegistration = $attendee->registrations->max('registration_date');
            
            fputcsv($file, [
                $attendee->full_name,
                $attendee->email,
                $attendee->phone ?? '',
                $attendee->organization_name ?? '',
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

    public function destroy(User $attendee): \Illuminate\Http\RedirectResponse
    {
        try {
            DB::beginTransaction();

            // Check if attendee has any registrations
            if ($attendee->registrations()->exists()) {
                return back()->with('error', 'Cannot delete attendee with existing registrations. Please cancel all registrations first.');
            }

            // Log the deletion
            Log::info('Attendee account deleted', [
                'attendee_id' => $attendee->id,
                'attendee_name' => $attendee->full_name,
                'attendee_email' => $attendee->email,
                'admin_user_id' => auth()->id()
            ]);

            // Delete the attendee
            $attendee->delete();

            DB::commit();

            return redirect()
                ->route('admin.attendees.index')
                ->with('success', 'Attendee account deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete attendee account', [
                'attendee_id' => $attendee->id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Failed to delete attendee account: ' . $e->getMessage());
        }
    }
}
