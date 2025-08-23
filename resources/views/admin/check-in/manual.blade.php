<x-app-layout>
    <x-slot name="title">Manual Check-in - {{ config('app.name') }}</x-slot>

    @push('head')
    <style>
        .search-container {
            position: relative;
        }
        
        .search-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #e5e7eb;
            border-top: none;
            border-radius: 0 0 0.5rem 0.5rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            max-height: 400px;
            overflow-y: auto;
            z-index: 50;
        }
        
        .registration-item {
            padding: 1rem;
            border-bottom: 1px solid #f3f4f6;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .registration-item:hover {
            background-color: #f9fafb;
        }
        
        .registration-item:last-child {
            border-bottom: none;
        }
        
        .checked-in {
            background-color: #f0fdf4;
            border-left: 4px solid #22c55e;
        }
        
        .bulk-selection {
            background-color: #eff6ff;
            border-left: 4px solid #3b82f6;
        }
        
        .quick-filter {
            transition: all 0.2s ease;
        }
        
        .quick-filter.active {
            background-color: #3b82f6;
            color: white;
        }
    </style>
    @endpush

    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-4xl font-bold text-gray-900">Manual Check-in</h1>
                    <p class="text-lg text-gray-600 mt-2">Search and check in participants manually</p>
                </div>
                <a href="{{ route('admin.check-in.index') }}" 
                   class="bg-slate-800 hover:bg-slate-900 text-white px-6 py-3 rounded-lg font-semibold transition-colors duration-200 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    QR Scanner
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                <!-- Search Section -->
                <div class="lg:col-span-3">
                    <div class="bg-white rounded-xl shadow-sm p-8">
                        <!-- Search Bar -->
                        <div class="search-container mb-6">
                            <div class="relative">
                                <input type="text" id="search-input" placeholder="Search by name, email, or registration code..."
                                       class="w-full pl-12 pr-4 py-4 text-lg border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <svg class="absolute left-4 top-1/2 transform -translate-y-1/2 w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            
                            <!-- Search Results -->
                            <div id="search-results" class="search-results" style="display: none;"></div>
                        </div>

                        <!-- Quick Filters -->
                        <div class="flex flex-wrap gap-3 mb-6">
                            <span class="text-sm font-medium text-gray-700">Quick filters:</span>
                            <button class="quick-filter px-3 py-1 text-sm border border-gray-300 rounded-full hover:bg-gray-50" data-filter="all">
                                All Events
                            </button>
                            @foreach($activeEvents as $event)
                                <button class="quick-filter px-3 py-1 text-sm border border-gray-300 rounded-full hover:bg-gray-50" data-filter="{{ $event->id }}">
                                    {{ $event->title }}
                                </button>
                            @endforeach
                        </div>

                        <!-- Bulk Actions -->
                        <div id="bulk-actions" class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6" style="display: none;">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <span id="selected-count" class="font-medium text-blue-900">0 selected</span>
                                    <button id="clear-selection" class="ml-4 text-sm text-blue-600 hover:text-blue-800">Clear selection</button>
                                </div>
                                <button id="bulk-checkin" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold transition-colors duration-200">
                                    Check In Selected
                                </button>
                            </div>
                        </div>

                        <!-- Recent Searches -->
                        <div id="recent-searches" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="text-center py-12">
                                <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Search for Participants</h3>
                                <p class="text-gray-600">Enter a name, email, or registration code to find participants for check-in.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Event Stats -->
                    @foreach($activeEvents as $event)
                        <div class="bg-white rounded-xl shadow-sm p-6">
                            <h3 class="font-semibold text-gray-900 mb-2">{{ $event->title }}</h3>
                            <p class="text-sm text-gray-600 mb-4">{{ $event->venue }}</p>
                            
                            <div class="space-y-2">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Registered:</span>
                                    <span class="font-medium">{{ $event->confirmed_registrations_count }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Checked In:</span>
                                    <span class="font-medium text-green-600">{{ $event->check_ins_count }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">Pending:</span>
                                    <span class="font-medium text-orange-600">{{ $event->confirmed_registrations_count - $event->check_ins_count }}</span>
                                </div>
                            </div>

                            @if($event->confirmed_registrations_count > 0)
                                @php
                                    $percentage = round(($event->check_ins_count / $event->confirmed_registrations_count) * 100);
                                @endphp
                                <div class="mt-4">
                                    <div class="flex justify-between text-xs text-gray-600 mb-1">
                                        <span>Check-in Progress</span>
                                        <span>{{ $percentage }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-green-600 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                    </div>
                                </div>
                            @endif

                            <div class="mt-4 flex space-x-2">
                                <button onclick="exportReport('{{ $event->id }}')" 
                                        class="flex-1 text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 px-3 rounded transition-colors">
                                    Export
                                </button>
                                <button onclick="viewStats('{{ $event->id }}')" 
                                        class="flex-1 text-xs bg-blue-100 hover:bg-blue-200 text-blue-700 py-2 px-3 rounded transition-colors">
                                    Stats
                                </button>
                            </div>
                        </div>
                    @endforeach

                    <!-- Quick Actions -->
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="font-semibold text-gray-900 mb-4">Quick Actions</h3>
                        <div class="space-y-3">
                            <button onclick="refreshEvents()" 
                                    class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 py-3 px-4 rounded-lg font-medium transition-colors duration-200 flex items-center justify-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Refresh Stats
                            </button>
                            <a href="{{ route('admin.events.index') }}" 
                               class="w-full block text-center bg-indigo-600 hover:bg-indigo-700 text-white py-3 px-4 rounded-lg font-medium transition-colors duration-200">
                                Manage Events
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Check-in Confirmation Modal -->
    <div id="confirm-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" style="display: none;">
        <div class="bg-white rounded-2xl p-8 max-w-md w-full mx-4">
            <div class="text-center">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Confirm Check-in</h3>
                <div id="confirm-details" class="text-gray-600 mb-6"></div>
                <div class="flex space-x-4">
                    <button id="cancel-checkin" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 py-3 px-4 rounded-lg font-semibold transition-colors duration-200">
                        Cancel
                    </button>
                    <button id="confirm-checkin" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-3 px-4 rounded-lg font-semibold transition-colors duration-200">
                        Check In
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        class ManualCheckIn {
            constructor() {
                this.selectedRegistrations = new Set();
                this.currentFilter = 'all';
                this.searchTimeout = null;
                this.currentRegistration = null;
                this.initializeEventListeners();
            }

            initializeEventListeners() {
                // Search input
                const searchInput = document.getElementById('search-input');
                searchInput.addEventListener('input', (e) => {
                    this.debounceSearch(e.target.value);
                });

                // Quick filters
                document.querySelectorAll('.quick-filter').forEach(button => {
                    button.addEventListener('click', (e) => {
                        this.setFilter(e.target.dataset.filter);
                        this.updateFilterButtons(e.target);
                    });
                });

                // Bulk actions
                document.getElementById('clear-selection').addEventListener('click', () => {
                    this.clearSelection();
                });

                document.getElementById('bulk-checkin').addEventListener('click', () => {
                    this.bulkCheckIn();
                });

                // Modal buttons
                document.getElementById('cancel-checkin').addEventListener('click', () => {
                    this.hideModal();
                });

                document.getElementById('confirm-checkin').addEventListener('click', () => {
                    this.processCheckIn();
                });

                // Close modal on outside click
                document.getElementById('confirm-modal').addEventListener('click', (e) => {
                    if (e.target.id === 'confirm-modal') {
                        this.hideModal();
                    }
                });
            }

            debounceSearch(query) {
                clearTimeout(this.searchTimeout);
                this.searchTimeout = setTimeout(() => {
                    this.search(query);
                }, 300);
            }

            async search(query) {
                if (query.length < 2) {
                    this.hideSearchResults();
                    return;
                }

                try {
                    const params = new URLSearchParams({
                        query: query,
                        ...(this.currentFilter !== 'all' && { event_id: this.currentFilter })
                    });

                    const response = await fetch(`{{ route('admin.check-in.search') }}?${params}`, {
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        }
                    });

                    const result = await response.json();

                    if (result.success) {
                        this.displaySearchResults(result.registrations);
                    }

                } catch (error) {
                    console.error('Search error:', error);
                }
            }

            displaySearchResults(registrations) {
                const resultsContainer = document.getElementById('search-results');
                
                if (registrations.length === 0) {
                    resultsContainer.innerHTML = '<div class="p-4 text-center text-gray-500">No registrations found</div>';
                } else {
                    resultsContainer.innerHTML = registrations.map(reg => this.createRegistrationItem(reg)).join('');
                }

                resultsContainer.style.display = 'block';
            }

            createRegistrationItem(registration) {
                const isCheckedIn = registration.checked_in;
                const isSelected = this.selectedRegistrations.has(registration.id);
                
                return `
                    <div class="registration-item ${isCheckedIn ? 'checked-in' : ''} ${isSelected ? 'bulk-selection' : ''}" 
                         data-registration-id="${registration.id}"
                         data-registration-code="${registration.registration_code}">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                ${!isCheckedIn ? `
                                    <input type="checkbox" class="registration-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500" 
                                           ${isSelected ? 'checked' : ''} 
                                           onchange="checkInManager.toggleSelection(${registration.id})">
                                ` : ''}
                                <div>
                                    <h4 class="font-semibold text-gray-900">${registration.user_name}</h4>
                                    <p class="text-sm text-gray-600">${registration.user_email}</p>
                                    <p class="text-xs text-gray-500">Code: ${registration.registration_code}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-gray-900">${registration.event_title}</p>
                                <p class="text-xs text-gray-500">${registration.event_date}</p>
                                ${isCheckedIn ? `
                                    <div class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 mt-1">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        Checked In
                                    </div>
                                ` : `
                                    <button onclick="checkInManager.showConfirmation(${registration.id})" 
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 hover:bg-blue-200 transition-colors mt-1">
                                        Check In
                                    </button>
                                `}
                            </div>
                        </div>
                    </div>
                `;
            }

            hideSearchResults() {
                document.getElementById('search-results').style.display = 'none';
            }

            setFilter(filter) {
                this.currentFilter = filter;
                const searchInput = document.getElementById('search-input');
                if (searchInput.value.length >= 2) {
                    this.search(searchInput.value);
                }
            }

            updateFilterButtons(activeButton) {
                document.querySelectorAll('.quick-filter').forEach(btn => {
                    btn.classList.remove('active');
                });
                activeButton.classList.add('active');
            }

            toggleSelection(registrationId) {
                if (this.selectedRegistrations.has(registrationId)) {
                    this.selectedRegistrations.delete(registrationId);
                } else {
                    this.selectedRegistrations.add(registrationId);
                }
                this.updateBulkActions();
            }

            clearSelection() {
                this.selectedRegistrations.clear();
                document.querySelectorAll('.registration-checkbox').forEach(cb => {
                    cb.checked = false;
                });
                this.updateBulkActions();
            }

            updateBulkActions() {
                const count = this.selectedRegistrations.size;
                const bulkActions = document.getElementById('bulk-actions');
                const selectedCount = document.getElementById('selected-count');

                if (count > 0) {
                    bulkActions.style.display = 'block';
                    selectedCount.textContent = `${count} selected`;
                } else {
                    bulkActions.style.display = 'none';
                }
            }

            showConfirmation(registrationId) {
                // Find registration data from the current search results
                const registrationItem = document.querySelector(`[data-registration-id="${registrationId}"]`);
                if (registrationItem) {
                    this.currentRegistration = registrationId;
                    this.currentRegistrationCode = registrationItem.getAttribute('data-registration-code');
                    
                    const userName = registrationItem.querySelector('h4').textContent;
                    const eventTitle = registrationItem.querySelector('.text-right .text-sm').textContent;
                    
                    document.getElementById('confirm-details').innerHTML = `
                        <p class="font-medium">${userName}</p>
                        <p class="text-sm">${eventTitle}</p>
                        <p class="text-xs text-gray-500 mt-1">Code: ${this.currentRegistrationCode}</p>
                    `;
                    
                    document.getElementById('confirm-modal').style.display = 'flex';
                }
            }

            hideModal() {
                document.getElementById('confirm-modal').style.display = 'none';
                this.currentRegistration = null;
            }

            async processCheckIn() {
                if (!this.currentRegistrationCode) return;

                try {
                    const response = await fetch('{{ route("admin.check-in.code") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify({ 
                            registration_code: this.currentRegistrationCode 
                        })
                    });

                    const result = await response.json();

                    if (result.success) {
                        this.showSuccess(result.message);
                        this.hideModal();
                        // Refresh search results
                        const searchInput = document.getElementById('search-input');
                        if (searchInput.value.length >= 2) {
                            this.search(searchInput.value);
                        }
                    } else {
                        this.showError(result.message);
                    }

                } catch (error) {
                    console.error('Check-in error:', error);
                    this.showError('Network error. Please try again.');
                }
            }

            async bulkCheckIn() {
                if (this.selectedRegistrations.size === 0) return;

                try {
                    const response = await fetch('{{ route("admin.check-in.bulk") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify({ 
                            registration_ids: Array.from(this.selectedRegistrations)
                        })
                    });

                    const result = await response.json();

                    if (result.success) {
                        this.showSuccess(`Bulk check-in completed: ${result.summary.successful} successful, ${result.summary.failed} failed`);
                        this.clearSelection();
                        // Refresh search results
                        const searchInput = document.getElementById('search-input');
                        if (searchInput.value.length >= 2) {
                            this.search(searchInput.value);
                        }
                    }

                } catch (error) {
                    console.error('Bulk check-in error:', error);
                    this.showError('Network error. Please try again.');
                }
            }

            showSuccess(message) {
                // Use browser notification or toast
                if (typeof window.showToast === 'function') {
                    window.showToast(message, 'success');
                } else {
                    alert(message);
                }
            }

            showError(message) {
                // Use browser notification or toast
                if (typeof window.showToast === 'function') {
                    window.showToast(message, 'error');
                } else {
                    alert(message);
                }
            }
        }

        // Global functions for sidebar actions
        function exportReport(eventId) {
            window.open(`{{ route('admin.events.export', '') }}/${eventId}`, '_blank');
        }

        function viewStats(eventId) {
            // Implement stats modal or redirect
            alert('Stats feature coming soon!');
        }

        function refreshEvents() {
            window.location.reload();
        }

        // Initialize when page loads
        let checkInManager;
        document.addEventListener('DOMContentLoaded', () => {
            checkInManager = new ManualCheckIn();
        });
    </script>
    @endpush
</x-app-layout>
