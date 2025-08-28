@extends('layouts.app')

@section('title', 'Check-in System - ' . config('app.name'))

@section('content')
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-semibold text-gray-900">Check-in System</h1>
                <div class="flex space-x-3">
                    <a href="{{ route('admin.check-in.manual') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Manual Check-in
                    </a>
                </div>
            </div>

            <!-- QR Scanner Section -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">QR Code Scanner</h2>
                <div class="text-center">
                    <div id="scanner-container" class="max-w-md mx-auto">
                        <video id="qr-video" class="w-full rounded-lg" autoplay muted></video>
                        <div id="scanner-status" class="mt-4 text-sm text-gray-600">
                            Initializing camera...
                        </div>
                    </div>
                    <div class="mt-4">
                        <button id="start-scan" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 mr-2">
                            Start Scanner
                        </button>
                        <button id="stop-scan" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700" disabled>
                            Stop Scanner
                        </button>
                    </div>
                </div>
            </div>

            <!-- Quick Check-in Section -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Quick Check-in</h2>
                <form id="quick-checkin-form" class="max-w-md">
                    @csrf
                    <div class="mb-4">
                        <label for="registration_code" class="block text-sm font-medium text-gray-700 mb-1">Registration Code</label>
                        <input type="text" id="registration_code" name="registration_code" 
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                               placeholder="Enter registration code">
                    </div>
                    <button type="submit" class="w-full bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                        Check In
                    </button>
                </form>
            </div>

            <!-- Recent Check-ins -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Recent Check-ins</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attendee</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check-in Time</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Checked by</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($recentCheckIns as $checkIn)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $checkIn->registration->user->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $checkIn->registration->user->email }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $checkIn->registration->event->title }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $checkIn->checked_in_at->format('M j, Y g:i A') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($checkIn->check_in_method === 'qr') bg-green-100 text-green-800
                                            @elseif($checkIn->check_in_method === 'manual') bg-blue-100 text-blue-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            {{ ucfirst($checkIn->check_in_method) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $checkIn->checkedInBy ? $checkIn->checkedInBy->name : 'System' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <p class="text-lg font-medium">No recent check-ins</p>
                                        <p class="text-sm">Check-ins will appear here once attendees start arriving.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
    <script>
        let video = null;
        let canvas = null;
        let context = null;
        let scanning = false;

        document.addEventListener('DOMContentLoaded', function() {
            video = document.getElementById('qr-video');
            canvas = document.createElement('canvas');
            context = canvas.getContext('2d');

            document.getElementById('start-scan').addEventListener('click', startScanner);
            document.getElementById('stop-scan').addEventListener('click', stopScanner);
            document.getElementById('quick-checkin-form').addEventListener('submit', handleQuickCheckin);
        });

        async function startScanner() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
                video.srcObject = stream;
                video.play();
                
                document.getElementById('start-scan').disabled = true;
                document.getElementById('stop-scan').disabled = false;
                document.getElementById('scanner-status').textContent = 'Scanning for QR codes...';
                
                scanning = true;
                scanQRCode();
            } catch (error) {
                console.error('Error accessing camera:', error);
                document.getElementById('scanner-status').textContent = 'Error: Could not access camera';
            }
        }

        function stopScanner() {
            if (video.srcObject) {
                video.srcObject.getTracks().forEach(track => track.stop());
            }
            
            document.getElementById('start-scan').disabled = false;
            document.getElementById('stop-scan').disabled = true;
            document.getElementById('scanner-status').textContent = 'Scanner stopped';
            
            scanning = false;
        }

        function scanQRCode() {
            if (!scanning) return;

            if (video.readyState === video.HAVE_ENOUGH_DATA) {
                canvas.height = video.videoHeight;
                canvas.width = video.videoWidth;
                context.drawImage(video, 0, 0, canvas.width, canvas.height);
                
                const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
                const code = jsQR(imageData.data, imageData.width, imageData.height);
                
                if (code) {
                    handleQRCode(code.data);
                    return;
                }
            }
            
            requestAnimationFrame(scanQRCode);
        }

        function handleQRCode(data) {
            // Play success sound
            playBeep();
            
            // Show success message
            document.getElementById('scanner-status').textContent = 'QR Code detected! Processing...';
            
            // Process the QR code data
            processCheckin(data);
        }

        function handleQuickCheckin(e) {
            e.preventDefault();
            const code = document.getElementById('registration_code').value;
            if (code) {
                processCheckin(code);
                document.getElementById('registration_code').value = '';
            }
        }

        async function processCheckin(code) {
            try {
                // Determine if this is a QR code scan or manual code entry
                // QR codes contain JSON data, manual codes are just strings
                let isQRCode = false;
                try {
                    JSON.parse(code);
                    isQRCode = true;
                } catch (e) {
                    isQRCode = false;
                }

                const route = isQRCode ? '{{ route("admin.check-in.scan") }}' : '{{ route("admin.check-in.code") }}';
                const payload = isQRCode ? { qr_data: code } : { registration_code: code };

                const response = await fetch(route, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(payload)
                });

                const result = await response.json();
                
                if (result.success) {
                    showSuccessMessage(result.message);
                    // Refresh the page to show updated check-ins
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showErrorMessage(result.message);
                }
            } catch (error) {
                console.error('Error processing check-in:', error);
                showErrorMessage('An error occurred while processing the check-in');
            }
        }

        function showSuccessMessage(message) {
            // Create success notification
            const notification = document.createElement('div');
            notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-md shadow-lg z-50';
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        function showErrorMessage(message) {
            // Create error notification
            const notification = document.createElement('div');
            notification.className = 'fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-md shadow-lg z-50';
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        function playBeep() {
            // Create audio context for beep sound
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);
            
            oscillator.frequency.value = 800;
            oscillator.type = 'sine';
            
            gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.1);
            
            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.1);
        }
    </script>
    @endpush
@endsection
