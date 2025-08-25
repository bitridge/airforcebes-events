@extends('layouts.app')

@section('title', 'Admin Dashboard - ' . config('app.name'))

@push('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<style>
    .stat-card { background: white; border-radius: 0.75rem; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
    .widget { background: white; border-radius: 0.75rem; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
    .scroll-area { max-height: 22rem; overflow-y: auto; }
    
    /* Chart container styles */
    .chart-container {
        position: relative;
        height: 300px;
        width: 100%;
    }
    
    .chart-container-large {
        position: relative;
        height: 350px;
        width: 100%;
    }
    
    .chart-container-small {
        position: relative;
        height: 250px;
        width: 100%;
    }
    
    /* Responsive chart adjustments */
    @media (max-width: 1024px) {
        .chart-container { height: 250px; }
        .chart-container-large { height: 300px; }
        .chart-container-small { height: 200px; }
    }
    
    @media (max-width: 768px) {
        .chart-container { height: 200px; }
        .chart-container-large { height: 250px; }
        .chart-container-small { height: 180px; }
    }
</style>
@endpush

@section('content')
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
                    <div class="chart-container-large">
                        <canvas id="registrationsChart"></canvas>
                    </div>
                </div>
                <div class="widget p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">Check-in Rate by Event</h3>
                    <div class="chart-container">
                        <canvas id="checkinsChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Capacity & Popular -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="widget p-6 lg:col-span-2">
                    <h3 class="font-semibold text-gray-900 mb-4">Capacity Utilization</h3>
                    <div class="chart-container-large">
                        <canvas id="capacityChart"></canvas>
                    </div>
                </div>
                <div class="widget p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">Top Events (by registrations)</h3>
                    <div class="chart-container">
                        <canvas id="popularChart"></canvas>
                    </div>
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
                                    <span class="font-medium">{{ $e->confirmedRegistrations->count() }}/{{ $e->max_capacity ?: '∞' }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-gray-500 py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <p class="text-sm">No upcoming events</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Registration Trends Chart
        const registrationsCtx = document.getElementById('registrationsChart').getContext('2d');
        new Chart(registrationsCtx, {
            type: 'line',
            data: {
                labels: @json($chartData['labels']),
                datasets: [{
                    label: 'Registrations',
                    data: @json($chartData['registrations']),
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    borderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { 
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });

        // Check-ins Chart
        const checkinsCtx = document.getElementById('checkinsChart').getContext('2d');
        new Chart(checkinsCtx, {
            type: 'doughnut',
            data: {
                labels: @json($chartData['event_names']),
                datasets: [{
                    data: @json($chartData['checkin_rates']),
                    backgroundColor: [
                        'rgb(59, 130, 246)',
                        'rgb(16, 185, 129)',
                        'rgb(245, 158, 11)',
                        'rgb(239, 68, 68)',
                        'rgb(139, 92, 246)'
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { 
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    }
                },
                cutout: '60%'
            }
        });

        // Capacity Chart
        const capacityCtx = document.getElementById('capacityChart').getContext('2d');
        new Chart(capacityCtx, {
            type: 'bar',
            data: {
                labels: @json($chartData['event_names']),
                datasets: [{
                    label: 'Capacity Used',
                    data: @json($chartData['capacity_used']),
                    backgroundColor: 'rgba(59, 130, 246, 0.8)',
                    borderColor: 'rgb(59, 130, 246)',
                    borderWidth: 1
                }, {
                    label: 'Capacity Available',
                    data: @json($chartData['capacity_available']),
                    backgroundColor: 'rgba(156, 163, 175, 0.8)',
                    borderColor: 'rgb(156, 163, 175)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { 
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    }
                },
                scales: {
                    x: { 
                        stacked: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    },
                    y: { 
                        stacked: true, 
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });

        // Popular Events Chart
        const popularCtx = document.getElementById('popularChart').getContext('2d');
        new Chart(popularCtx, {
            type: 'bar',
            data: {
                labels: @json($chartData['popular_event_names']),
                datasets: [{
                    label: 'Registrations',
                    data: @json($chartData['popular_event_registrations']),
                    backgroundColor: 'rgba(239, 68, 68, 0.8)',
                    borderColor: 'rgb(239, 68, 68)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { 
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });
    </script>
    @endpush
@endsection
