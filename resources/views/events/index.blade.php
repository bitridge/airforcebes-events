@extends('layouts.app')

@section('title', 'Events - ' . config('app.name'))
@section('description', 'Browse all upcoming Air Force events, workshops, and networking opportunities. Filter by date, venue, and search by keywords.')

@section('content')
    <!-- Hero Section -->
    <section class="bg-slate-800 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-4xl font-bold mb-4">Upcoming Events</h1>
                <p class="text-xl text-slate-300 max-w-2xl mx-auto">
                    Discover professional development opportunities and connect with fellow Air Force professionals
                </p>
            </div>
        </div>
    </section>

    <!-- Filters Section -->
    <section class="bg-white border-b border-gray-200 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <form method="GET" action="{{ route('events.index') }}" class="space-y-4 lg:space-y-0 lg:grid lg:grid-cols-12 lg:gap-4">
                <!-- Search -->
                <div class="lg:col-span-4">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search Events</label>
                    <input type="text" 
                           id="search" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Search by title, description, or venue..."
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <!-- Date From -->
                <div class="lg:col-span-2">
                    <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                    <input type="date" 
                           id="date_from" 
                           name="date_from" 
                           value="{{ request('date_from') }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <!-- Date To -->
                <div class="lg:col-span-2">
                    <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                    <input type="date" 
                           id="date_to" 
                           name="date_to" 
                           value="{{ request('date_to') }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                <!-- Venue -->
                <div class="lg:col-span-2">
                    <label for="venue" class="block text-sm font-medium text-gray-700 mb-1">Venue</label>
                    <select id="venue" 
                            name="venue" 
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All Venues</option>
                        @foreach($venues as $venue)
                            <option value="{{ $venue }}" {{ request('venue') === $venue ? 'selected' : '' }}>
                                {{ $venue }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Actions -->
                <div class="lg:col-span-2 flex space-x-2">
                    <button type="submit" 
                            class="flex-1 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                        Filter
                    </button>
                    <a href="{{ route('events.index') }}" 
                       class="flex-1 text-center bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                        Clear
                    </a>
                </div>
            </form>
        </div>
    </section>

    <!-- Events Grid -->
    <section class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if($events->count() > 0)
                <!-- Results Info -->
                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">
                            {{ $events->total() }} {{ Str::plural('Event', $events->total()) }}
                            @if(request()->hasAny(['search', 'date_from', 'date_to', 'venue']))
                                <span class="text-gray-500">found</span>
                            @endif
                        </h2>
                        @if(request('search'))
                            <p class="text-gray-600 mt-1">Results for "{{ request('search') }}"</p>
                        @endif
                    </div>
                    
                    <div class="text-sm text-gray-500">
                        Showing {{ $events->firstItem() }}-{{ $events->lastItem() }} of {{ $events->total() }} results
                    </div>
                </div>

                <!-- Events Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($events as $event)
                        <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-200">
                            @if($event->featured_image)
                                <img src="{{ asset('storage/' . $event->featured_image) }}" alt="{{ $event->title }}" class="w-full h-48 object-cover">
                            @else
                                <div class="w-full h-48 bg-gradient-to-br from-slate-600 to-slate-800 flex items-center justify-center">
                                    <svg class="w-16 h-16 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            @endif
                            
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Upcoming
                                    </span>
                                    @if($event->max_capacity)
                                        <span class="text-sm text-gray-500">
                                            {{ $event->capacity_status['registered'] }}/{{ $event->max_capacity }} registered
                                        </span>
                                    @endif
                                </div>
                                
                                <h3 class="text-xl font-semibold text-gray-900 mb-3">{{ $event->title }}</h3>
                                <p class="text-gray-600 text-sm mb-4 line-clamp-3">{{ Str::limit($event->description, 120) }}</p>
                                
                                <div class="space-y-2 mb-4">
                                    <div class="flex items-center text-sm text-gray-500">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        {{ $event->formatted_date_range }}
                                    </div>
                                    <div class="flex items-center text-sm text-gray-500">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        {{ $event->formatted_time_range }}
                                    </div>
                                    <div class="flex items-center text-sm text-gray-500">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        {{ $event->venue }}
                                    </div>
                                </div>

                                <!-- Progress Bar for Capacity -->
                                @if($event->max_capacity)
                                    <div class="mb-4">
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div class="bg-slate-600 h-2 rounded-full" style="width: {{ min(100, $event->capacity_status['percentage']) }}%"></div>
                                        </div>
                                    </div>
                                @endif
                                
                                <div class="flex items-center justify-between">
                                    <a href="{{ route('events.show', $event->slug) }}" class="inline-flex items-center text-red-600 hover:text-red-700 font-medium">
                                        View Details
                                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </a>

                                    @if($event->isFull())
                                        <span class="text-sm text-red-600 font-medium">Event Full</span>
                                    @elseif(!$event->isRegistrationOpen())
                                        <span class="text-sm text-gray-500 font-medium">Registration Closed</span>
                                    @else
                                        <span class="text-sm text-green-600 font-medium">Registration Open</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-12">
                    {{ $events->links() }}
                </div>
            @else
                <!-- No Events State -->
                <div class="text-center py-16">
                    <svg class="mx-auto h-16 w-16 text-gray-400 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <h3 class="text-xl font-medium text-gray-900 mb-3">
                        @if(request()->hasAny(['search', 'date_from', 'date_to', 'venue']))
                            No events found matching your criteria
                        @else
                            No upcoming events
                        @endif
                    </h3>
                    <p class="text-gray-500 mb-6">
                        @if(request()->hasAny(['search', 'date_from', 'date_to', 'venue']))
                            Try adjusting your search filters or check back later for new events.
                        @else
                            Check back soon for new events and opportunities.
                        @endif
                    </p>
                    @if(request()->hasAny(['search', 'date_from', 'date_to', 'venue']))
                        <a href="{{ route('events.index') }}" class="inline-flex items-center bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors duration-200">
                            View All Events
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </section>
@endsection
