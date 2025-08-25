@extends('layouts.app')

@section('title', 'Dashboard - ' . config('app.name'))

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Welcome Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h1 class="text-2xl font-semibold text-gray-900 mb-2">Welcome back, {{ auth()->user()->name }}!</h1>
                    <p class="text-gray-600">Manage your event registrations and view your upcoming events.</p>
                </div>
            </div>

            <!-- My Registrations Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900">My Registrations</h2>
                </div>
                <div class="p-6">
                    @if(auth()->user()->registrations->count() > 0)
                        <div class="space-y-4">
                            @foreach(auth()->user()->registrations()->with(['event'])->orderBy('created_at', 'desc')->get() as $registration)
                                <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50">
                                    <div class="flex items-center justify-between">
                                        <div class="flex-1">
                                            <h3 class="text-lg font-medium text-gray-900">{{ $registration->event->title }}</h3>
                                            <div class="mt-1 flex items-center space-x-4 text-sm text-gray-500">
                                                <span>{{ $registration->event->start_date->format('M d, Y') }}</span>
                                                <span>{{ $registration->event->venue }}</span>
                                                <span class="font-mono">{{ $registration->registration_code }}</span>
                                            </div>
                                            <div class="mt-2">
                                                @if($registration->status === 'confirmed')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                        </svg>
                                                        Confirmed
                                                    </span>
                                                @elseif($registration->status === 'pending')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                        </svg>
                                                        Pending Approval
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                        </svg>
                                                        Cancelled
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="flex space-x-2">
                                            @if($registration->status === 'confirmed')
                                                <a href="{{ route('registrations.qr-print', $registration) }}" target="_blank" class="inline-flex items-center px-3 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                                    </svg>
                                                    Print Registration Card
                                                </a>
                                            @elseif($registration->status === 'pending')
                                                <span class="inline-flex items-center px-3 py-2 bg-gray-100 text-gray-500 text-sm font-medium rounded-md">
                                                    Awaiting Admin Approval
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No registrations yet</h3>
                            <p class="text-gray-500 mb-4">You haven't registered for any events yet.</p>
                            <a href="{{ route('events.index') }}" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Browse Events
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
