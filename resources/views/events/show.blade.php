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
                                    @if($userRegistration->status === 'confirmed')
                                        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                                            <div class="flex items-center">
                                                <svg class="w-5 h-5 text-green-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                <span class="text-green-800 font-medium">Your registration is confirmed!</span>
                                            </div>
                                            <div class="mt-2 text-sm text-green-700">
                                                Registration Code: <span class="font-mono font-medium">{{ $userRegistration->registration_code }}</span>
                                            </div>
                                            <div class="mt-3">
                                                <a href="{{ route('registrations.qr-print', $userRegistration) }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                                    </svg>
                                                    Print Registration Card
                                                </a>
                                            </div>
                                        </div>
                                    @else
                                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
                                            <div class="flex items-center">
                                                <svg class="w-5 h-5 text-yellow-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                <span class="text-yellow-800 font-medium">Your registration is pending approval</span>
                                            </div>
                                            <div class="mt-2 text-sm text-yellow-700">
                                                Registration Code: <span class="font-mono font-medium">{{ $userRegistration->registration_code }}</span>
                                            </div>
                                            <div class="mt-2 text-sm text-yellow-600">
                                                Your registration has been submitted and is currently being reviewed by our team. You will receive an email with your registration card once it's approved.
                                            </div>
                                        </div>
                                    @endif
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
                                                                                <form action="{{ route('registrations.store', $event->slug) }}" method="POST" class="space-y-6" id="registrationForm">
                                            @csrf
                                            <input type="hidden" name="event_id" value="{{ $event->id }}">
                                            
                                            <!-- Personal Information Section -->
                                            <div class="bg-gray-50 p-4 rounded-lg">
                                                <h4 class="text-lg font-medium text-gray-900 mb-4">Personal Information</h4>
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    <div>
                                                        <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">First Name *</label>
                                                        <input type="text" id="first_name" name="first_name" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value="{{ old('first_name', auth()->user()->name ?? '') }}">
                                                    </div>
                                                    <div>
                                                        <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">Last Name *</label>
                                                        <input type="text" id="last_name" name="last_name" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value="{{ old('last_name') }}">
                                                    </div>
                                                    <div>
                                                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                                                        <input type="email" id="email" name="email" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value="{{ old('email', auth()->user()->email ?? '') }}">
                                                    </div>
                                                    <div>
                                                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                                                        <input type="tel" id="phone" name="phone" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value="{{ old('phone', auth()->user()->phone ?? '') }}">
                                                    </div>
                                                    <div class="md:col-span-2">
                                                        <label for="organization_name" class="block text-sm font-medium text-gray-700 mb-1">Organization Name *</label>
                                                        <input type="text" id="organization_name" name="organization_name" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value="{{ old('organization_name') }}">
                                                    </div>
                                                    <div class="md:col-span-2">
                                                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Job Title</label>
                                                        <input type="text" id="title" name="title" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value="{{ old('title') }}">
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Registration Type Section -->
                                            <div class="bg-gray-50 p-4 rounded-lg">
                                                <h4 class="text-lg font-medium text-gray-900 mb-4">Registration Type</h4>
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    <div>
                                                        <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Type *</label>
                                                        <select id="type" name="type" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                            <option value="">Select Type</option>
                                                            <option value="registration" {{ old('type') == 'registration' ? 'selected' : '' }}>Registration (CT)</option>
                                                            <option value="checkin" {{ old('type') == 'checkin' ? 'selected' : '' }}>Check-in (CT)</option>
                                                        </select>
                                                    </div>
                                                    <div id="checkin_type_container" class="hidden">
                                                        <label for="checkin_type" class="block text-sm font-medium text-gray-700 mb-1">Check-in Type</label>
                                                        <select id="checkin_type" name="checkin_type" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                            <option value="">Select Check-in Type</option>
                                                            <option value="in_person" {{ old('checkin_type') == 'in_person' ? 'selected' : '' }}>In Person</option>
                                                            <option value="virtual" {{ old('checkin_type') == 'virtual' ? 'selected' : '' }}>Virtual</option>
                                                            <option value="hybrid" {{ old('checkin_type') == 'hybrid' ? 'selected' : '' }}>Hybrid</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Business Information Section -->
                                            <div class="bg-gray-50 p-4 rounded-lg">
                                                <h4 class="text-lg font-medium text-gray-900 mb-4">Business Information</h4>
                                                <div class="space-y-4">
                                                    <div>
                                                        <label for="naics_codes" class="block text-sm font-medium text-gray-700 mb-1">NAICS Codes</label>
                                                        <textarea id="naics_codes" name="naics_codes" rows="2" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Enter NAICS codes...">{{ old('naics_codes') }}</textarea>
                                                    </div>
                                                    <div>
                                                        <label for="industry_connections" class="block text-sm font-medium text-gray-700 mb-1">Industry Connections</label>
                                                        <textarea id="industry_connections" name="industry_connections" rows="2" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Describe your industry connections...">{{ old('industry_connections') }}</textarea>
                                                    </div>
                                                    <div>
                                                        <label for="core_specialty_area" class="block text-sm font-medium text-gray-700 mb-1">Core Specialty Area</label>
                                                        <textarea id="core_specialty_area" name="core_specialty_area" rows="2" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Describe your core specialty area...">{{ old('core_specialty_area') }}</textarea>
                                                    </div>
                                                    <div>
                                                        <label for="contract_vehicles" class="block text-sm font-medium text-gray-700 mb-1">Contract Vehicles</label>
                                                        <textarea id="contract_vehicles" name="contract_vehicles" rows="2" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="List your contract vehicles...">{{ old('contract_vehicles') }}</textarea>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Preferences Section -->
                                            <div class="bg-gray-50 p-4 rounded-lg">
                                                <h4 class="text-lg font-medium text-gray-900 mb-4">Preferences</h4>
                                                <div class="space-y-4">
                                                    <div>
                                                        <label for="meeting_preference" class="block text-sm font-medium text-gray-700 mb-1">Meeting Preference *</label>
                                                        <input type="text" id="meeting_preference" name="meeting_preference" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="e.g., In-person, Virtual, Hybrid, Morning preference, etc." value="{{ old('meeting_preference') }}">
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Event Specific Section -->
                                            <div class="bg-gray-50 p-4 rounded-lg">
                                                <h4 class="text-lg font-medium text-gray-900 mb-4">Event Participation</h4>
                                                <div class="space-y-4">
                                                    <div>
                                                        <label class="text-sm font-medium text-gray-700 mb-2 block">Small Business Forum: Increasing the Defense Industrial Base</label>
                                                        <div class="space-y-2">
                                                            <div class="flex items-center">
                                                                <input id="small_business_forum_yes" name="small_business_forum" type="radio" value="Yes (In-person)" {{ old('small_business_forum') == 'Yes (In-person)' ? 'checked' : '' }} class="w-4 h-4 text-red-600 bg-gray-100 border-gray-300 focus:ring-red-500 focus:ring-2">
                                                                <label for="small_business_forum_yes" class="ml-3 text-sm text-gray-700">Yes (In-person)</label>
                                                            </div>
                                                            <div class="flex items-center">
                                                                <input id="small_business_forum_no" name="small_business_forum" type="radio" value="No" {{ old('small_business_forum') == 'No' ? 'checked' : '' }} class="w-4 h-4 text-red-600 bg-gray-100 border-gray-300 focus:ring-red-500 focus:ring-2">
                                                                <label for="small_business_forum_no" class="ml-3 text-sm text-gray-700">No</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div>
                                                        <label class="text-sm font-medium text-gray-700 mb-2 block">Small Business Matchmaker</label>
                                                        <div class="space-y-2">
                                                            <div class="flex items-center">
                                                                <input id="small_business_matchmaker_yes" name="small_business_matchmaker" type="radio" value="Yes (In-person)" {{ old('small_business_matchmaker') == 'Yes (In-person)' ? 'checked' : '' }} class="w-4 h-4 text-red-600 bg-gray-100 border-gray-300 focus:ring-red-500 focus:ring-2">
                                                                <label for="small_business_matchmaker_yes" class="ml-3 text-sm text-gray-700">Yes (In-person)</label>
                                                            </div>
                                                            <div class="flex items-center">
                                                                <input id="small_business_matchmaker_no" name="small_business_matchmaker" type="radio" value="No" {{ old('small_business_matchmaker') == 'No' ? 'checked' : '' }} class="w-4 h-4 text-red-600 bg-gray-100 border-gray-300 focus:ring-red-500 focus:ring-2">
                                                                <label for="small_business_matchmaker_no" class="ml-3 text-sm text-gray-700">No</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Additional Notes Section -->
                                            <div class="bg-gray-50 p-4 rounded-lg">
                                                <h4 class="text-lg font-medium text-gray-900 mb-4">Additional Information</h4>
                                                <div>
                                                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Additional Notes (Optional)</label>
                                                    <textarea id="notes" name="notes" rows="3" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Any special requirements or notes...">{{ old('notes') }}</textarea>
                                                </div>
                                            </div>

                                            <!-- Terms and Submit -->
                                            <div class="bg-gray-50 p-4 rounded-lg">
                                                <div class="flex items-start">
                                                    <div class="flex items-center h-5">
                                                        <input id="terms_accepted" name="terms_accepted" type="checkbox" required class="w-4 h-4 text-red-600 bg-gray-100 border-gray-300 rounded focus:ring-red-500 focus:ring-2">
                                                    </div>
                                                    <div class="ml-3 text-sm">
                                                        <label for="terms_accepted" class="font-medium text-gray-700">I accept the <a href="#" class="text-red-600 hover:text-red-500">terms and conditions</a></label>
                                                    </div>
                                                </div>
                                                
                                                <button type="submit" class="w-full mt-4 bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors duration-200">
                                                    Register for Event
                                                </button>
                                            </div>
                                        </form>

                                                <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const typeSelect = document.getElementById('type');
                            const checkinTypeContainer = document.getElementById('checkin_type_container');
                            const checkinTypeSelect = document.getElementById('checkin_type');

                            // Handle type selection change
                            typeSelect.addEventListener('change', function() {
                                if (this.value === 'checkin') {
                                    checkinTypeContainer.classList.remove('hidden');
                                    checkinTypeSelect.required = true;
                                } else {
                                    checkinTypeContainer.classList.add('hidden');
                                    checkinTypeSelect.required = false;
                                    checkinTypeSelect.value = '';
                                }
                            });

                            // Form submission handling
                            document.getElementById('registrationForm').addEventListener('submit', function(e) {
                                console.log('Registration form submitted');
                                console.log('Form action:', this.action);
                                console.log('Form method:', this.method);
                                console.log('CSRF token:', document.querySelector('input[name="_token"]').value);
                                console.log('Event ID:', document.querySelector('input[name="event_id"]').value);
                                console.log('First Name:', document.querySelector('input[name="first_name"]').value);
                                console.log('Last Name:', document.querySelector('input[name="last_name"]').value);
                                console.log('Email:', document.querySelector('input[name="email"]').value);
                                console.log('Organization:', document.querySelector('input[name="organization_name"]').value);
                                console.log('Type:', document.querySelector('select[name="type"]').value);
                                console.log('Meeting Preference:', document.querySelector('select[name="meeting_preference"]').value);
                                console.log('Small Business Forum:', document.querySelector('input[name="small_business_forum"]').checked);
                                console.log('Small Business Matchmaker:', document.querySelector('input[name="small_business_matchmaker"]').checked);
                                console.log('Notes:', document.querySelector('textarea[name="notes"]').value);
                                console.log('Terms accepted:', document.querySelector('input[name="terms_accepted"]').checked);
                                
                                // Show loading state
                                const submitBtn = this.querySelector('button[type="submit"]');
                                submitBtn.disabled = true;
                                submitBtn.textContent = 'Processing...';
                            });
                        });
                        </script>
                                    @endif
                                @endif
                            @else
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                    <div class="text-center">
                                        <a href="{{ route('register') }}" class="inline-flex items-center justify-center w-full bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg text-base font-medium transition-colors duration-200">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                            </svg>
                                            Register for This Event
                                        </a>
                                        <p class="mt-2 text-sm text-blue-700">Create an account to register for this event</p>
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
