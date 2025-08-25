@extends('layouts.app')

@section('title', 'Edit Registration - ' . config('app.name'))

@section('content')
    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Edit Registration</h1>
                    <p class="text-gray-600">Registration #{{ $registration->registration_code }}</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('admin.registrations.show', $registration) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Details
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

            <!-- Edit Form -->
            <div class="bg-white shadow rounded-lg">
                <form action="{{ route('admin.registrations.update', $registration) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Registration Information</h3>
                    </div>
                    
                    <div class="px-6 py-4 space-y-6">
                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                            <select id="status" name="status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:ring-indigo-500 sm:text-sm rounded-md">
                                <option value="pending" {{ $registration->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="confirmed" {{ $registration->status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                <option value="cancelled" {{ $registration->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>

                        <!-- Personal Information Section -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="text-lg font-medium text-gray-900 mb-4">Personal Information</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="first_name" class="block text-sm font-medium text-gray-700">First Name *</label>
                                    <input type="text" id="first_name" name="first_name" required value="{{ old('first_name', $registration->first_name) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>
                                <div>
                                    <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name *</label>
                                    <input type="text" id="last_name" name="last_name" required value="{{ old('last_name', $registration->last_name) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700">Email *</label>
                                    <input type="email" id="email" name="email" required value="{{ old('email', $registration->email) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>
                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                                    <input type="tel" id="phone" name="phone" value="{{ old('phone', $registration->phone) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>
                                <div class="md:col-span-2">
                                    <label for="organization_name" class="block text-sm font-medium text-gray-700">Organization Name *</label>
                                    <input type="text" id="organization_name" name="organization_name" required value="{{ old('organization_name', $registration->organization_name) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>
                                <div class="md:col-span-2">
                                    <label for="title" class="block text-sm font-medium text-gray-700">Job Title</label>
                                    <input type="text" id="title" name="title" value="{{ old('title', $registration->title) }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>
                            </div>
                        </div>

                        <!-- Registration Type Section -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="text-lg font-medium text-gray-900 mb-4">Registration Type</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="type" class="block text-sm font-medium text-gray-700">Type *</label>
                                    <select id="type" name="type" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        <option value="">Select Type</option>
                                        <option value="registration" {{ old('type', $registration->type) == 'registration' ? 'selected' : '' }}>Registration (CT)</option>
                                        <option value="checkin" {{ old('type', $registration->type) == 'checkin' ? 'selected' : '' }}>Check-in (CT)</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="checkin_type" class="block text-sm font-medium text-gray-700">Check-in Type</label>
                                    <select id="checkin_type" name="checkin_type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        <option value="">Select Check-in Type</option>
                                        <option value="in_person" {{ old('checkin_type', $registration->checkin_type) == 'in_person' ? 'selected' : '' }}>In Person</option>
                                        <option value="virtual" {{ old('checkin_type', $registration->checkin_type) == 'virtual' ? 'selected' : '' }}>Virtual</option>
                                        <option value="hybrid" {{ old('checkin_type', $registration->checkin_type) == 'hybrid' ? 'selected' : '' }}>Hybrid</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Business Information Section -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="text-lg font-medium text-gray-900 mb-4">Business Information</h4>
                            <div class="space-y-4">
                                <div>
                                    <label for="naics_codes" class="block text-sm font-medium text-gray-700">NAICS Codes</label>
                                    <textarea id="naics_codes" name="naics_codes" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">{{ old('naics_codes', $registration->naics_codes) }}</textarea>
                                </div>
                                <div>
                                    <label for="industry_connections" class="block text-sm font-medium text-gray-700">Industry Connections</label>
                                    <textarea id="industry_connections" name="industry_connections" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">{{ old('industry_connections', $registration->industry_connections) }}</textarea>
                                </div>
                                <div>
                                    <label for="core_specialty_area" class="block text-sm font-medium text-gray-700">Core Specialty Area</label>
                                    <textarea id="core_specialty_area" name="core_specialty_area" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">{{ old('core_specialty_area', $registration->core_specialty_area) }}</textarea>
                                </div>
                                <div>
                                    <label for="contract_vehicles" class="block text-sm font-medium text-gray-700">Contract Vehicles</label>
                                    <textarea id="contract_vehicles" name="contract_vehicles" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">{{ old('contract_vehicles', $registration->contract_vehicles) }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Preferences Section -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="text-lg font-medium text-gray-900 mb-4">Preferences</h4>
                            <div>
                                <label for="meeting_preference" class="block text-sm font-medium text-gray-700">Meeting Preference *</label>
                                <select id="meeting_preference" name="meeting_preference" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <option value="">Select Preference</option>
                                    <option value="in_person" {{ old('meeting_preference', $registration->meeting_preference) == 'in_person' ? 'selected' : '' }}>In Person</option>
                                    <option value="virtual" {{ old('meeting_preference', $registration->meeting_preference) == 'virtual' ? 'selected' : '' }}>Virtual</option>
                                    <option value="hybrid" {{ old('meeting_preference', $registration->meeting_preference) == 'hybrid' ? 'selected' : '' }}>Hybrid</option>
                                    <option value="no_preference" {{ old('meeting_preference', $registration->meeting_preference) == 'no_preference' ? 'selected' : '' }}>No Preference</option>
                                </select>
                            </div>
                        </div>

                        <!-- Event Specific Section -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="text-lg font-medium text-gray-700 mb-4">Event Participation</h4>
                            <div class="space-y-4">
                                <div class="flex items-center">
                                    <input id="small_business_forum" name="small_business_forum" type="checkbox" value="1" {{ old('small_business_forum', $registration->small_business_forum) ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500 focus:ring-2">
                                    <label for="small_business_forum" class="ml-3 text-sm font-medium text-gray-700">Small Business Forum: Increasing the Defense Industrial Base</label>
                                </div>
                                <div class="flex items-center">
                                    <input id="small_business_matchmaker" name="small_business_matchmaker" type="checkbox" value="1" {{ old('small_business_matchmaker', $registration->small_business_matchmaker) ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 rounded focus:ring-indigo-500 focus:ring-2">
                                    <label for="small_business_matchmaker" class="ml-3 text-sm font-medium text-gray-700">Small Business Matchmaker</label>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                            <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">{{ old('notes', $registration->notes) }}</textarea>
                            <p class="mt-2 text-sm text-gray-500">Add any notes or comments about this registration.</p>
                        </div>

                        <!-- Read-only Information -->
                        <div class="bg-gray-50 p-4 rounded-md">
                            <h4 class="text-sm font-medium text-gray-700 mb-3">Registration Details (Read-only)</h4>
                            <dl class="grid grid-cols-1 gap-x-4 gap-y-2 sm:grid-cols-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Registration Code</dt>
                                    <dd class="text-sm text-gray-900 font-mono">{{ $registration->registration_code }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Event</dt>
                                    <dd class="text-sm text-gray-900">{{ $registration->event->title }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Attendee</dt>
                                    <dd class="text-sm text-gray-900">{{ $registration->user->name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                                    <dd class="text-sm text-gray-900">{{ $registration->user->email }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Registration Date</dt>
                                    <dd class="text-sm text-gray-900">{{ $registration->created_at->format('M d, Y g:i A') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">QR Code</dt>
                                    <dd class="text-sm text-gray-900">
                                        @if($registration->qr_code_data)
                                            <span class="text-green-600">Generated</span>
                                        @else
                                            <span class="text-red-600">Not generated</span>
                                        @endif
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                    
                    <div class="px-6 py-4 bg-gray-50 text-right rounded-b-lg">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Update Registration
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
