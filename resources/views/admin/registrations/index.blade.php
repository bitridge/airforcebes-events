@extends('layouts.app')

@section('title', 'Manage Registrations - ' . config('app.name'))

@section('content')
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-semibold text-gray-900">Manage Registrations</h1>
                <div class="flex space-x-3">
                    <button type="button" onclick="openImportModal()" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                        </svg>
                        Import CSV
                    </button>
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
                                            <a href="{{ route('admin.attendees.show', $registration->user) }}" class="text-sm font-medium text-gray-900 hover:text-indigo-600 hover:underline">{{ $registration->user->full_name }}</a>
                                            <div class="text-sm text-gray-500">{{ $registration->user->email }}</div>
                                            @if($registration->user->phone)
                                                <div class="text-sm text-gray-500">{{ $registration->user->phone }}</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <a href="{{ route('admin.events.show', $registration->event) }}" class="text-sm font-medium text-gray-900 hover:text-indigo-600 hover:underline">{{ $registration->event->title }}</a>
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

        // CSV Import functionality
        function openImportModal() {
            document.getElementById('importModal').classList.remove('hidden');
        }

        function closeImportModal() {
            document.getElementById('importModal').classList.add('hidden');
            document.getElementById('csvFile').value = '';
            document.getElementById('eventSelect').value = '';
            document.getElementById('mappingContainer').innerHTML = '';
            document.getElementById('importPreview').innerHTML = '';
        }

        function handleFileSelect() {
            const file = document.getElementById('csvFile').files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function(e) {
                const csv = e.target.result;
                const lines = csv.split('\n');
                const headers = lines[0].split(',').map(h => h.trim().replace(/"/g, ''));
                
                // Show field mapping
                showFieldMapping(headers);
                
                // Show preview
                showPreview(lines.slice(1, 6)); // Show first 5 rows
            };
            reader.readAsText(file);
        }

        function showFieldMapping(headers) {
            const container = document.getElementById('mappingContainer');
            const mappingFields = [
                // Personal Information (Required)
                { key: 'first_name', label: 'First Name', required: true },
                { key: 'last_name', label: 'Last Name', required: true },
                { key: 'email', label: 'Email', required: true },
                { key: 'phone', label: 'Phone', required: false },
                { key: 'organization_name', label: 'Organization Name', required: true },
                { key: 'title', label: 'Job Title', required: false },
                
                // Registration Type
                { key: 'type', label: 'Registration Type', required: false },
                { key: 'checkin_type', label: 'Check-in Type', required: false },
                
                // Business Information
                { key: 'naics_codes', label: 'NAICS Codes', required: false },
                { key: 'industry_connections', label: 'Industry Connections', required: false },
                { key: 'core_specialty_area', label: 'Core Specialty Area', required: false },
                { key: 'contract_vehicles', label: 'Contract Vehicles', required: false },
                
                // Preferences
                { key: 'meeting_preference', label: 'Meeting Preference', required: false },
                
                // Event Specific
                { key: 'small_business_forum', label: 'Small Business Forum', required: false },
                { key: 'small_business_matchmaker', label: 'Small Business Matchmaker', required: false },
                
                // Additional Information
                { key: 'notes', label: 'Notes', required: false }
            ];

            // Add helpful mapping suggestions based on common CSV headers
            const headerSuggestions = {
                'first_name': ['First Name', 'First', 'First Name', 'Given Name'],
                'last_name': ['Last Name', 'Last', 'Surname', 'Family Name'],
                'email': ['Email', 'E-mail', 'Email Address', 'E-mail Address'],
                'phone': ['Phone', 'Phone Number', 'Telephone', 'Mobile', 'Cell'],
                'organization_name': ['Organization Name', 'Organization', 'Company', 'Company Name', 'Employer'],
                'title': ['Job Title', 'Title', 'Position', 'Job Position', 'Role'],
                'type': ['Registration Type', 'Type', 'Reg Type', 'Category'],
                'checkin_type': ['Check-in Type', 'Checkin Type', 'Check In Type', 'Checkin'],
                'naics_codes': ['NAICS Codes', 'NAICS', 'NAICS Code', 'Industry Codes'],
                'industry_connections': ['Industry Connections', 'Industry', 'Connections', 'Industry Type'],
                'core_specialty_area': ['Core Specialty Area', 'Specialty', 'Core Specialty', 'Specialty Area'],
                'contract_vehicles': ['Contract Vehicles', 'Contract Vehicle', 'Vehicles', 'Contract Type'],
                'meeting_preference': ['Meeting Preference', 'Preference', 'Meeting Type', 'Format'],
                'small_business_forum': ['Small Business Forum', 'Business Forum', 'Forum', 'SBF'],
                'small_business_matchmaker': ['Small Business Matchmaker', 'Business Matchmaker', 'Matchmaker', 'SBM'],
                'notes': ['Notes', 'Additional Notes', 'Comments', 'Remarks', 'Description']
            };

            let html = '<div class="grid grid-cols-3 gap-4 mb-4">';
            mappingFields.forEach(field => {
                html += `
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">
                            ${field.label} ${field.required ? '<span class="text-red-500">*</span>' : ''}
                        </label>
                        <select name="mapping[${field.key}]" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">-- Select CSV Column --</option>
                            ${headers.map((header, index) => `<option value="${index}">${header}</option>`).join('')}
                        </select>
                    </div>
                `;
            });
            html += '</div>';
            
            container.innerHTML = html;
        }

        function showPreview(lines) {
            const container = document.getElementById('importPreview');
            if (lines.length === 0) {
                container.innerHTML = '<p class="text-gray-500">No data to preview</p>';
                return;
            }

            let html = '<div class="bg-gray-50 rounded-lg p-4">';
            html += '<h4 class="font-medium text-gray-900 mb-2">CSV Preview (First 5 rows):</h4>';
            html += '<div class="overflow-x-auto">';
            html += '<table class="min-w-full text-sm">';
            
            // Headers
            const headers = lines[0].split(',').map(h => h.trim().replace(/"/g, ''));
            html += '<thead><tr>';
            headers.forEach(header => {
                html += `<th class="px-3 py-2 text-left font-medium text-gray-700 bg-white">${header}</th>`;
            });
            html += '</tr></thead>';
            
            // Rows
            html += '<tbody>';
            lines.slice(1).forEach((line, index) => {
                if (index >= 5) return; // Only show first 5 rows
                const cells = line.split(',').map(c => c.trim().replace(/"/g, ''));
                html += '<tr>';
                cells.forEach(cell => {
                    html += `<td class="px-3 py-2 text-gray-600 bg-white">${cell}</td>`;
                });
                html += '</tr>';
            });
            html += '</tbody></table></div></div>';
            
            container.innerHTML = html;
        }

        function validateImport() {
            const eventId = document.getElementById('eventSelect').value;
            const file = document.getElementById('csvFile').files[0];
            
            if (!eventId) {
                alert('Please select an event for the registrations.');
                return false;
            }
            
            if (!file) {
                alert('Please select a CSV file.');
                return false;
            }

            // Check required field mappings
            const requiredFields = ['first_name', 'last_name', 'email', 'organization_name'];
            for (const field of requiredFields) {
                const mapping = document.querySelector(`select[name="mapping[${field}]"]`).value;
                if (!mapping && mapping !== '0') {
                    alert(`Please map the ${field.replace('_', ' ')} field.`);
                    return false;
                }
            }

            return true;
        }

        function downloadTemplate() {
            const csvContent = [
                'First Name,Last Name,Email,Phone,Organization Name,Job Title,Registration Type,Check-in Type,NAICS Codes,Industry Connections,Core Specialty Area,Contract Vehicles,Meeting Preference,Small Business Forum,Small Business Matchmaker,Notes',
                'John,Doe,john.doe@example.com,+1234567890,ABC Company,Software Engineer,registration,standard,541511,Technology,Software Development,IDIQ,no_preference,true,false,First time attendee',
                'Jane,Smith,jane.smith@example.com,+1234567892,XYZ Corp,Project Manager,registration,standard,541512,Consulting,Project Management,GWAC,prefer_afternoon,false,true,',
                'Bob,Johnson,bob.johnson@example.com,+1234567894,123 Industries,Business Analyst,registration,standard,541519,Manufacturing,Analysis,IDIQ,prefer_morning,true,true,Looking forward to the event'
            ].join('\n');
            
            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'registration_template.csv';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        }

        // Handle import form submission
        document.getElementById('importForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            if (!validateImport()) {
                return;
            }

            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            
            // Show loading state
            submitBtn.disabled = true;
            submitBtn.textContent = 'Importing...';
            
            try {
                const response = await fetch(this.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: formData
                });

                const result = await response.json();
                
                if (result.success) {
                    // Show success message
                    alert(result.message);
                    
                    // Close modal and refresh page
                    closeImportModal();
                    location.reload();
                } else {
                    // Show error message
                    alert('Import failed: ' + result.message);
                }
                
            } catch (error) {
                console.error('Import error:', error);
                alert('Import failed. Please try again.');
            } finally {
                // Reset button state
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        });
    </script>

    <!-- CSV Import Modal -->
    <div id="importModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Import Registrations from CSV</h3>
                    <button onclick="closeImportModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form id="importForm" action="{{ route('admin.registrations.import-csv') }}" method="POST" enctype="multipart/form-data" onsubmit="return validateImport()">
                    @csrf
                    
                    <!-- Event Selection -->
                    <div class="mb-6">
                        <label for="eventSelect" class="block text-sm font-medium text-gray-700 mb-2">
                            Select Event <span class="text-red-500">*</span>
                        </label>
                        <select id="eventSelect" name="event_id" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">-- Select Event --</option>
                            @foreach($events as $event)
                                <option value="{{ $event->id }}">{{ $event->title }} ({{ $event->start_date->format('M j, Y') }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- File Upload -->
                    <div class="mb-6">
                        <div class="flex justify-between items-center mb-2">
                            <label for="csvFile" class="block text-sm font-medium text-gray-700">
                                CSV File <span class="text-red-500">*</span>
                            </label>
                            <button type="button" onclick="downloadTemplate()" class="text-sm text-blue-600 hover:text-blue-800 underline">
                                Download CSV Template
                            </button>
                        </div>
                        <input type="file" id="csvFile" name="csv_file" accept=".csv" required 
                               onchange="handleFileSelect()" 
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <p class="text-sm text-gray-500 mt-1">
                            Upload a CSV file with registration data. The first row should contain column headers.
                        </p>
                    </div>

                    <!-- Field Mapping -->
                    <div id="mappingContainer" class="mb-6"></div>

                    <!-- Preview -->
                    <div id="importPreview" class="mb-6"></div>

                    <!-- Import Options -->
                    <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                        <h4 class="font-medium text-blue-900 mb-2">Import Options</h4>
                        <div class="space-y-2 text-sm text-blue-800">
                            <p>• All imported registrations will be automatically confirmed</p>
                            <p>• Registration codes will be automatically generated</p>
                            <p>• QR codes will be automatically generated</p>
                            <p>• Duplicate email addresses for the same event will be skipped</p>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeImportModal()" 
                                class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Import Registrations
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
