<x-app-layout>
    <x-slot name="title">Registration Details - {{ $registration->registration_code }} - {{ config('app.name') }}</x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Header -->
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Registration Details</h1>
                    <p class="mt-2 text-lg text-gray-600">Code: {{ $registration->registration_code }}</p>
                </div>
                
                <div class="flex space-x-3">
                    <a href="{{ route('admin.registrations.edit', $registration) }}" 
                       class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit Registration
                    </a>
                    
                    <form method="POST" action="{{ route('admin.registrations.resend-email', $registration) }}" class="inline">
                        @csrf
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            Resend Email
                        </button>
                    </form>
                </div>
            </div>

            <!-- Registration Overview -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Registration Overview</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="font-medium text-gray-900 mb-4">Registration Information</h4>
                            <dl class="space-y-3 text-sm">
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Registration Code:</dt>
                                    <dd class="text-gray-900 font-medium">{{ $registration->registration_code }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Status:</dt>
                                    <dd class="text-gray-900">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                   {{ $registration->status === 'confirmed' ? 'bg-green-100 text-green-800' : 
                                                      ($registration->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                            {{ ucfirst($registration->status) }}
                                        </span>
                                    </dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Registration Date:</dt>
                                    <dd class="text-gray-900">{{ $registration->registration_date->format('M j, Y g:i A') }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Created:</dt>
                                    <dd class="text-gray-900">{{ $registration->created_at->format('M j, Y g:i A') }}</dd>
                                </div>
                                @if($registration->notes)
                                    <div class="col-span-2">
                                        <dt class="text-gray-500 mb-2">Notes:</dt>
                                        <dd class="text-gray-900 bg-gray-50 p-3 rounded">{{ $registration->notes }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                        
                        <div>
                            <h4 class="font-medium text-gray-900 mb-4">Event Information</h4>
                            <dl class="space-y-3 text-sm">
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Event:</dt>
                                    <dd class="text-gray-900 font-medium">{{ $registration->event->title }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Date:</dt>
                                    <dd class="text-gray-900">{{ $registration->event->formatted_date_range }}</dd>
                                </div>
                                @if($registration->event->start_time)
                                    <div class="flex justify-between">
                                        <dt class="text-gray-500">Time:</dt>
                                        <dd class="text-gray-900">{{ $registration->event->start_time }} - {{ $registration->event->end_time }}</dd>
                                    </div>
                                @endif
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Venue:</dt>
                                    <dd class="text-gray-900">{{ $registration->event->venue }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Event Status:</dt>
                                    <dd class="text-gray-900">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                   {{ $registration->event->status === 'published' ? 'bg-green-100 text-green-800' : 
                                                      ($registration->event->status === 'draft' ? 'bg-gray-100 text-gray-800' : 
                                                       ($registration->event->status === 'completed' ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800')) }}">
                                            {{ ucfirst($registration->event->status) }}
                                        </span>
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attendee Information -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Attendee Information</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="font-medium text-gray-900 mb-4">Personal Details</h4>
                            <dl class="space-y-3 text-sm">
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Name:</dt>
                                    <dd class="text-gray-900 font-medium">{{ $registration->user->name }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Email:</dt>
                                    <dd class="text-gray-900">{{ $registration->user->email }}</dd>
                                </div>
                                @if($registration->user->phone)
                                    <div class="flex justify-between">
                                        <dt class="text-gray-500">Phone:</dt>
                                        <dd class="text-gray-900">{{ $registration->user->phone }}</dd>
                                    </div>
                                @endif
                                @if($registration->user->organization)
                                    <div class="flex justify-between">
                                        <dt class="text-gray-500">Organization:</dt>
                                        <dd class="text-gray-900">{{ $registration->user->organization }}</dd>
                                    </div>
                                @endif
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Role:</dt>
                                    <dd class="text-gray-900">{{ ucfirst($registration->user->role) }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Account Status:</dt>
                                    <dd class="text-gray-900">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                   {{ $registration->user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $registration->user->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </dd>
                                </div>
                            </dl>
                        </div>
                        
                        <div>
                            <h4 class="font-medium text-gray-900 mb-4">Account Information</h4>
                            <dl class="space-y-3 text-sm">
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Member Since:</dt>
                                    <dd class="text-gray-900">{{ $registration->user->created_at->format('M j, Y') }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Last Updated:</dt>
                                    <dd class="text-gray-900">{{ $registration->user->updated_at->format('M j, Y g:i A') }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Total Registrations:</dt>
                                    <dd class="text-gray-900">{{ $registration->user->registrations()->count() }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Total Check-ins:</dt>
                                    <dd class="text-gray-900">{{ $registration->user->checkIns()->count() }}</dd>
                                </div>
                            </dl>
                            
                            <div class="mt-4">
                                <a href="{{ route('admin.attendees.show', $registration->user) }}" 
                                   class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    View Full Profile
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Check-in Information -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Check-in Information</h3>
                </div>
                <div class="p-6">
                    @if($registration->checkIn)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h4 class="font-medium text-gray-900 mb-4">Check-in Details</h4>
                                <dl class="space-y-3 text-sm">
                                    <div class="flex justify-between">
                                        <dt class="text-gray-500">Status:</dt>
                                        <dd class="text-gray-900">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                ✓ Checked In
                                            </span>
                                        </dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-gray-500">Check-in Time:</dt>
                                        <dd class="text-gray-900">{{ $registration->checkIn->checked_in_at->format('M j, Y g:i A') }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-gray-500">Method:</dt>
                                        <dd class="text-gray-900">{{ ucfirst($registration->checkIn->check_in_method) }}</dd>
                                    </div>
                                    @if($registration->checkIn->checkedInBy)
                                        <div class="flex justify-between">
                                            <dt class="text-gray-500">Checked in by:</dt>
                                            <dd class="text-gray-900">{{ $registration->checkIn->checkedInBy->name }}</dd>
                                        </div>
                                    @endif
                                </dl>
                            </div>
                            
                            <div>
                                <h4 class="font-medium text-gray-900 mb-4">QR Code Information</h4>
                                <dl class="space-y-3 text-sm">
                                    <div class="flex justify-between">
                                        <dt class="text-gray-500">QR Code:</dt>
                                        <dd class="text-gray-900">
                                            <a href="{{ route('registrations.qr-view', $registration) }}" 
                                               class="text-blue-600 hover:text-blue-900">View QR Code</a>
                                        </dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-gray-500">Download:</dt>
                                        <dd class="text-gray-900">
                                            <a href="{{ route('registrations.qr-code', $registration) }}" 
                                               class="text-green-600 hover:text-green-900">Download SVG</a>
                                        </dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-gray-500">Print:</dt>
                                        <dd class="text-gray-900">
                                            <a href="{{ route('registrations.qr-print', $registration) }}" 
                                               class="text-purple-600 hover:text-purple-900">Print Version</a>
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Not Checked In</h3>
                            <p class="mt-1 text-sm text-gray-500">This attendee has not checked in yet.</p>
                            <div class="mt-6">
                                <a href="{{ route('admin.check-in.manual') }}?code={{ $registration->registration_code }}" 
                                   class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700">
                                    Check In Now
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Actions</h3>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('admin.registrations.edit', $registration) }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit Registration
                    </a>
                    
                    <form method="POST" action="{{ route('admin.registrations.resend-email', $registration) }}" class="inline">
                        @csrf
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            Resend Confirmation Email
                        </button>
                    </form>
                    
                    @if(!$registration->checkIn)
                        <form method="POST" action="{{ route('admin.registrations.destroy', $registration) }}" class="inline" 
                              onsubmit="return confirm('Are you sure you want to delete this registration?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 border border-red-300 shadow-sm text-sm font-medium rounded-md text-red-700 bg-white hover:bg-red-50">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Delete Registration
                            </button>
                        </form>
                    @endif
                    
                    <a href="{{ route('admin.registrations.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
