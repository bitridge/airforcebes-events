@extends('layouts.app')

@section('title', 'Edit Attendee - ' . config('app.name'))

@section('content')
    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Edit Attendee</h1>
                    <p class="text-gray-600">{{ $attendee->name }}</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('admin.attendees.show', $attendee) }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Profile
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
                <form action="{{ route('admin.attendees.update', $attendee) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Personal Information</h3>
                    </div>
                    
                    <div class="px-6 py-4 space-y-6">
                        <!-- First Name and Last Name -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="first_name" class="block text-sm font-medium text-gray-700">First Name *</label>
                                <input type="text" id="first_name" name="first_name" 
                                       value="{{ old('first_name', $attendee->first_name) }}" required
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                @error('first_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name *</label>
                                <input type="text" id="last_name" name="last_name" 
                                       value="{{ old('last_name', $attendee->last_name) }}" required
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                @error('last_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email *</label>
                            <input type="email" id="email" name="email" 
                                   value="{{ old('email', $attendee->email) }}" required
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Phone -->
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                            <input type="tel" id="phone" name="phone" 
                                   value="{{ old('phone', $attendee->phone) }}"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            @error('phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Organization and Title -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="organization_name" class="block text-sm font-medium text-gray-700">Organization Name</label>
                                <input type="text" id="organization_name" name="organization_name" 
                                       value="{{ old('organization_name', $attendee->organization_name) }}"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                @error('organization_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="title" class="block text-sm font-medium text-gray-700">Job Title</label>
                                <input type="text" id="title" name="title" 
                                       value="{{ old('title', $attendee->title) }}"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                @error('title')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Business Information -->
                        <div class="border-t pt-6">
                            <h4 class="text-md font-medium text-gray-900 mb-4">Business Information</h4>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="naics_codes" class="block text-sm font-medium text-gray-700">NAICS Codes</label>
                                    <input type="text" id="naics_codes" name="naics_codes" 
                                           value="{{ old('naics_codes', $attendee->naics_codes) }}"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    @error('naics_codes')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="industry_connections" class="block text-sm font-medium text-gray-700">Industry Connections</label>
                                    <input type="text" id="industry_connections" name="industry_connections" 
                                           value="{{ old('industry_connections', $attendee->industry_connections) }}"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    @error('industry_connections')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                <div>
                                    <label for="core_specialty_area" class="block text-sm font-medium text-gray-700">Core Specialty Area</label>
                                    <input type="text" id="core_specialty_area" name="core_specialty_area" 
                                           value="{{ old('core_specialty_area', $attendee->core_specialty_area) }}"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    @error('core_specialty_area')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="contract_vehicles" class="block text-sm font-medium text-gray-700">Contract Vehicles</label>
                                    <input type="text" id="contract_vehicles" name="contract_vehicles" 
                                           value="{{ old('contract_vehicles', $attendee->contract_vehicles) }}"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    @error('contract_vehicles')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Preferences -->
                        <div class="border-t pt-6">
                            <h4 class="text-md font-medium text-gray-900 mb-4">Preferences</h4>
                            
                            <div>
                                <label for="meeting_preference" class="block text-sm font-medium text-gray-700">Meeting Preference</label>
                                <select id="meeting_preference" name="meeting_preference"
                                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                    <option value="no_preference" {{ $attendee->meeting_preference === 'no_preference' ? 'selected' : '' }}>No Preference</option>
                                    <option value="in_person" {{ $attendee->meeting_preference === 'in_person' ? 'selected' : '' }}>In Person</option>
                                    <option value="virtual" {{ $attendee->meeting_preference === 'virtual' ? 'selected' : '' }}>Virtual</option>
                                    <option value="hybrid" {{ $attendee->meeting_preference === 'hybrid' ? 'selected' : '' }}>Hybrid</option>
                                    <option value="prefer_morning" {{ $attendee->meeting_preference === 'prefer_morning' ? 'selected' : '' }}>Prefer Morning</option>
                                    <option value="prefer_afternoon" {{ $attendee->meeting_preference === 'prefer_afternoon' ? 'selected' : '' }}>Prefer Afternoon</option>
                                    <option value="prefer_evening" {{ $attendee->meeting_preference === 'prefer_evening' ? 'selected' : '' }}>Prefer Evening</option>
                                </select>
                                @error('meeting_preference')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Event Participation -->
                        <div class="border-t pt-6">
                            <h4 class="text-md font-medium text-gray-900 mb-4">Event Participation</h4>
                            
                            <div class="space-y-3">
                                <label class="flex items-center">
                                    <input type="checkbox" name="small_business_forum" value="1" 
                                           {{ $attendee->small_business_forum ? 'checked' : '' }}
                                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                    <span class="ml-2 text-sm text-gray-700">Small Business Forum: Increasing the Defense Industrial Base</span>
                                </label>
                                
                                <label class="flex items-center">
                                    <input type="checkbox" name="small_business_matchmaker" value="1" 
                                           {{ $attendee->small_business_matchmaker ? 'checked' : '' }}
                                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                    <span class="ml-2 text-sm text-gray-700">Small Business Matchmaker</span>
                                </label>
                            </div>
                        </div>

                        <!-- Account Settings -->
                        <div class="border-t pt-6">
                            <h4 class="text-md font-medium text-gray-900 mb-4">Account Settings</h4>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="role" class="block text-sm font-medium text-gray-700">Role *</label>
                                    <select id="role" name="role" required
                                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                        <option value="attendee" {{ $attendee->role === 'attendee' ? 'selected' : '' }}>Attendee</option>
                                        <option value="admin" {{ $attendee->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                    </select>
                                    @error('role')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="is_active" class="flex items-center">
                                        <input type="checkbox" id="is_active" name="is_active" value="1" 
                                               {{ $attendee->is_active ? 'checked' : '' }}
                                               class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                        <span class="ml-2 text-sm text-gray-700">Active Account</span>
                                    </label>
                                    <p class="mt-1 text-sm text-gray-500">Uncheck to deactivate this account</p>
                                </div>
                            </div>
                        </div>

                        <!-- Password Change -->
                        <div class="border-t pt-6">
                            <h4 class="text-md font-medium text-gray-900 mb-4">Change Password</h4>
                            <p class="text-sm text-gray-600 mb-4">Leave password fields blank to keep the current password</p>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="new_password" class="block text-sm font-medium text-gray-700">New Password</label>
                                    <input type="password" id="new_password" name="new_password" minlength="8"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <p class="mt-1 text-sm text-gray-500">Minimum 8 characters</p>
                                    @error('new_password')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div>
                                    <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                                    <input type="password" id="new_password_confirmation" name="new_password_confirmation" minlength="8"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    @error('new_password_confirmation')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="px-6 py-4 bg-gray-50 text-right rounded-b-lg">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Update Attendee
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

