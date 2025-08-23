<x-app-layout>
    <x-slot name="title">{{ $event->title }} - {{ config('app.name') }}</x-slot>
    <x-slot name="description">{{ Str::limit($event->description, 155) }}</x-slot>
    <x-slot name="keywords">{{ $event->title }}, {{ $event->venue }}, Air Force events, {{ $event->start_date->format('F Y') }}</x-slot>
    @if($event->featured_image)
        <x-slot name="ogImage">{{ asset('storage/' . $event->featured_image) }}</x-slot>
    @endif

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
                            {!! nl2br(e($event->description)) !!}
                        </div>
                    </div>

                    <!-- Event Statistics -->
                    <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="bg-white rounded-lg border border-gray-200 p-6 text-center">
                            <div class="text-3xl font-bold text-slate-800 mb-2">{{ $registrationStats['confirmed'] }}</div>
                            <div class="text-gray-600">Registered</div>
                        </div>
                        <div class="bg-white rounded-lg border border-gray-200 p-6 text-center">
                            <div class="text-3xl font-bold text-slate-800 mb-2">{{ $checkInStats['total'] }}</div>
                            <div class="text-gray-600">Checked In</div>
                        </div>
                        <div class="bg-white rounded-lg border border-gray-200 p-6 text-center">
                            <div class="text-3xl font-bold text-slate-800 mb-2">{{ $event->getAvailableSpots() ?? '∞' }}</div>
                            <div class="text-gray-600">Available Spots</div>
                        </div>
                    </div>

                    <!-- Share Buttons -->
                    <div class="mt-12">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Share This Event</h3>
                        <div class="flex space-x-4">
                            <a href="https://twitter.com/intent/tweet?url={{ urlencode(route('events.show', $event->slug)) }}&text={{ urlencode($event->title) }}" 
                               target="_blank" 
                               class="flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors duration-200">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/>
                                </svg>
                                Twitter
                            </a>
                            <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(route('events.show', $event->slug)) }}" 
                               target="_blank" 
                               class="flex items-center px-4 py-2 bg-blue-700 hover:bg-blue-800 text-white rounded-lg transition-colors duration-200">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                </svg>
                                LinkedIn
                            </a>
                            <button onclick="copyEventUrl()" 
                                    class="flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                </svg>
                                Copy Link
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="lg:col-span-1">
                    <!-- Registration Card -->
                    <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
                        @auth
                            @if($userRegistration)
                                <!-- User is already registered -->
                                <div class="text-center">
                                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <h3 class="text-lg font-semibold text-gray-900 mb-2">You're Registered!</h3>
                                    <p class="text-gray-600 mb-4">Registration Code: <span class="font-mono font-semibold">{{ $userRegistration->registration_code }}</span></p>
                                    <p class="text-sm text-gray-500 mb-4">Registered on {{ $userRegistration->formatted_registration_date }}</p>
                                    
                                    @if($userRegistration->isCheckedIn())
                                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
                                            <p class="text-blue-700 text-sm font-medium">✓ Checked In</p>
                                        </div>
                                    @endif

                                    <form method="POST" action="{{ route('registrations.destroy', $userRegistration) }}" onsubmit="return confirm('Are you sure you want to cancel your registration?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                                            Cancel Registration
                                        </button>
                                    </form>
                                </div>
                            @else
                                <!-- Registration Form -->
                                @if($event->canRegister())
                                    <div class="text-center">
                                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Register for This Event</h3>
                                        
                                        <form method="POST" action="{{ route('registrations.store', $event) }}">
                                            @csrf
                                            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors duration-200 mb-4">
                                                Register Now
                                            </button>
                                        </form>

                                        @if($event->registration_deadline)
                                            <p class="text-sm text-gray-500">
                                                Registration closes {{ $event->registration_deadline->format('M j, Y g:i A') }}
                                            </p>
                                        @endif
                                    </div>
                                @else
                                    <!-- Registration Closed -->
                                    <div class="text-center">
                                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                            </svg>
                                        </div>
                                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Registration Closed</h3>
                                        <p class="text-gray-600">
                                            @if($event->isFull())
                                                This event has reached its maximum capacity.
                                            @elseif($event->registration_deadline && $event->registration_deadline->isPast())
                                                The registration deadline has passed.
                                            @elseif($event->hasStarted())
                                                This event has already started.
                                            @else
                                                Registration is not available for this event.
                                            @endif
                                        </p>
                                    </div>
                                @endif
                            @endif
                        @else
                            <!-- Guest User -->
                            <div class="text-center">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Register for This Event</h3>
                                <p class="text-gray-600 mb-6">Sign in to your account to register for this event.</p>
                                
                                <div class="space-y-3">
                                    <a href="{{ route('login', ['redirect' => route('events.show', $event->slug)]) }}" 
                                       class="w-full bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors duration-200 block">
                                        Sign In to Register
                                    </a>
                                    <a href="{{ route('register') }}" 
                                       class="w-full border border-gray-300 hover:border-gray-400 text-gray-700 px-6 py-3 rounded-lg font-medium transition-colors duration-200 block">
                                        Create Account
                                    </a>
                                </div>
                            </div>
                        @endauth
                    </div>

                    <!-- Event Details Card -->
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Event Details</h3>
                        <div class="space-y-4">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Organizer</span>
                                <span class="font-medium text-gray-900">{{ $event->creator->name }}</span>
                            </div>
                            @if($event->max_capacity)
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Capacity</span>
                                    <span class="font-medium text-gray-900">{{ $event->max_capacity }} attendees</span>
                                </div>
                            @endif
                            <div class="flex justify-between">
                                <span class="text-gray-600">Duration</span>
                                <span class="font-medium text-gray-900">{{ $event->duration }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Category</span>
                                <span class="font-medium text-gray-900">Professional Development</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Related Events -->
    @if($relatedEvents->count() > 0)
        <section class="py-12 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-8">Related Events</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    @foreach($relatedEvents as $relatedEvent)
                        <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-200">
                            @if($relatedEvent->featured_image)
                                <img src="{{ asset('storage/' . $relatedEvent->featured_image) }}" alt="{{ $relatedEvent->title }}" class="w-full h-48 object-cover">
                            @else
                                <div class="w-full h-48 bg-gradient-to-br from-slate-600 to-slate-800 flex items-center justify-center">
                                    <svg class="w-12 h-12 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            @endif
                            
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $relatedEvent->title }}</h3>
                                <p class="text-gray-600 text-sm mb-3">{{ Str::limit($relatedEvent->description, 80) }}</p>
                                <div class="text-sm text-gray-500 mb-4">
                                    {{ $relatedEvent->formatted_date_range }} • {{ $relatedEvent->venue }}
                                </div>
                                <a href="{{ route('events.show', $relatedEvent->slug) }}" class="inline-flex items-center text-red-600 hover:text-red-700 font-medium">
                                    View Event
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @push('scripts')
    <script>
        function copyEventUrl() {
            navigator.clipboard.writeText(window.location.href).then(function() {
                // Show a temporary success message
                const button = event.target.closest('button');
                const originalText = button.innerHTML;
                button.innerHTML = '<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>Copied!';
                button.classList.add('bg-green-600', 'hover:bg-green-700');
                button.classList.remove('bg-gray-600', 'hover:bg-gray-700');
                
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.classList.remove('bg-green-600', 'hover:bg-green-700');
                    button.classList.add('bg-gray-600', 'hover:bg-gray-700');
                }, 2000);
            }).catch(function(err) {
                console.error('Could not copy text: ', err);
            });
        }
    </script>
    @endpush
</x-app-layout>
