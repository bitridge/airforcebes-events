@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Page Header -->
        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-bold text-gray-900">My Profile</h1>
            <a href="{{ route('profile.edit') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Edit Profile
            </a>
        </div>



        <!-- Profile Overview -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center space-x-6">
                <!-- Avatar -->
                <div class="flex-shrink-0">
                    @if($user->profile_picture)
                        <img src="{{ asset('storage/' . $user->profile_picture) }}" 
                             alt="Profile Picture" 
                             class="h-24 w-24 rounded-full object-cover">
                    @else
                        <div class="h-24 w-24 bg-indigo-600 rounded-full flex items-center justify-center">
                            <span class="text-2xl font-medium text-white">{{ $user->initials ?? 'U' }}</span>
                        </div>
                    @endif
                </div>

                <!-- User Info -->
                <div class="flex-1">
                    <h3 class="text-2xl font-bold text-gray-900">{{ $user->name ?? 'User' }}</h3>
                    <p class="text-gray-600">{{ $user->email ?? 'email@example.com' }}</p>
                    
                    <div class="mt-2 flex items-center space-x-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                            @if($user->isAdmin()) bg-blue-100 text-blue-800 @else bg-green-100 text-green-800 @endif">
                            {{ $user->getRoleDisplayName() ?? 'User' }}
                        </span>
                        
                        @if($user->phone)
                            <span class="text-sm text-gray-500">
                                <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                                {{ $user->formatted_phone ?? $user->phone }}
                            </span>
                        @endif
                        
                        @if($user->organization_name)
                            <span class="text-sm text-gray-500">
                                <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                                {{ $user->organization_name }}
                            </span>
                        @endif

                        @if($user->title)
                            <span class="text-sm text-gray-500">
                                <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 00-2 2v2m-8 0v2a2 2 0 002 2h4a2 2 0 002 2v-2m8 0v2a2 2 0 01-2 2h-4a2 2 0 01-2-2v2"></path>
                                </svg>
                                {{ $user->title }}
                            </span>
                        @endif
                    </div>

                    @if($user->bio)
                        <div class="mt-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Bio</h4>
                            <p class="text-sm text-gray-600">{{ $user->bio }}</p>
                        </div>
                    @endif

                    <!-- Business Information -->
                    @if($user->naics_codes || $user->industry_connections || $user->core_specialty_area || $user->contract_vehicles)
                        <div class="mt-6 border-t pt-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-3">Business Information</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                @if($user->naics_codes)
                                    <div>
                                        <span class="font-medium text-gray-600">NAICS Codes:</span>
                                        <span class="text-gray-800">{{ $user->naics_codes }}</span>
                                    </div>
                                @endif
                                @if($user->industry_connections)
                                    <div>
                                        <span class="font-medium text-gray-600">Industry:</span>
                                        <span class="text-gray-800">{{ $user->industry_connections }}</span>
                                    </div>
                                @endif
                                @if($user->core_specialty_area)
                                    <div>
                                        <span class="font-medium text-gray-600">Specialty Area:</span>
                                        <span class="text-gray-800">{{ $user->core_specialty_area }}</span>
                                    </div>
                                @endif
                                @if($user->contract_vehicles)
                                    <div>
                                        <span class="font-medium text-gray-600">Contract Vehicles:</span>
                                        <span class="text-gray-800">{{ $user->contract_vehicles }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Preferences -->
                    @if($user->meeting_preference && $user->meeting_preference !== 'no_preference')
                        <div class="mt-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Meeting Preference</h4>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ ucwords(str_replace('_', ' ', $user->meeting_preference)) }}
                            </span>
                        </div>
                    @endif

                    <!-- Event Participation -->
                    @if($user->small_business_forum || $user->small_business_matchmaker)
                        <div class="mt-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Event Participation</h4>
                            <div class="space-y-1">
                                @if($user->small_business_forum)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 mr-2">
                                        Small Business Forum
                                    </span>
                                @endif
                                @if($user->small_business_matchmaker)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        Small Business Matchmaker
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-8 w-8 bg-indigo-500 rounded-md">
                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Registrations</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $registrationCount ?? 0 }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-8 w-8 bg-green-500 rounded-md">
                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Events Attended</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $checkInCount ?? 0 }}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-8 w-8 bg-blue-500 rounded-md">
                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Account Status</dt>
                            <dd class="text-lg font-medium text-gray-900">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <a href="{{ route('registrations.index') }}" 
                   class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-8 w-8 bg-blue-500 rounded-md">
                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-sm font-medium text-gray-900">My Registrations</h4>
                        <p class="text-sm text-gray-500">View and manage your event registrations</p>
                    </div>
                </a>

                <a href="{{ route('events.index') }}" 
                   class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors duration-200">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center h-8 w-8 bg-green-500 rounded-md">
                            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-sm font-medium text-gray-900">Browse Events</h4>
                        <p class="text-sm text-gray-500">Discover and register for upcoming events</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
