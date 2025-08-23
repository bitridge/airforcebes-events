<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Event;

class EventManagerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Please login to continue.');
        }

        $user = auth()->user();

        // Admins can manage all events
        if ($user->isAdmin()) {
            return $next($request);
        }

        // For non-admin users, check if they can manage the specific event
        $event = $this->getEventFromRequest($request);

        if (!$event) {
            abort(404, 'Event not found.');
        }

        // Check if user is the creator of the event
        if ($event->created_by === $user->id) {
            return $next($request);
        }

        // Check if user has specific permission to manage this event
        // This could be extended with a permissions table in the future
        if ($this->hasEventPermission($user, $event)) {
            return $next($request);
        }

        abort(403, 'You do not have permission to manage this event.');
    }

    /**
     * Get the event from the request parameters.
     */
    private function getEventFromRequest(Request $request): ?Event
    {
        // Try to get event from route parameter
        $event = $request->route('event');
        
        if ($event instanceof Event) {
            return $event;
        }

        // Try to get event ID from route parameter
        $eventId = $request->route('event') ?? $request->input('event_id');
        
        if ($eventId) {
            return Event::find($eventId);
        }

        // Try to get event from slug
        $eventSlug = $request->route('slug');
        if ($eventSlug) {
            return Event::where('slug', $eventSlug)->first();
        }

        return null;
    }

    /**
     * Check if user has specific permission to manage the event.
     * This can be extended with a permissions system in the future.
     */
    private function hasEventPermission($user, Event $event): bool
    {
        // For now, only the creator and admins can manage events
        // This can be extended with:
        // - Role-based permissions
        // - Event collaborators table
        // - Organization-based permissions
        
        return false;
    }
}
