<x-app-layout>
    <x-slot name="title">Manage Events - {{ config('app.name') }}</x-slot>

    @push('head')
    <style>
        .status-badge { @apply px-2 py-1 text-xs font-medium rounded-full; }
        .status-draft { @apply bg-gray-100 text-gray-800; }
        .status-published { @apply bg-green-100 text-green-800; }
        .status-completed { @apply bg-blue-100 text-blue-800; }
        .status-cancelled { @apply bg-red-100 text-red-800; }
    </style>
    @endpush

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-semibold text-gray-900">Manage Events</h1>
                <a href="{{ route('admin.events.create') }}" 
                   class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Create Event
                </a>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Title, description, venue..."
                               class="w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500">
                            <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>All Statuses</option>
                            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date Filter</label>
                        <select name="date_filter" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500">
                            <option value="">All Dates</option>
                            <option value="upcoming" {{ request('date_filter') === 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                            <option value="past" {{ request('date_filter') === 'past' ? 'selected' : '' }}>Past</option>
                            <option value="today" {{ request('date_filter') === 'today' ? 'selected' : '' }}>Today</option>
                        </select>
                    </div>
                    
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                            Filter
                        </button>
                    </div>
                </form>
            </div>

            <!-- Bulk Actions -->
            <div class="bg-white rounded-lg shadow p-4 mb-6">
                <form method="POST" action="{{ route('admin.events.bulk-action') }}" id="bulkForm">
                    @csrf
                    <div class="flex items-center space-x-4">
                        <select name="action" class="border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500">
                            <option value="">Select Action</option>
                            <option value="publish">Publish Selected</option>
                            <option value="unpublish">Unpublish Selected</option>
                            <option value="delete">Delete Selected</option>
                        </select>
                        <button type="submit" id="bulkSubmit" disabled
                                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed">
                            Apply
                        </button>
                        <span class="text-sm text-gray-500" id="selectedCount">0 events selected</span>
                    </div>
                </form>
            </div>

            <!-- Events Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'title', 'sort_dir' => request('sort_by') === 'title' && request('sort_dir') === 'asc' ? 'desc' : 'asc']) }}" 
                                       class="group inline-flex items-center">
                                        Event
                                        <svg class="ml-2 flex-shrink-0 h-4 w-4 text-gray-400 group-hover:text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </a>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'start_date', 'sort_dir' => request('sort_by') === 'start_date' && request('sort_dir') === 'asc' ? 'desc' : 'asc']) }}" 
                                       class="group inline-flex items-center">
                                        Date
                                        <svg class="ml-2 flex-shrink-0 h-4 w-4 text-gray-400 group-hover:text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </a>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Venue</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registrations</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($events as $event)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" name="event_ids[]" value="{{ $event->id }}" 
                                               class="event-checkbox rounded border-gray-300 text-red-600 focus:ring-red-500">
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            @if($event->featured_image)
                                                <img class="h-10 w-10 rounded-lg object-cover mr-3" 
                                                     src="{{ Storage::url($event->featured_image) }}" alt="{{ $event->title }}">
                                            @else
                                                <div class="h-10 w-10 rounded-lg bg-gray-200 flex items-center justify-center mr-3">
                                                    <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                    </svg>
                                                </div>
                                            @endif
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ $event->title }}</div>
                                                <div class="text-sm text-gray-500">{{ Str::limit($event->description, 50) }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $event->formatted_date_range }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $event->venue }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="status-badge status-{{ $event->status }}">
                                            {{ ucfirst($event->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <div class="text-center">
                                            <div class="font-medium">{{ $event->confirmed_registrations_count }}</div>
                                            @if($event->max_capacity)
                                                <div class="text-xs text-gray-500">of {{ $event->max_capacity }}</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('admin.events.show', $event) }}" 
                                               class="text-blue-600 hover:text-blue-900">View</a>
                                            <a href="{{ route('admin.events.edit', $event) }}" 
                                               class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                            <form method="POST" action="{{ route('admin.events.duplicate', $event) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="text-green-600 hover:text-green-900">Duplicate</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.events.destroy', $event) }}" class="inline" 
                                                  onsubmit="return confirm('Are you sure you want to delete this event?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                        No events found. <a href="{{ route('admin.events.create') }}" class="text-red-600 hover:text-red-900">Create your first event</a>
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
        // Bulk selection
        const selectAll = document.getElementById('selectAll');
        const eventCheckboxes = document.querySelectorAll('.event-checkbox');
        const bulkSubmit = document.getElementById('bulkSubmit');
        const selectedCount = document.getElementById('selectedCount');

        function updateBulkActions() {
            const checked = document.querySelectorAll('.event-checkbox:checked');
            const count = checked.length;
            
            selectedCount.textContent = `${count} event${count !== 1 ? 's' : ''} selected`;
            bulkSubmit.disabled = count === 0;
        }

        selectAll.addEventListener('change', function() {
            eventCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkActions();
        });

        eventCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateBulkActions);
        });

        // Bulk form validation
        document.getElementById('bulkForm').addEventListener('submit', function(e) {
            const action = this.querySelector('[name="action"]').value;
            const checked = document.querySelectorAll('.event-checkbox:checked');
            
            if (!action) {
                e.preventDefault();
                alert('Please select an action');
                return;
            }
            
            if (checked.length === 0) {
                e.preventDefault();
                alert('Please select at least one event');
                return;
            }
            
            if (action === 'delete') {
                if (!confirm(`Are you sure you want to delete ${checked.length} event${checked.length !== 1 ? 's' : ''}?`)) {
                    e.preventDefault();
                    return;
                }
            }
        });
    </script>
    @endpush
</x-app-layout>
