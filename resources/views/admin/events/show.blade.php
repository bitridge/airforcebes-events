<x-app-layout>
    <x-slot name="title">{{ $event->title }} - Event Details - {{ config('app.name') }}</x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Header -->
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $event->title }}</h1>
                    <p class="mt-2 text-lg text-gray-600">{{ $event->venue }}</p>
                    <div class="mt-2 flex items-center space-x-4">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                                   {{ $event->status === 'published' ? 'bg-green-100 text-green-800' : 
                                      ($event->status === 'draft' ? 'bg-gray-100 text-gray-800' : 
                                       ($event->status === 'completed' ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800')) }}">
                            {{ ucfirst($event->status) }}
                        </span>
                        @if($event->is_featured)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                Featured
                            </span>
                        @endif
                    </div>
                </div>
                
                <div class="flex space-x-3">
                    <a href="{{ route('admin.events.edit', $event) }}" 
                       class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit Event
                    </a>
                    
                    <form method="POST" action="{{ route('admin.events.duplicate', $event) }}" class="inline">
                        @csrf
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v8a2 2 0 01-2 2h-2m-6-4l8 8m0 0l8-8m-8 8V4"></path>
                            </svg>
                            Duplicate
                        </button>
                    </form>
                </div>
            </div>

            <!-- Event Overview -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Event Overview</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-gray-900">{{ $stats['total_registrations'] }}</div>
                            <div class="text-sm text-gray-500">Total Registrations</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-gray-900">{{ $stats['confirmed_registrations'] }}</div>
                            <div class="text-sm text-gray-500">Confirmed</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-gray-900">{{ $stats['check_ins'] }}</div>
                            <div class="text-sm text-gray-500">Check-ins</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-gray-900">{{ $stats['capacity_utilization'] }}%</div>
                            <div class="text-sm text-gray-500">Capacity Used</div>
                        </div>
                    </div>
                    
                    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="font-medium text-gray-900 mb-2">Event Details</h4>
                            <dl class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Date:</dt>
                                    <dd class="text-gray-900">{{ $event->formatted_date_range }}</dd>
                                </div>
                                @if($event->start_time)
                                    <div class="flex justify-between">
                                        <dt class="text-gray-500">Time:</dt>
                                        <dd class="text-gray-900">{{ $event->start_time }} - {{ $event->end_time }}</dd>
                                    </div>
                                @endif
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Venue:</dt>
                                    <dd class="text-gray-900">{{ $event->venue }}</dd>
                                </div>
                                @if($event->max_capacity)
                                    <div class="flex justify-between">
                                        <dt class="text-gray-500">Capacity:</dt>
                                        <dd class="text-gray-900">{{ $event->confirmedRegistrations()->count() }} / {{ $event->max_capacity }}</dd>
                                    </div>
                                @endif
                                @if($event->registration_deadline)
                                    <div class="flex justify-between">
                                        <dt class="text-gray-500">Registration Deadline:</dt>
                                        <dd class="text-gray-900">{{ $event->registration_deadline->format('M j, Y') }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                        
                        <div>
                            <h4 class="font-medium text-gray-900 mb-2">Description</h4>
                            <div class="text-sm text-gray-700 prose max-w-none">
                                {!! $event->description !!}
                            </div>
                            
                            @if($event->featured_image)
                                <div class="mt-4">
                                    <h5 class="font-medium text-gray-900 mb-2">Featured Image</h5>
                                    <img src="{{ Storage::url($event->featured_image) }}" 
                                         alt="{{ $event->title }}" class="w-full max-w-md rounded-lg">
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Export Options -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Export Options</h3>
                <div class="flex flex-wrap gap-3">
                    <form method="POST" action="{{ route('admin.events.export-attendees', $event) }}" class="inline">
                        @csrf
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Export Attendee List (CSV)
                        </button>
                    </form>
                    
                    <form method="POST" action="{{ route('admin.events.export-checkins', $event) }}" class="inline">
                        @csrf
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Export Check-in Report (CSV)
                        </button>
                    </form>
                </div>
            </div>

            <!-- Registrations List -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Registrations ({{ $registrations->total() }})</h3>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attendee</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registration Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check-in</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($registrations as $registration)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $registration->user->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $registration->user->email }}</div>
                                            @if($registration->user->phone)
                                                <div class="text-sm text-gray-500">{{ $registration->user->phone }}</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $registration->registration_date->format('M j, Y g:i A') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                   {{ $registration->status === 'confirmed' ? 'bg-green-100 text-green-800' : 
                                                      ($registration->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                            {{ ucfirst($registration->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($registration->checkIn)
                                            <div class="text-sm text-gray-900">
                                                <div class="font-medium text-green-600">Checked In</div>
                                                <div class="text-xs text-gray-500">
                                                    {{ $registration->checkIn->checked_in_at->format('M j, Y g:i A') }}
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    via {{ ucfirst($registration->checkIn->check_in_method) }}
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-sm text-gray-500">Not checked in</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            @if(!$registration->checkIn)
                                                <a href="{{ route('admin.check-in.manual') }}?code={{ $registration->registration_code }}" 
                                                   class="text-green-600 hover:text-green-900">Check In</a>
                                            @endif
                                            <a href="{{ route('registrations.qr-view', $registration) }}" 
                                               class="text-blue-600 hover:text-blue-900">View QR</a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                        No registrations found for this event.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                @if($registrations->hasPages())
                    <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                        {{ $registrations->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
