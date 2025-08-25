@extends('layouts.app')

@section('title', $event->title . ' - ' . config('app.name'))
@section('description', Str::limit($event->description, 155))
@section('keywords', $event->title . ', ' . $event->venue . ', Air Force events, ' . $event->start_date->format('F Y'))
@if($event->featured_image)
    @section('ogImage', asset('storage/' . $event->featured_image))
@endif

@section('content')
    <!-- Event Header -->
    <section class="bg-slate-800 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Event Image -->
                <div class="lg:col-span-1">
                    @if($event->featured_image)
                        <img src="{{ asset('storage/' . $event->featured_image) }}" alt="{{ $event->title }}" class="w-full h-64 lg:h-80 object-cover rounded-lg">
                    @else
                        <div class="w-full h-64 lg:h-80 bg-gradient-to-br from-slate-600 to-slate-800 rounded-lg flex items-center justify-center">
                            <svg class="w-24 h-24 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    @endif
                </div>

                <!-- Event Details -->
                <div class="lg:col-span-2">
                    <div class="flex items-center space-x-3 mb-4">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            {{ $event->getStatusDisplayName() }}
                        </span>
                        @if($event->max_capacity)
                            <span class="text-slate-300 text-sm">
                                {{ $registrationStats['confirmed'] }}/{{ $event->max_capacity }} registered
                            </span>
                        @endif
                    </div>

                    <h1 class="text-4xl font-bold mb-4">{{ $event->title }}</h1>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div class="space-y-3">
                            <div class="flex items-center text-slate-300">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span>{{ $event->formatted_date_range }}</span>
                            </div>
                            <div class="flex items-center text-slate-300">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>{{ $event->formatted_time_range }}</span>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center text-slate-300">
                                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <span>{{ $event->venue }}</span>
                            </div>
                            @if($event->registration_deadline)
                                <div class="flex items-center text-slate-300">
                                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                    <span>Registration deadline: {{ $event->registration_deadline->format('M j, Y g:i A') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Capacity Progress Bar -->
                    @if($event->max_capacity)
                        <div class="mb-6">
                            <div class="flex justify-between text-sm text-slate-300 mb-2">
                                <span>Registration Progress</span>
                                <span>{{ $registrationStats['confirmed'] }}/{{ $event->max_capacity }}</span>
                            </div>
                            <div class="w-full bg-slate-700 rounded-full h-3">
                                <div class="bg-red-600 h-3 rounded-full transition-all duration-300" style="width: {{ min(100, $event->capacity_status['percentage']) }}%"></div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <!-- Event Content -->
    <section class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                <!-- Main Content -->
                <div class="lg:col-span-2">
                    <div class="prose prose-lg max-w-none">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">About This Event</h2>
                        <div class="text-gray-700 leading-relaxed">
                            {!! $event->description !!}
                        </div>
                    </div>

                    <!-- Registration Section -->
                    @if($event->status === 'published')
                        <div class="mt-12 bg-white border border-gray-200 rounded-lg p-6">
                            <h3 class="text-xl font-semibold text-gray-900 mb-4">Register for This Event</h3>
                            
                            @if(auth()->check())
                                @if($userRegistration)
                                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                                        <div class="flex items-center">
                                            <svg class="w-5 h-5 text-green-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <span class="text-green-800 font-medium">You are registered for this event!</span>
                                        </div>
                                        <div class="mt-2 text-sm text-green-700">
                                            Registration Code: <span class="font-mono font-medium">{{ $userRegistration->registration_code }}</span>
                                        </div>
                                        <div class="mt-2">
                                            <a href="{{ route('registrations.qr-code', $userRegistration) }}" class="inline-flex items-center text-green-600 hover:text-green-700 text-sm font-medium">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V6a1 1 0 00-1-1H5a1 1 0 00-1 1v1a1 1 0 001 1zm12 0h2a1 1 0 001-1V6a1 1 0 00-1-1h-2a1 1 0 00-1 1v1a1 1 0 001 1zM5 20h2a1 1 0 001-1v-1a1 1 0 00-1-1H5a1 1 0 00-1 1v1a1 1 0 001 1z"></path>
                                                </svg>
                                                View QR Code
                                            </a>
                                        </div>
                                    </div>
                                @else
                                    @if($event->isFull())
                                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                            <div class="flex items-center">
                                                <svg class="w-5 h-5 text-red-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                <span class="text-red-800 font-medium">This event is full</span>
                                            </div>
                                            <p class="mt-1 text-sm text-red-700">Sorry, this event has reached its maximum capacity.</p>
                                        </div>
                                    @elseif(!$event->isRegistrationOpen())
                                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                            <div class="flex items-center">
                                                <svg class="w-5 h-5 text-yellow-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                                </svg>
                                                <span class="text-yellow-800 font-medium">Registration is closed</span>
                                            </div>
                                            <p class="mt-1 text-sm text-yellow-700">The registration deadline for this event has passed.</p>
                                        </div>
                                    @else
                                        <form action="{{ route('registrations.store') }}" method="POST" class="space-y-4">
                                            @csrf
                                            <input type="hidden" name="event_id" value="{{ $event->id }}">
                                            
                                            <div>
                                                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Additional Notes (Optional)</label>
                                                <textarea id="notes" name="notes" rows="3" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Any special requirements or notes..."></textarea>
                                            </div>
                                            
                                            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors duration-200">
                                                Register for Event
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            @else
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-blue-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span class="text-blue-800 font-medium">Login Required</span>
                                    </div>
                                    <p class="mt-1 text-sm text-blue-700">Please log in to register for this event.</p>
                                    <div class="mt-3 flex space-x-3">
                                        <a href="{{ route('login') }}" class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200">
                                            Log In
                                        </a>
                                        <a href="{{ route('register') }}" class="inline-flex items-center border border-blue-600 text-blue-600 hover:bg-blue-50 px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200">
                                            Create Account
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Sidebar -->
                <div class="lg:col-span-1">
                    <!-- Event Stats -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Event Statistics</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Status</span>
                                <span class="font-medium">{{ $event->getStatusDisplayName() }}</span>
                            </div>
                            @if($event->max_capacity)
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Capacity</span>
                                    <span class="font-medium">{{ $event->max_capacity }} people</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Registered</span>
                                    <span class="font-medium">{{ $registrationStats['confirmed'] }} people</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Available</span>
                                    <span class="font-medium">{{ $event->max_capacity - $registrationStats['confirmed'] }} spots</span>
                                </div>
                            @endif
                            <div class="flex justify-between">
                                <span class="text-gray-600">Created</span>
                                <span class="font-medium">{{ $event->created_at->format('M j, Y') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Share Event -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Share This Event</h3>
                        <div class="flex space-x-3">
                            <a href="https://twitter.com/intent/tweet?text={{ urlencode('Check out this event: ' . $event->title) }}&url={{ urlencode(request()->url()) }}" target="_blank" class="flex-1 bg-blue-500 hover:bg-blue-600 text-white text-center px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200">
                                Twitter
                            </a>
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}" target="_blank" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-center px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200">
                                Facebook
                            </a>
                            <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(request()->url()) }}" target="_blank" class="flex-1 bg-blue-700 hover:bg-blue-800 text-white text-center px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200">
                                LinkedIn
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
