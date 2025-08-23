<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('My Profile') }}
            </h2>
            <a href="{{ route('profile.edit') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                {{ __('Edit Profile') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Profile Overview -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex items-center space-x-6">
                        <!-- Avatar -->
                        <div class="flex-shrink-0">
                            <div class="h-20 w-20 bg-indigo-600 rounded-full flex items-center justify-center">
                                <span class="text-2xl font-medium text-white">{{ $user->initials }}</span>
                            </div>
                        </div>

                        <!-- User Info -->
                        <div class="flex-1">
                            <h3 class="text-2xl font-bold text-gray-900">{{ $user->name }}</h3>
                            <p class="text-gray-600">{{ $user->email }}</p>
                            
                            <div class="mt-2 flex items-center space-x-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    @if($user->isAdmin()) bg-blue-100 text-blue-800 @else bg-green-100 text-green-800 @endif">
                                    {{ $user->getRoleDisplayName() }}
                                </span>
                                
                                @if($user->phone)
                                    <span class="text-sm text-gray-500">
                                        <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                        </svg>
                                        {{ $user->formatted_phone }}
                                    </span>
                                @endif
                                
                                @if($user->organization)
                                    <span class="text-sm text-gray-500">
                                        <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                        </svg>
                                        {{ $user->organization }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
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
                                    <dd class="text-lg font-medium text-gray-900">{{ $registrationCount }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
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
                                    <dd class="text-lg font-medium text-gray-900">{{ $checkInCount }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
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
                                    <dt class="text-sm font-medium text-gray-500 truncate">Upcoming Events</dt>
                                    <dd class="text-lg font-medium text-gray-900">{{ $upcomingRegistrations->count() }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upcoming Events -->
            @if($upcomingRegistrations->count() > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Upcoming Events') }}</h3>
                        
                        <div class="space-y-4">
                            @foreach($upcomingRegistrations as $registration)
                                <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1">
                                            <h4 class="text-lg font-medium text-gray-900">{{ $registration->event->title }}</h4>
                                            <p class="text-sm text-gray-600 mt-1">{{ $registration->event->venue }}</p>
                                            <div class="flex items-center space-x-4 mt-2">
                                                <span class="text-sm text-gray-500">
                                                    <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                                                    </svg>
                                                    {{ $registration->event->formatted_date_range }}
                                                </span>
                                                <span class="text-sm text-gray-500">
                                                    <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    {{ $registration->event->formatted_time_range }}
                                                </span>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Registered
                                                </span>
                                            </div>
                                        </div>
                                        <div class="flex space-x-2">
                                            <a href="{{ route('events.show', $registration->event) }}" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                                View Event
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Past Events -->
            @if($pastRegistrations->count() > 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('Past Events') }}</h3>
                        
                        <div class="space-y-4 max-h-96 overflow-y-auto">
                            @foreach($pastRegistrations->take(10) as $registration)
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1">
                                            <h4 class="text-lg font-medium text-gray-900">{{ $registration->event->title }}</h4>
                                            <p class="text-sm text-gray-600 mt-1">{{ $registration->event->venue }}</p>
                                            <div class="flex items-center space-x-4 mt-2">
                                                <span class="text-sm text-gray-500">
                                                    <svg class="inline w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                                                    </svg>
                                                    {{ $registration->event->formatted_date_range }}
                                                </span>
                                                @if($registration->isCheckedIn())
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                        Attended
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                        Registered
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        @if($pastRegistrations->count() > 10)
                            <p class="text-sm text-gray-500 mt-4 text-center">
                                Showing 10 of {{ $pastRegistrations->count() }} past events
                            </p>
                        @endif
                    </div>
                </div>
            @endif

            @if($upcomingRegistrations->count() === 0 && $pastRegistrations->count() === 0)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No event registrations</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by registering for an upcoming event.</p>
                        <div class="mt-6">
                            <a href="{{ route('events.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Browse Events
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
