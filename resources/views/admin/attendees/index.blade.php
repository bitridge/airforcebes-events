<x-app-layout>
    <x-slot name="title">Manage Attendees - {{ config('app.name') }}</x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-semibold text-gray-900">Manage Attendees</h1>
                <div class="flex space-x-3">
                    <button onclick="openBulkCommunicationModal()" 
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        Bulk Communication
                    </button>
                    
                    <form method="POST" action="{{ route('admin.attendees.export-csv') }}" class="inline">
                        @csrf
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Export CSV
                        </button>
                    </form>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Name, email, phone, organization..."
                               class="w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                        <select name="role" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500">
                            <option value="all">All Roles</option>
                            <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="attendee" {{ request('role') === 'attendee' ? 'selected' : '' }}>Attendee</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Registration Count</label>
                        <select name="registration_count" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500">
                            <option value="">All</option>
                            <option value="1" {{ request('registration_count') === '1' ? 'selected' : '' }}>1 Event</option>
                            <option value="2-5" {{ request('registration_count') === '2-5' ? 'selected' : '' }}>2-5 Events</option>
                            <option value="6+" {{ request('registration_count') === '6+' ? 'selected' : '' }}>6+ Events</option>
                        </select>
                    </div>
                    
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                            Filter
                        </button>
                    </div>
                </form>
            </div>

            <!-- Attendees Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'name', 'sort_dir' => request('sort_by') === 'name' && request('sort_dir') === 'asc' ? 'desc' : 'asc']) }}" 
                                       class="group inline-flex items-center">
                                        Attendee
                                        <svg class="ml-2 flex-shrink-0 h-4 w-4 text-gray-400 group-hover:text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </a>
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statistics</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($attendees as $attendee)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" name="attendee_ids[]" value="{{ $attendee->id }}" 
                                               class="attendee-checkbox rounded border-gray-300 text-red-600 focus:ring-red-500">
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                    <span class="text-sm font-medium text-gray-700">
                                                        {{ strtoupper(substr($attendee->name, 0, 2)) }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $attendee->name }}</div>
                                                <div class="text-sm text-gray-500">Member since {{ $attendee->created_at->format('M Y') }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div>
                                            <div class="text-sm text-gray-900">{{ $attendee->email }}</div>
                                            @if($attendee->phone)
                                                <div class="text-sm text-gray-500">{{ $attendee->phone }}</div>
                                            @endif
                                            @if($attendee->organization)
                                                <div class="text-sm text-gray-500">{{ $attendee->organization }}</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                   {{ $attendee->role === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                                            {{ ucfirst($attendee->role) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <div class="text-center">
                                            <div class="font-medium">{{ $attendee->registrations_count }}</div>
                                            <div class="text-xs text-gray-500">Registrations</div>
                                        </div>
                                        <div class="text-center mt-1">
                                            <div class="font-medium">{{ $attendee->check_ins_count }}</div>
                                            <div class="text-xs text-gray-500">Check-ins</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                   {{ $attendee->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $attendee->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('admin.attendees.show', $attendee) }}" 
                                               class="text-blue-600 hover:text-blue-900">View</a>
                                            <a href="{{ route('admin.attendees.edit', $attendee) }}" 
                                               class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                            <button onclick="openCommunicationModal({{ $attendee->id }}, '{{ $attendee->name }}')" 
                                                    class="text-green-600 hover:text-green-900">Send Message</button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                        No attendees found matching your criteria.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                @if($attendees->hasPages())
                    <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                        {{ $attendees->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Individual Communication Modal -->
    <div id="communicationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Send Message to <span id="attendeeName"></span></h3>
                <form method="POST" id="communicationForm">
                    @csrf
                    <input type="hidden" name="attendee_id" id="attendeeId">
                    
                    <div class="mb-4">
                        <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Subject *</label>
                        <input type="text" id="subject" name="subject" required
                               class="w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500">
                    </div>
                    
                    <div class="mb-4">
                        <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Message *</label>
                        <textarea id="message" name="message" rows="4" required
                                  class="w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500"></textarea>
                    </div>
                    
                    <div class="mb-4">
                        <label for="communication_type" class="block text-sm font-medium text-gray-700 mb-1">Type *</label>
                        <select id="communication_type" name="communication_type" required
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500">
                            <option value="email">Email</option>
                            <option value="sms">SMS</option>
                            <option value="announcement">Announcement</option>
                        </select>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeCommunicationModal()" 
                                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                            Send Message
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bulk Communication Modal -->
    <div id="bulkCommunicationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Send Bulk Message</h3>
                <form method="POST" action="{{ route('admin.attendees.bulk-communication') }}" id="bulkCommunicationForm">
                    @csrf
                    <input type="hidden" name="attendee_ids" id="bulkAttendeeIds">
                    
                    <div class="mb-4">
                        <label for="bulk_subject" class="block text-sm font-medium text-gray-700 mb-1">Subject *</label>
                        <input type="text" id="bulk_subject" name="subject" required
                               class="w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500">
                    </div>
                    
                    <div class="mb-4">
                        <label for="bulk_message" class="block text-sm font-medium text-gray-700 mb-1">Message *</label>
                        <textarea id="bulk_message" name="message" rows="4" required
                                  class="w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500"></textarea>
                    </div>
                    
                    <div class="mb-4">
                        <label for="bulk_communication_type" class="block text-sm font-medium text-gray-700 mb-1">Type *</label>
                        <select id="bulk_communication_type" name="communication_type" required
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500">
                            <option value="email">Email</option>
                            <option value="sms">SMS</option>
                            <option value="announcement">Announcement</option>
                        </select>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeBulkCommunicationModal()" 
                                class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                            Send to Selected
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Individual communication modal
        function openCommunicationModal(attendeeId, attendeeName) {
            document.getElementById('attendeeId').value = attendeeId;
            document.getElementById('attendeeName').textContent = attendeeName;
            document.getElementById('communicationForm').action = `/admin/attendees/${attendeeId}/communication`;
            document.getElementById('communicationModal').classList.remove('hidden');
        }

        function closeCommunicationModal() {
            document.getElementById('communicationModal').classList.add('hidden');
        }

        // Bulk communication modal
        function openBulkCommunicationModal() {
            const checked = document.querySelectorAll('.attendee-checkbox:checked');
            if (checked.length === 0) {
                alert('Please select at least one attendee');
                return;
            }
            
            const attendeeIds = Array.from(checked).map(cb => cb.value);
            document.getElementById('bulkAttendeeIds').value = JSON.stringify(attendeeIds);
            document.getElementById('bulkCommunicationModal').classList.remove('hidden');
        }

        function closeBulkCommunicationModal() {
            document.getElementById('bulkCommunicationModal').classList.add('hidden');
        }

        // Bulk selection
        const selectAll = document.getElementById('selectAll');
        const attendeeCheckboxes = document.querySelectorAll('.attendee-checkbox');

        selectAll.addEventListener('change', function() {
            attendeeCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        attendeeCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                if (!this.checked) {
                    selectAll.checked = false;
                } else {
                    const allChecked = Array.from(attendeeCheckboxes).every(cb => cb.checked);
                    selectAll.checked = allChecked;
                }
            });
        });

        // Close modals when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target.classList.contains('fixed')) {
                event.target.classList.add('hidden');
            }
        });
    </script>
    @endpush
</x-app-layout>
