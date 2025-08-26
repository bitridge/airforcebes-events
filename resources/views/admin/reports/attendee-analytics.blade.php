@extends('layouts.app')

@section('title', 'Attendee Analytics - ' . config('app.name'))

@section('content')
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Attendee Analytics</h1>
                    <p class="text-gray-600">Comprehensive insights into attendee behavior and patterns</p>
                </div>
                <div class="flex space-x-3">
                    <a href="{{ route('admin.reports.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Reports
                    </a>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white shadow rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <div class="text-2xl font-bold text-gray-900">{{ $totalAttendees }}</div>
                            <div class="text-sm text-gray-600">Total Attendees</div>
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <div class="text-2xl font-bold text-gray-900">{{ $activeAttendees }}</div>
                            <div class="text-sm text-gray-600">Active Attendees</div>
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <div class="text-2xl font-bold text-gray-900">{{ number_format($averageEventsPerAttendee, 1) }}</div>
                            <div class="text-sm text-gray-600">Avg. Events/Attendee</div>
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <div class="text-2xl font-bold text-gray-900">{{ number_format($averageCheckInRate, 1) }}%</div>
                            <div class="text-sm text-gray-600">Avg. Check-in Rate</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white shadow rounded-lg mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Filters</h3>
                </div>
                <div class="px-6 py-4">
                    <form method="GET" action="{{ route('admin.reports.attendee-analytics') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label for="date_range" class="block text-sm font-medium text-gray-700">Date Range</label>
                            <select id="date_range" name="date_range" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="all" {{ request('date_range') === 'all' ? 'selected' : '' }}>All Time</option>
                                <option value="this_month" {{ request('date_range') === 'this_month' ? 'selected' : '' }}>This Month</option>
                                <option value="last_month" {{ request('date_range') === 'last_month' ? 'selected' : '' }}>Last Month</option>
                                <option value="this_year" {{ request('date_range') === 'this_year' ? 'selected' : '' }}>This Year</option>
                                <option value="last_year" {{ request('date_range') === 'last_year' ? 'selected' : '' }}>Last Year</option>
                            </select>
                        </div>
                        <div>
                            <label for="activity_level" class="block text-sm font-medium text-gray-700">Activity Level</label>
                            <select id="activity_level" name="activity_level" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="">All Levels</option>
                                <option value="high" {{ request('activity_level') === 'high' ? 'selected' : '' }}>High Activity (5+ events)</option>
                                <option value="medium" {{ request('activity_level') === 'medium' ? 'selected' : '' }}>Medium Activity (2-4 events)</option>
                                <option value="low" {{ request('activity_level') === 'low' ? 'selected' : '' }}>Low Activity (1 event)</option>
                            </select>
                        </div>
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700">Search Attendees</label>
                            <input type="text" id="search" name="search" value="{{ request('search') }}" 
                                   placeholder="Name or email"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                                Apply Filters
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Top Attendees -->
            <div class="bg-white shadow rounded-lg mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Top Attendees</h3>
                </div>
                <div class="px-6 py-4">
                    @if($topAttendees->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rank</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attendee</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Events Attended</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check-in Rate</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Activity</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($topAttendees as $index => $attendee)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <div class="flex items-center">
                                                    @if($index < 3)
                                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full 
                                                            @if($index === 0) bg-yellow-100 text-yellow-800
                                                            @elseif($index === 1) bg-gray-100 text-gray-800
                                                            @else bg-orange-100 text-orange-800
                                                            @endif font-bold">
                                                            {{ $index + 1 }}
                                                        </span>
                                                    @else
                                                        <span class="text-gray-500 font-medium">{{ $index + 1 }}</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $attendee->full_name }}</div>
                                                <div class="text-sm text-gray-500">{{ $attendee->email }}</div>
                                                @if($attendee->phone)
                                                    <div class="text-sm text-gray-500">{{ $attendee->phone }}</div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <div class="text-center">
                                                    <div class="font-medium text-blue-600">{{ $attendee->registrations_count }}</div>
                                                    <div class="text-xs text-gray-500">events</div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                @if($attendee->registrations_count > 0)
                                                    @php
                                                        $checkInRate = ($attendee->check_ins_count / $attendee->registrations_count) * 100;
                                                    @endphp
                                                    <div class="text-center">
                                                        <div class="font-medium text-green-600">{{ number_format($checkInRate, 1) }}%</div>
                                                        <div class="text-xs text-gray-500">{{ $attendee->check_ins_count }}/{{ $attendee->registrations_count }}</div>
                                                    </div>
                                                @else
                                                    <span class="text-gray-400">N/A</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                @if($attendee->last_activity)
                                                    <div>{{ $attendee->last_activity->format('M d, Y') }}</div>
                                                    <div class="text-xs text-gray-500">{{ $attendee->last_activity->diffForHumans() }}</div>
                                                @else
                                                    <span class="text-gray-400">Never</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="{{ route('admin.attendees.show', $attendee) }}" class="text-indigo-600 hover:text-indigo-900">View Profile</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No attendees found</h3>
                            <p class="mt-1 text-sm text-gray-500">No attendees match your current filters.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- All Attendees Table -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900">All Attendees ({{ $attendees->total() }})</h3>
                    <div class="flex space-x-3">
                        <button onclick="exportReport()" class="inline-flex items-center px-3 py-2 bg-green-600 text-white text-sm rounded-md hover:bg-green-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Export Report
                        </button>
                    </div>
                </div>
                <div class="px-6 py-4">
                    @if($attendees->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attendee</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Events Attended</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check-in Rate</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Activity</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Member Since</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($attendees as $attendee)
                                        <tr>
                                            <td class="px-6 py-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $attendee->full_name }}</div>
                                                <div class="text-sm text-gray-500">{{ $attendee->email }}</div>
                                                @if($attendee->phone)
                                                    <div class="text-sm text-gray-500">{{ $attendee->phone }}</div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <div class="text-center">
                                                    <div class="font-medium text-blue-600">{{ $attendee->registrations_count }}</div>
                                                    <div class="text-xs text-gray-500">events</div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                @if($attendee->registrations_count > 0)
                                                    @php
                                                        $checkInRate = ($attendee->check_ins_count / $attendee->registrations_count) * 100;
                                                    @endphp
                                                    <div class="text-center">
                                                        <div class="font-medium text-green-600">{{ number_format($checkInRate, 1) }}%</div>
                                                        <div class="text-xs text-gray-500">{{ $attendee->check_ins_count }}/{{ $attendee->registrations_count }}</div>
                                                    </div>
                                                @else
                                                    <span class="text-gray-400">N/A</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                @if($attendee->last_activity)
                                                    <div>{{ $attendee->last_activity->format('M d, Y') }}</div>
                                                    <div class="text-xs text-gray-500">{{ $attendee->last_activity->diffForHumans() }}</div>
                                                @else
                                                    <span class="text-gray-400">Never</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <div>{{ $attendee->created_at->format('M d, Y') }}</div>
                                                <div class="text-xs text-gray-500">{{ $attendee->created_at->diffForHumans() }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex space-x-2">
                                                    <a href="{{ route('admin.attendees.show', $attendee) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                                    <a href="{{ route('admin.attendees.edit', $attendee) }}" class="text-green-600 hover:text-green-900">Edit</a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        @if($attendees->hasPages())
                            <div class="mt-4">
                                {{ $attendees->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No attendees found</h3>
                            <p class="mt-1 text-sm text-gray-500">No attendees match your current filters.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        function exportReport() {
            const currentUrl = new URL(window.location);
            const params = new URLSearchParams(currentUrl.search);
            
            // Add export parameter
            params.set('export', 'csv');
            
            // Create export URL
            const exportUrl = currentUrl.pathname + '?' + params.toString();
            
            // Trigger download
            window.location.href = exportUrl;
        }
    </script>
@endsection
