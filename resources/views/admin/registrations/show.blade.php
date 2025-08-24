<x-app-layout>
    <x-slot name="title">Registration Details - {{ $registration->registration_code }} - {{ config('app.name') }}</x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Registration Details</h1>
                    <p class="text-gray-600">Code: {{ $registration->registration_code }}</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('admin.registrations.edit', $registration) }}" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit Registration
                    </a>
                    <a href="{{ route('admin.registrations.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        All Registrations
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Registration Information -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Registration Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Registration Code</label>
                        <p class="mt-1 text-sm font-mono text-gray-900">{{ $registration->registration_code }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Status</label>
                        <p class="mt-1 text-sm text-gray-900">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                       {{ $registration->status === 'confirmed' ? 'bg-green-100 text-green-800' : 
                                          ($registration->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                {{ ucfirst($registration->status) }}
                            </span>
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Registration Date</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $registration->registration_date->format('M j, Y g:i A') }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">QR Code</label>
                        <p class="mt-1 text-sm text-gray-900">
                            @if($registration->qr_code_data)
                                <a href="{{ route('admin.registrations.qr-view', $registration) }}" 
                                   class="text-blue-600 hover:text-blue-900">View QR Code</a>
                            @else
                                <span class="text-gray-500">No QR code generated</span>
                            @endif
                        </p>
                    </div>
                    @if($registration->notes)
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Notes</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $registration->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Event Information -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Event Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Event Title</label>
                        <p class="mt-1 text-sm font-medium text-gray-900">{{ $registration->event->title }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Event Date</label>
                        <p class="mt-1 text-sm text-gray-900">
                            {{ $registration->event->start_date->format('M j, Y') }}
                            @if($registration->event->start_time)
                                at {{ $registration->event->start_time->format('g:i A') }}
                            @endif
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Venue</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $registration->event->venue }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Event Status</label>
                        <p class="mt-1 text-sm text-gray-900">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                       {{ $registration->event->status === 'published' ? 'bg-green-100 text-green-800' : 
                                          ($registration->event->status === 'draft' ? 'bg-gray-100 text-gray-800' : 'bg-red-100 text-red-800') }}">
                                {{ ucfirst($registration->event->status) }}
                            </span>
                        </p>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="{{ route('admin.events.show', $registration->event) }}" 
                       class="text-blue-600 hover:text-blue-900 text-sm">
                        View Event Details →
                    </a>
                </div>
            </div>

            <!-- Attendee Information -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Attendee Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Full Name</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $registration->user->name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $registration->user->email }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Phone</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $registration->user->phone ?? 'Not provided' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Organization</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $registration->user->organization ?? 'Not provided' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Role</label>
                        <p class="mt-1 text-sm text-gray-900">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                       {{ $registration->user->role === 'admin' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                {{ ucfirst($registration->user->role) }}
                            </span>
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Account Status</label>
                        <p class="mt-1 text-sm text-gray-900">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                       {{ $registration->user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $registration->user->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </p>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="{{ route('admin.attendees.show', $registration->user) }}" 
                       class="text-blue-600 hover:text-blue-900 text-sm">
                        View Attendee Profile →
                    </a>
                </div>
            </div>

            <!-- Check-in Information -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Check-in Information</h2>
                @if($registration->checkIn)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Check-in Status</label>
                            <p class="mt-1 text-sm text-gray-900">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Checked In
                                </span>
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Check-in Date & Time</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $registration->checkIn->checked_in_at->format('M j, Y g:i A') }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Check-in Method</label>
                            <p class="mt-1 text-sm text-gray-900">{{ ucfirst($registration->checkIn->check_in_method) }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Checked in by</label>
                            <p class="mt-1 text-sm text-gray-900">
                                @if($registration->checkIn->checkedInBy)
                                    {{ $registration->checkIn->checkedInBy->name }}
                                @else
                                    System
                                @endif
                            </p>
                        </div>
                        @if($registration->checkIn->notes)
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Check-in Notes</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $registration->checkIn->notes }}</p>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Not Checked In</h3>
                        <p class="mt-1 text-sm text-gray-500">This attendee has not checked in yet.</p>
                    </div>
                @endif
            </div>

            <!-- Actions -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Actions</h2>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('admin.registrations.print-card', $registration) }}" 
                       target="_blank"
                       class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        Print Registration Card
                    </a>

                    <form method="POST" action="{{ route('admin.registrations.resend-email', $registration) }}" class="inline">
                        @csrf
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            Resend Confirmation Email
                        </button>
                    </form>

                    @if(!$registration->checkIn)
                        <form method="POST" action="{{ route('admin.registrations.destroy', $registration) }}" class="inline" 
                              onsubmit="return confirm('Are you sure you want to delete this registration? This action cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Delete Registration
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
