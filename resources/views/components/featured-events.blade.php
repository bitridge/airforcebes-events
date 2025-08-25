<!-- Featured Events Section -->
<section class="bg-gray-50 py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-slate-800 mb-4">Upcoming Events</h2>
            <p class="text-lg text-slate-600">Join our professional development and networking events</p>
        </div>

        @if($events->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
                @foreach($events as $event)
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-200">
                        @if($event->featured_image)
                            <a href="{{ route('events.show', $event->slug) }}" class="block">
                                <img src="{{ asset('storage/' . $event->featured_image) }}" alt="{{ $event->title }}" class="w-full h-48 object-cover hover:opacity-90 transition-opacity duration-200">
                            </a>
                        @else
                            <a href="{{ route('events.show', $event->slug) }}" class="block">
                                <div class="w-full h-48 bg-gradient-to-br from-slate-600 to-slate-800 flex items-center justify-center hover:opacity-90 transition-opacity duration-200">
                                <svg class="w-16 h-16 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            </a>
                        @endif
                        
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Upcoming
                                </span>
                                @if($event->max_capacity)
                                    <span class="text-sm text-slate-500">
                                        {{ $event->capacity_status['registered'] }}/{{ $event->max_capacity }} registered
                                    </span>
                                @endif
                            </div>
                            
                            <a href="{{ route('events.show', $event->slug) }}" class="block">
                                <h3 class="text-xl font-semibold text-slate-800 mb-3 hover:text-red-600 transition-colors duration-200">{{ $event->title }}</h3>
                            </a>
                            <p class="text-slate-600 text-sm mb-4 line-clamp-3">{{ Str::limit($event->description, 120) }}</p>
                            
                            <div class="space-y-2 mb-4">
                                <div class="flex items-center text-sm text-slate-500">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    {{ $event->formatted_date_range }}
                                </div>
                                <div class="flex items-center text-sm text-slate-500">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    {{ $event->formatted_time_range }}
                                </div>
                                <div class="flex items-center text-sm text-slate-500">
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
                            
                            <a href="{{ route('events.show', $event->slug) }}" class="inline-flex items-center text-red-600 hover:text-red-700 font-medium">
                                View Details
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- View All Events Button -->
            <div class="text-center">
                <a href="{{ route('events.index') }}" class="inline-flex items-center bg-slate-800 hover:bg-slate-900 text-white px-6 py-3 rounded-lg font-semibold transition-colors duration-200">
                    View All Events
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
        @else
            <!-- No Events State -->
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-slate-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <h3 class="text-lg font-medium text-slate-900 mb-2">No upcoming events</h3>
                <p class="text-slate-500">Check back soon for new events and opportunities.</p>
            </div>
        @endif
    </div>
</section>
