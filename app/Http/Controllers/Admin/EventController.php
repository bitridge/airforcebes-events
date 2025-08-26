<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Models\Event;
use App\Models\Registration;
use App\Models\CheckIn;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EventController extends Controller
{
    public function index(Request $request): View
    {
        $query = Event::with(['creator', 'registrations'])
            ->withCount(['confirmedRegistrations', 'checkIns']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('venue', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by date
        if ($request->filled('date_filter')) {
            switch ($request->date_filter) {
                case 'upcoming':
                    $query->upcoming();
                    break;
                case 'past':
                    $query->past();
                    break;
                case 'today':
                    $query->whereDate('start_date', today());
                    break;
            }
        }

        // Sort
        $sortBy = $request->get('sort_by', 'start_date');
        $sortDir = $request->get('sort_dir', 'asc');
        $query->orderBy($sortBy, $sortDir);

        $events = $query->paginate(15)->withQueryString();

        return view('admin.events.index', compact('events'));
    }

    public function create(): View
    {
        return view('admin.events.create');
    }

    public function store(StoreEventRequest $request): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $data['slug'] = Str::slug($data['title']);
            $data['created_by'] = auth()->id();

            // Handle image upload
            if ($request->hasFile('featured_image')) {
                try {
                    $file = $request->file('featured_image');
                    Log::info('Processing image upload', [
                        'original_name' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                        'extension' => $file->getClientOriginalExtension(),
                    ]);

                    $path = $file->store('events', 'public');
                    $data['featured_image'] = $path;
                    
                    Log::info('Image uploaded successfully', [
                        'stored_path' => $path,
                        'full_url' => Storage::disk('public')->url($path),
                    ]);
                } catch (\Exception $e) {
                    Log::error('Image upload failed', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    
                    return back()
                        ->withInput()
                        ->with('error', 'Failed to upload image: ' . $e->getMessage());
                }
            }

            $event = Event::create($data);

            DB::commit();

            return redirect()
                ->route('admin.events.show', $event)
                ->with('success', 'Event created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to create event: ' . $e->getMessage());
        }
    }

    public function show(Event $event): View
    {
        $event->load(['creator', 'registrations.user', 'checkIns.checkedInBy']);
        
        $stats = [
            'total_registrations' => $event->registrations()->count(),
            'confirmed_registrations' => $event->confirmedRegistrations()->count(),
            'check_ins' => $event->checkIns()->count(),
            'capacity_utilization' => $event->max_capacity ? 
                round(($event->confirmedRegistrations()->count() / $event->max_capacity) * 100, 1) : 0,
        ];

        $registrations = $event->registrations()
            ->with(['user', 'checkIn'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.events.show', compact('event', 'stats', 'registrations'));
    }

    public function edit(Event $event): View
    {
        return view('admin.events.edit', compact('event'));
    }

    public function update(UpdateEventRequest $request, Event $event): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            
            // Handle image upload
            if ($request->hasFile('featured_image')) {
                try {
                    // Delete old image
                    if ($event->featured_image) {
                        Storage::disk('public')->delete($event->featured_image);
                    }
                    
                    $file = $request->file('featured_image');
                    Log::info('Processing image update', [
                        'original_name' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                        'extension' => $file->getClientOriginalExtension(),
                    ]);

                    $path = $file->store('events', 'public');
                    $data['featured_image'] = $path;
                    
                    Log::info('Image updated successfully', [
                        'stored_path' => $path,
                        'full_url' => Storage::disk('public')->url($path),
                    ]);
                } catch (\Exception $e) {
                    Log::error('Image update failed', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    
                    return back()
                        ->withInput()
                        ->with('error', 'Failed to update image: ' . $e->getMessage());
                }
            }

            $event->update($data);

            DB::commit();

            return redirect()
                ->route('admin.events.show', $event)
                ->with('success', 'Event updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Failed to update event: ' . $e->getMessage());
        }
    }

    public function destroy(Event $event): RedirectResponse
    {
        try {
            // Check if event has registrations
            if ($event->registrations()->exists()) {
                return back()->with('error', 'Cannot delete event with existing registrations.');
            }

            // Delete featured image
            if ($event->featured_image) {
                Storage::disk('public')->delete($event->featured_image);
            }

            $event->delete();

            return redirect()
                ->route('admin.events.index')
                ->with('success', 'Event deleted successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete event: ' . $e->getMessage());
        }
    }

    public function duplicate(Event $event): RedirectResponse
    {
        try {
            $newEvent = $event->replicate();
            $newEvent->title = $event->title . ' (Copy)';
            $newEvent->slug = Str::slug($event->title . ' copy ' . now()->timestamp);
            $newEvent->status = 'draft';
            $newEvent->start_date = now()->addWeek();
            $newEvent->end_date = now()->addWeek()->addDay();
            $newEvent->registration_deadline = now()->addWeek()->subDay();
            $newEvent->created_by = auth()->id();
            $newEvent->created_at = now();
            $newEvent->updated_at = now();
            
            // Don't copy image
            $newEvent->featured_image = null;
            
            $newEvent->save();

            return redirect()
                ->route('admin.events.edit', $newEvent)
                ->with('success', 'Event duplicated successfully! You can now modify the details.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to duplicate event: ' . $e->getMessage());
        }
    }

    public function bulkAction(Request $request): RedirectResponse
    {
        $request->validate([
            'action' => 'required|in:delete,publish,unpublish',
            'event_ids' => 'required|array|min:1',
            'event_ids.*' => 'exists:events,id'
        ]);

        $events = Event::whereIn('id', $request->event_ids);

        try {
            switch ($request->action) {
                case 'delete':
                    // Check for registrations
                    $eventsWithRegistrations = $events->whereHas('registrations')->count();
                    if ($eventsWithRegistrations > 0) {
                        return back()->with('error', "Cannot delete {$eventsWithRegistrations} events with existing registrations.");
                    }
                    $events->delete();
                    $message = 'Events deleted successfully!';
                    break;

                case 'publish':
                    $events->update(['status' => 'published']);
                    $message = 'Events published successfully!';
                    break;

                case 'unpublish':
                    $events->update(['status' => 'draft']);
                    $message = 'Events unpublished successfully!';
                    break;
            }

            return back()->with('success', $message);

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to perform bulk action: ' . $e->getMessage());
        }
    }

    public function exportAttendees(Event $event): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $registrations = $event->registrations()
            ->with('user')
            ->where('status', 'confirmed')
            ->get();

        $filename = "event_{$event->slug}_attendees_" . now()->format('Y-m-d') . ".csv";
        $filepath = storage_path("app/temp/{$filename}");

        $file = fopen($filepath, 'w');
        
        // Headers
        fputcsv($file, ['Name', 'Email', 'Phone', 'Organization', 'Registration Date', 'Check-in Status']);
        
        foreach ($registrations as $registration) {
            fputcsv($file, [
                $registration->user->full_name,
                $registration->user->email,
                $registration->user->phone,
                $registration->user->organization,
                $registration->registration_date->format('Y-m-d H:i'),
                $registration->isCheckedIn() ? 'Checked In' : 'Not Checked In'
            ]);
        }
        
        fclose($file);

        return response()->download($filepath)->deleteFileAfterSend();
    }

    public function exportCheckInReport(Event $event): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $checkIns = $event->checkIns()
            ->with(['registration.user', 'checkedInBy'])
            ->orderBy('checked_in_at')
            ->get();

        $filename = "event_{$event->slug}_checkins_" . now()->format('Y-m-d') . ".csv";
        $filepath = storage_path("app/temp/{$filename}");

        $file = fopen($filepath, 'w');
        
        // Headers
        fputcsv($file, ['Name', 'Email', 'Check-in Time', 'Method', 'Checked in by']);
        
        foreach ($checkIns as $checkIn) {
            fputcsv($file, [
                $checkIn->registration->user->full_name,
                $checkIn->registration->user->email,
                $checkIn->checked_in_at->format('Y-m-d H:i:s'),
                $checkIn->check_in_method,
                $checkIn->checkedInBy->full_name ?? 'System'
            ]);
        }
        
        fclose($file);

        return response()->download($filepath)->deleteFileAfterSend();
    }
}
