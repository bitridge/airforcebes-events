<x-app-layout>
    <x-slot name="title">Event Reports - {{ config('app.name') }}</x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Event Reports</h1>
                    <p class="mt-2 text-sm text-gray-600">Generate detailed reports for individual events</p>
                </div>
                <a href="{{ route('admin.reports.index') }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Reports
                </a>
            </div>

            <!-- Event Selection Form -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Generate Event Report</h3>
                <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="event_id" class="block text-sm font-medium text-gray-700 mb-1">Select Event</label>
                        <select id="event_id" name="event_id" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500">
                            <option value="">Choose an event...</option>
                            @foreach($events as $event)
                                <option value="{{ $event->id }}" {{ request('event_id') == $event->id ? 'selected' : '' }}>
                                    {{ $event->title }} ({{ $event->start_date->format('M j, Y') }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label for="date_range" class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
                        <select id="date_range" name="date_range" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500">
                            <option value="7" {{ request('date_range') == '7' ? 'selected' : '' }}>Last 7 days</option>
                            <option value="30" {{ request('date_range') == '30' ? 'selected' : '' }}>Last 30 days</option>
                            <option value="90" {{ request('date_range') == '90' ? 'selected' : '' }}>Last 90 days</option>
                            <option value="365" {{ request('date_range') == '365' ? 'selected' : '' }}>Last year</option>
                        </select>
                    </div>
                    
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">
                            Generate Report
                        </button>
                    </div>
                </form>
            </div>

            <!-- Events List -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">All Events</h3>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Venue</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registrations</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check-ins</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($events as $event)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $event->title }}</div>
                                            @if($event->is_featured)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    Featured
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $event->start_date->format('M j, Y') }}
                                        @if($event->start_time)
                                            <br><span class="text-gray-500">{{ $event->start_time }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $event->venue }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <div class="text-center">
                                            <div class="font-medium">{{ $event->registrations_count }}</div>
                                            @if($event->max_capacity)
                                                <div class="text-xs text-gray-500">
                                                    of {{ $event->max_capacity }}
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <div class="text-center">
                                            <div class="font-medium">{{ $event->check_ins_count }}</div>
                                            @if($event->registrations_count > 0)
                                                <div class="text-xs text-gray-500">
                                                    {{ round(($event->check_ins_count / $event->registrations_count) * 100, 1) }}%
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                   {{ $event->status === 'published' ? 'bg-green-100 text-green-800' : 
                                                      ($event->status === 'draft' ? 'bg-gray-100 text-gray-800' : 
                                                       ($event->status === 'completed' ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800')) }}">
                                            {{ ucfirst($event->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('admin.reports.events', ['event_id' => $event->id]) }}" 
                                               class="text-blue-600 hover:text-blue-900">View Report</a>
                                            
                                            <!-- Export Options -->
                                            <div class="relative inline-block text-left">
                                                <button type="button" 
                                                        onclick="toggleExportMenu({{ $event->id }})"
                                                        class="text-green-600 hover:text-green-900">
                                                    Export
                                                </button>
                                                
                                                <div id="export-menu-{{ $event->id }}" 
                                                     class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 border border-gray-200">
                                                    <div class="py-1">
                                                        <form method="POST" action="{{ route('admin.reports.export-event') }}" class="block">
                                                            @csrf
                                                            <input type="hidden" name="event_id" value="{{ $event->id }}">
                                                            <input type="hidden" name="format" value="csv">
                                                            <button type="submit" 
                                                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                                Export as CSV
                                                            </button>
                                                        </form>
                                                        
                                                        <form method="POST" action="{{ route('admin.reports.export-event') }}" class="block">
                                                            @csrf
                                                            <input type="hidden" name="event_id" value="{{ $event->id }}">
                                                            <input type="hidden" name="format" value="excel">
                                                            <button type="submit" 
                                                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                                Export as Excel
                                                            </button>
                                                        </form>
                                                        
                                                        <form method="POST" action="{{ route('admin.reports.export-event') }}" class="block">
                                                            @csrf
                                                            <input type="hidden" name="event_id" value="{{ $event->id }}">
                                                            <input type="hidden" name="format" value="pdf">
                                                            <button type="submit" 
                                                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                                Export as PDF
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                        No events found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                @if($events->hasPages())
                    <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                        {{ $events->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function toggleExportMenu(eventId) {
            const menu = document.getElementById(`export-menu-${eventId}`);
            const allMenus = document.querySelectorAll('[id^="export-menu-"]');
            
            // Close all other menus
            allMenus.forEach(m => {
                if (m.id !== `export-menu-${eventId}`) {
                    m.classList.add('hidden');
                }
            });
            
            // Toggle current menu
            menu.classList.toggle('hidden');
        }

        // Close export menus when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.closest('[id^="export-menu-"]') && !event.target.closest('button')) {
                const allMenus = document.querySelectorAll('[id^="export-menu-"]');
                allMenus.forEach(menu => menu.classList.add('hidden'));
            }
        });
    </script>
    @endpush
</x-app-layout>
