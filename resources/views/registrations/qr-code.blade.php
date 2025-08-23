<x-app-layout>
    <x-slot name="title">QR Code - {{ $registration->event->title }} - {{ config('app.name') }}</x-slot>
    <x-slot name="description">Your QR code for {{ $registration->event->title }}. Show this code at the event entrance for quick check-in.</x-slot>

    @push('head')
    <style>
        .qr-container {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            text-align: center;
            max-width: 500px;
            margin: 0 auto;
        }
        
        .qr-code svg {
            max-width: 100%;
            height: auto;
            border: 8px solid #f1f5f9;
            border-radius: 0.5rem;
            background: white;
        }
        
        .registration-details {
            background: #f8fafc;
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin: 1.5rem 0;
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .status-confirmed {
            background-color: #dcfce7;
            color: #166534;
        }
        
        .instructions {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin: 1.5rem 0;
        }
        
        @media print {
            .no-print {
                display: none !important;
            }
            
            .qr-container {
                box-shadow: none;
                border: 2px solid #e5e7eb;
            }
        }
    </style>
    @endpush

    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="text-center mb-8 no-print">
                <div class="flex items-center justify-center mb-4">
                    <a href="{{ route('registrations.index') }}" class="inline-flex items-center text-gray-600 hover:text-gray-900 transition-colors duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to My Registrations
                    </a>
                </div>
                <h1 class="text-3xl font-bold text-gray-900">Your QR Code</h1>
                <p class="text-lg text-gray-600 mt-2">Show this code at the event entrance for quick check-in</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- QR Code Section -->
                <div class="lg:col-span-2">
                    <div class="qr-container">
                        <!-- Status Badge -->
                        <div class="status-badge status-confirmed">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Registration Confirmed
                        </div>

                        <!-- QR Code -->
                        <div class="qr-code mb-6">
                            {!! $qrCode !!}
                        </div>

                        <!-- Registration Code -->
                        <div class="registration-details">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Registration Code</h3>
                            <div class="text-2xl font-mono font-bold text-red-600 bg-white px-4 py-2 rounded border-2 border-dashed border-red-200 inline-block">
                                {{ $registration->registration_code }}
                            </div>
                            <p class="text-sm text-gray-600 mt-2">Use this code if QR scanning is not available</p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex flex-col sm:flex-row gap-3 mt-6 no-print">
                            <a href="{{ $downloadUrl }}" 
                               class="flex-1 inline-flex items-center justify-center px-4 py-3 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Download QR Code
                            </a>
                            <a href="{{ route('registrations.qr-print', $registration) }}" 
                               target="_blank"
                               class="flex-1 inline-flex items-center justify-center px-4 py-3 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                </svg>
                                Print QR Code
                            </a>
                            <button onclick="window.print()" 
                                    class="flex-1 inline-flex items-center justify-center px-4 py-3 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                </svg>
                                Quick Print
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Event Details Sidebar -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Event Details</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-600">Event</dt>
                                <dd class="text-sm text-gray-900 font-semibold">{{ $registration->event->title }}</dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-gray-600">Date & Time</dt>
                                <dd class="text-sm text-gray-900">{{ $registration->event->formatted_date_range }}</dd>
                                <dd class="text-sm text-gray-900">{{ $registration->event->formatted_time_range }}</dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-gray-600">Venue</dt>
                                <dd class="text-sm text-gray-900">{{ $registration->event->venue }}</dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-gray-600">Registration Date</dt>
                                <dd class="text-sm text-gray-900">{{ $registration->formatted_registration_date }}</dd>
                            </div>
                            
                            <div>
                                <dt class="text-sm font-medium text-gray-600">Check-in Status</dt>
                                <dd class="text-sm">
                                    @if($registration->isCheckedIn())
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            Checked In
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            Pending Check-in
                                        </span>
                                    @endif
                                </dd>
                            </div>
                        </div>
                    </div>

                    <!-- Check-in Instructions -->
                    <div class="instructions no-print">
                        <h3 class="text-lg font-semibold text-blue-900 mb-3">📱 How to Use Your QR Code</h3>
                        <ol class="text-sm text-blue-800 space-y-2">
                            <li class="flex items-start">
                                <span class="flex-shrink-0 w-5 h-5 bg-blue-200 text-blue-800 rounded-full flex items-center justify-center text-xs font-semibold mr-2 mt-0.5">1</span>
                                <span>Arrive at the event venue</span>
                            </li>
                            <li class="flex items-start">
                                <span class="flex-shrink-0 w-5 h-5 bg-blue-200 text-blue-800 rounded-full flex items-center justify-center text-xs font-semibold mr-2 mt-0.5">2</span>
                                <span>Show this QR code to event staff at the check-in station</span>
                            </li>
                            <li class="flex items-start">
                                <span class="flex-shrink-0 w-5 h-5 bg-blue-200 text-blue-800 rounded-full flex items-center justify-center text-xs font-semibold mr-2 mt-0.5">3</span>
                                <span>If QR scanning is unavailable, provide your registration code: <strong>{{ $registration->registration_code }}</strong></span>
                            </li>
                            <li class="flex items-start">
                                <span class="flex-shrink-0 w-5 h-5 bg-blue-200 text-blue-800 rounded-full flex items-center justify-center text-xs font-semibold mr-2 mt-0.5">4</span>
                                <span>Bring a valid government ID for verification</span>
                            </li>
                        </ol>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white rounded-xl shadow-sm p-6 no-print">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                        <div class="space-y-3">
                            <a href="{{ route('events.show', $registration->event->slug) }}" 
                               class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                View Event Details
                            </a>
                            <a href="{{ route('registrations.index') }}" 
                               class="w-full inline-flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                                My Registrations
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Save QR code to device function
        function saveQRToDevice() {
            // This would integrate with mobile wallet APIs
            // For now, we'll just prompt the user to save the image
            alert('To save to your mobile wallet:\n\n1. Take a screenshot of this QR code\n2. Add it to your photo gallery\n3. Most wallet apps can import QR codes from photos');
        }

        // Check if page was opened for printing
        if (window.location.hash === '#print') {
            window.onload = function() {
                window.print();
            };
        }
    </script>
    @endpush
</x-app-layout>
