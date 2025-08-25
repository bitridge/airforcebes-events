@extends('layouts.app')

@section('title', 'Manage Registrations - ' . config('app.name'))

@section('content')
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-semibold text-gray-900">Manage Registrations</h1>
                <div class="flex space-x-3">
                    <a href="{{ route('admin.registrations.export') }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Export
                    </a>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <form method="GET" action="{{ route('admin.registrations.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <input type="text" id="search" name="search" value="{{ request('search') }}" 
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                               placeholder="Name, email, or code...">
                    </div>
                    <div>
                        <label for="event_id" class="block text-sm font-medium text-gray-700 mb-1">Event</label>
                        <select id="event_id" name="event_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">All Events</option>
                            @foreach($events as $event)
                                <option value="{{ $event->id }}" {{ request('event_id') == $event->id ? 'selected' : '' }}>
                                    {{ $event->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select id="status" name="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div>
                        <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                        <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                            Filter
                        </button>
                    </div>
                </form>
            </div>

            <!-- Bulk Actions -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <form method="POST" action="{{ route('admin.registrations.bulk-action') }}" id="bulkActionForm">
                    @csrf
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center space-x-2">
                            <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <label for="selectAll" class="text-sm font-medium text-gray-700">Select All</label>
                        </div>
                        <select name="action" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Choose Action</option>
                            <option value="confirm">Approve Selected</option>
                            <option value="cancel">Cancel Selected</option>
                            <option value="delete">Delete Selected</option>
                        </select>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50" id="bulkActionBtn" disabled>
                            Apply Action
                        </button>
                    </div>
                </form>
            </div>

            <!-- Registrations Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <input type="checkbox" id="headerCheckbox" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attendee</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registration Code</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($registrations as $registration)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" name="registration_ids[]" value="{{ $registration->id }}" class="row-checkbox rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $registration->user->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $registration->user->email }}</div>
                                            @if($registration->user->phone)
                                                <div class="text-sm text-gray-500">{{ $registration->user->phone }}</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $registration->event->title }}</div>
                                            <div class="text-sm text-gray-500">{{ $registration->event->formatted_date_range }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-mono text-gray-900">{{ $registration->registration_code }}</div>
                                        <div class="text-xs text-gray-500">QR: {{ Str::limit($registration->qr_code_data, 20) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($registration->status === 'confirmed') bg-green-100 text-green-800
                                            @elseif($registration->status === 'pending') bg-yellow-100 text-yellow-800
                                            @else bg-red-100 text-red-800
                                            @endif">
                                            {{ ucfirst($registration->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $registration->created_at->format('M j, Y g:i A') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('admin.registrations.show', $registration) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                            <a href="{{ route('admin.registrations.edit', $registration) }}" class="text-green-600 hover:text-green-900">Edit</a>
                                            <a href="{{ route('admin.registrations.qr-view', $registration) }}" class="text-blue-600 hover:text-blue-900">QR Code</a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                        <p class="text-lg font-medium">No registrations found</p>
                                        <p class="text-sm">Try adjusting your search filters.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                @if($registrations->hasPages())
                    <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                        {{ $registrations->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        // Bulk actions functionality
        document.addEventListener('DOMContentLoaded', function() {
            const selectAllCheckbox = document.getElementById('selectAll');
            const headerCheckbox = document.getElementById('headerCheckbox');
            const rowCheckboxes = document.querySelectorAll('.row-checkbox');
            const bulkActionBtn = document.getElementById('bulkActionBtn');
            const actionSelect = document.querySelector('select[name="action"]');

            // Header checkbox controls all row checkboxes
            headerCheckbox.addEventListener('change', function() {
                rowCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateBulkActionButton();
            });

            // Row checkboxes update header checkbox
            rowCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    updateHeaderCheckbox();
                    updateBulkActionButton();
                });
            });

            // Action select updates button state
            actionSelect.addEventListener('change', updateBulkActionButton);

            function updateHeaderCheckbox() {
                const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
                const totalCount = rowCheckboxes.length;
                
                if (checkedCount === 0) {
                    headerCheckbox.checked = false;
                    headerCheckbox.indeterminate = false;
                } else if (checkedCount === totalCount) {
                    headerCheckbox.checked = true;
                    headerCheckbox.indeterminate = false;
                } else {
                    headerCheckbox.checked = false;
                    headerCheckbox.indeterminate = true;
                }
            }

            function updateBulkActionButton() {
                const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
                const actionSelected = actionSelect.value !== '';
                
                bulkActionBtn.disabled = checkedCount === 0 || !actionSelected;
            }

            // Form submission confirmation
            document.getElementById('bulkActionForm').addEventListener('submit', function(e) {
                const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
                const action = actionSelect.value;
                
                if (checkedCount === 0) {
                    e.preventDefault();
                    alert('Please select at least one registration.');
                    return;
                }

                let message = `Are you sure you want to ${action} ${checkedCount} registration(s)?`;
                
                if (action === 'delete') {
                    message += '\n\nThis action cannot be undone!';
                }
                
                if (!confirm(message)) {
                    e.preventDefault();
                }
            });
        });
    </script>
@endsection
