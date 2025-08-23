<x-app-layout>
    <x-slot name="title">Admin Dashboard - {{ config('app.name') }}</x-slot>

    @push('head')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <style>
        .stat-card { background: white; border-radius: 0.75rem; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
        .widget { background: white; border-radius: 0.75rem; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
        .scroll-area { max-height: 22rem; overflow-y: auto; }
    </style>
    @endpush

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            <!-- Quick Actions -->
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.events.create') }}" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">Create Event</a>
                <a href="{{ route('admin.check-in.index') }}" class="inline-flex items-center px-4 py-2 bg-slate-800 text-white rounded-md hover:bg-slate-900">Open Check-in</a>
                <a href="{{ route('admin.registrations.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-700 text-white rounded-md hover:bg-gray-800">View Registrations</a>
                <a href="{{ route('admin.events.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-800 rounded-md hover:bg-gray-200">Manage Events</a>
            </div>

            <!-- Metrics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="stat-card p-6">
                    <div class="text-sm text-gray-500">Total Events</div>
                    <div class="mt-2 text-3xl font-semibold">{{ $metrics['total_events'] }}</div>
                </div>
                <div class="stat-card p-6">
                    <div class="text-sm text-gray-500">Registrations</div>
                    <div class="mt-2 text-3xl font-semibold">{{ $metrics['total_registrations'] }}</div>
                </div>
                <div class="stat-card p-6">
                    <div class="text-sm text-gray-500">Check-ins Today</div>
                    <div class="mt-2 text-3xl font-semibold">{{ $metrics['checkins_today'] }}</div>
                </div>
                <div class="stat-card p-6">
                    <div class="text-sm text-gray-500">Upcoming Events</div>
                    <div class="mt-2 text-3xl font-semibold">{{ $metrics['upcoming_events'] }}</div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="widget p-6 lg:col-span-2">
                    <h3 class="font-semibold text-gray-900 mb-4">Registration Trends (14 days)</h3>
                    <canvas id="registrationsChart" height="110"></canvas>
                </div>
                <div class="widget p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">Check-in Rate by Event</h3>
                    <canvas id="checkinsChart" height="110"></canvas>
                </div>
            </div>

            <!-- Capacity & Popular -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="widget p-6 lg:col-span-2">
                    <h3 class="font-semibold text-gray-900 mb-4">Capacity Utilization</h3>
                    <canvas id="capacityChart" height="110"></canvas>
                </div>
                <div class="widget p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">Top Events (by registrations)</h3>
                    <canvas id="popularChart" height="110"></canvas>
                </div>
            </div>

            <!-- Activity & Upcoming -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="widget p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">Recent Activity</h3>
                    <div class="scroll-area space-y-3">
                        @foreach($recentRegistrations as $r)
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $r->user->name }}</div>
                                    <div class="text-xs text-gray-500">Registered for {{ $r->event->title }}</div>
                                </div>
                                <div class="text-xs text-gray-400">{{ $r->created_at->diffForHumans() }}</div>
                            </div>
                        @endforeach
                        @foreach($recentCheckIns as $c)
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $c->registration->user->name }}</div>
                                    <div class="text-xs text-gray-500">Checked in to {{ $c->registration->event->title }}</div>
                                </div>
                                <div class="text-xs text-gray-400">{{ $c->checked_in_at->diffForHumans() }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="widget p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">Upcoming Events</h3>
                    <div class="space-y-3 scroll-area">
                        @forelse($upcomingEvents as $e)
                            <div class="p-3 rounded border border-gray-100">
                                <div class="text-sm font-medium text-gray-900">{{ $e->title }}</div>
                                <div class="text-xs text-gray-500">{{ $e->formatted_date_range }} • {{ $e->venue }}</div>
                                <div class="mt-1 flex justify-between text-xs">
                                    <span class="text-gray-500">Registered</span>
                                    <span class="font-medium">{{ $e->confirmed_registrations_count }}</span>
                                </div>
                                <div class="mt-1 flex justify-between text-xs">
                                    <span class="text-gray-500">Checked-in</span>
                                    <span class="font-medium">{{ $e->check_ins_count }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="text-sm text-gray-500">No upcoming events</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        const chartsTheme = {
            grid: 'rgba(148,163,184,0.15)',
            text: '#334155',
            primary: '#dc2626',
            secondary: '#0ea5e9',
            accent: '#16a34a',
        };

        // Registration trends
        new Chart(document.getElementById('registrationsChart'), {
            type: 'line',
            data: {
                labels: @json($registrationTrends['labels']),
                datasets: [{
                    label: 'Registrations',
                    data: @json($registrationTrends['values']),
                    borderColor: chartsTheme.primary,
                    backgroundColor: 'rgba(220,38,38,0.1)',
                    tension: 0.35,
                    fill: true,
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: { grid: { color: chartsTheme.grid } },
                    y: { grid: { color: chartsTheme.grid }, beginAtZero: true }
                },
                plugins: { legend: { display: false } }
            }
        });

        // Check-in rates
        new Chart(document.getElementById('checkinsChart'), {
            type: 'bar',
            data: {
                labels: @json($checkInRates['labels']),
                datasets: [{
                    label: 'Check-in %',
                    data: @json($checkInRates['values']),
                    backgroundColor: chartsTheme.accent,
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: { grid: { display: false } },
                    y: { grid: { color: chartsTheme.grid }, beginAtZero: true, max: 100 }
                },
                plugins: { legend: { display: false } }
            }
        });

        // Capacity utilization
        new Chart(document.getElementById('capacityChart'), {
            type: 'bar',
            data: {
                labels: @json($capacityUtilization['labels']),
                datasets: [{
                    label: 'Utilization %',
                    data: @json($capacityUtilization['values']),
                    backgroundColor: chartsTheme.secondary,
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: { grid: { display: false } },
                    y: { grid: { color: chartsTheme.grid }, beginAtZero: true, max: 100 }
                },
                plugins: { legend: { display: false } }
            }
        });

        // Popular events
        new Chart(document.getElementById('popularChart'), {
            type: 'doughnut',
            data: {
                labels: @json($popularEvents['labels']),
                datasets: [{
                    data: @json($popularEvents['values']),
                    backgroundColor: [chartsTheme.primary, chartsTheme.secondary, '#f59e0b', '#8b5cf6', '#10b981'],
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    </script>
    @endpush
</x-app-layout>
