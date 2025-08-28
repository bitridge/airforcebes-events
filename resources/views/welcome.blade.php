@extends('layouts.app')

@section('title', config('app.name') . ' - Professional Development Events')
@section('description', 'Discover and register for upcoming BES events, workshops, and networking opportunities.')

@section('content')
    <!-- Hero Section -->
    <section class="bg-slate-800 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="text-center">
                <h1 class="text-4xl md:text-6xl font-bold mb-6">BES Event Management</h1>
                <p class="text-xl md:text-2xl text-slate-300 mb-8 max-w-3xl mx-auto">
                    Discover and register for upcoming BES events, workshops, and networking opportunities.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('events.index') }}" class="bg-red-600 hover:bg-red-700 text-white px-8 py-4 rounded-lg text-lg font-semibold transition-colors duration-200">
                        Browse Events
                    </a>
                    @guest
                        <a href="{{ route('register') }}" class="border-2 border-white text-white hover:bg-white hover:text-slate-800 px-8 py-4 rounded-lg text-lg font-semibold transition-all duration-200">
                            Create Account
                        </a>
                    @else
                        <a href="{{ route('self-checkin.index') }}" class="bg-green-600 hover:bg-green-700 text-white px-8 py-4 rounded-lg text-lg font-semibold transition-colors duration-200">
                            Check In
                        </a>
                    @endguest
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="bg-white py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="text-4xl font-bold text-slate-800 mb-2">{{ $statistics['total_events'] }}</div>
                    <div class="text-slate-600">Total Events</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold text-slate-800 mb-2">{{ $statistics['upcoming_events'] }}</div>
                    <div class="text-slate-600">Upcoming Events</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold text-slate-800 mb-2">{{ $statistics['total_registrations'] }}</div>
                    <div class="text-slate-600">Total Registrations</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold text-slate-800 mb-2">{{ $statistics['total_attendees'] }}</div>
                    <div class="text-slate-600">Event Attendees</div>
                </div>
            </div>
        </div>
    </section>

    @include('components.featured-events', ['events' => $featuredEvents])

    <!-- Call to Action Section -->
    <section class="bg-slate-800 text-white py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl font-bold mb-4">Ready to Join Our Community?</h2>
            <p class="text-xl text-slate-300 mb-8 max-w-2xl mx-auto">
                Connect with fellow BES professionals and advance your career.
            </p>
            @guest
                <a href="{{ route('register') }}" class="bg-red-600 hover:bg-red-700 text-white px-8 py-4 rounded-lg text-lg font-semibold transition-colors duration-200">
                    Get Started Today
                </a>
            @else
                <a href="{{ route('events.index') }}" class="bg-red-600 hover:bg-red-700 text-white px-8 py-4 rounded-lg text-lg font-semibold transition-colors duration-200">
                    Explore Events
                </a>
            @endguest
        </div>
    </section>
@endsection
