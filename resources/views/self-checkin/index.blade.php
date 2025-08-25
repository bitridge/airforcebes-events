@extends('layouts.app')

@section('title', 'Self Check-In - ' . config('app.name'))

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Self Check-In</h1>
            <p class="mt-2 text-gray-600">Check in to your confirmed event registrations</p>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                {{ session('error') }}
            </div>
        @endif

        <!-- Check-in Methods -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- QR Code Check-in -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">QR Code Check-in</h2>
                <p class="text-gray-600 mb-4">Scan your QR code using your camera to check in quickly</p>
                
                <div class="space-y-4">
                    <!-- Camera Scanner -->
                    <div class="text-center">
                        <div id="qr-reader" class="w-full max-w-md mx-auto"></div>
                        <div id="qr-reader-results" class="mt-4"></div>
                    </div>
                    
                    <!-- Manual Input Fallback -->
                    <div class="border-t pt-4">
                        <p class="text-sm text-gray-600 mb-2">Or enter QR code manually:</p>
                        <div class="flex space-x-2">
                            <input type="text" id="qr_code" name="qr_code" placeholder="Enter or paste QR code data" 
                                   class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <button type="button" onclick="processQrCode()" 
                                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition-colors duration-200">
                                Check In
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Manual Check-in -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Manual Check-in</h2>
                <p class="text-gray-600 mb-4">Enter your registration code manually</p>
                
                <div class="space-y-4">
                    <div>
                        <label for="registration_code" class="block text-sm font-medium text-gray-700 mb-2">Registration Code</label>
                        <input type="text" id="registration_code" name="registration_code" placeholder="Enter your registration code" 
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    
                    <button type="button" onclick="manualCheckIn()" 
                            class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                        Check In Manually
                    </button>
                </div>
            </div>
        </div>

        <!-- Your Registrations -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">Your Confirmed Registrations</h2>
                <p class="text-sm text-gray-600 mt-1">Select a registration to view details or check in</p>
            </div>
            
            <div class="p-6">
                @if($registrations->count() > 0)
                    <div class="space-y-4">
                        @foreach($registrations as $registration)
                            <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors duration-200">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <h3 class="text-lg font-medium text-gray-900">{{ $registration->event->title }}</h3>
                                        <p class="text-sm text-gray-600 mt-1">
                                            <span class="font-medium">Registration Code:</span> 
                                            <span class="font-mono text-indigo-600">{{ $registration->registration_code }}</span>
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            <span class="font-medium">Event Date:</span> 
                                            {{ $registration->event->start_date->format('M j, Y') }}
                                        </p>
                                        @if($registration->event->start_time)
                                            <p class="text-sm text-gray-600">
                                                <span class="font-medium">Event Time:</span> 
                                                {{ $registration->event->display_start_time }} - {{ $registration->event->display_end_time }}
                                            </p>
                                        @endif
                                        
                                        @if($registration->checkIn)
                                            <div class="mt-2">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    ✓ Checked In
                                                </span>
                                                <span class="text-sm text-gray-500 ml-2">
                                                    {{ $registration->checkIn->checked_in_at->format('M j, Y g:i A') }}
                                                </span>
                                            </div>
                                        @else
                                            <div class="mt-2">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    Not Checked In
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <div class="flex flex-col space-y-2 ml-4">
                                        @if(!$registration->checkIn)
                                            <button type="button" 
                                                    onclick="checkInRegistration('{{ $registration->registration_code }}')"
                                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                Check In
                                            </button>
                                        @endif
                                        
                                        <a href="{{ route('registrations.qr-code', $registration) }}" 
                                           target="_blank"
                                           class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            View QR Code
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No confirmed registrations</h3>
                        <p class="mt-1 text-sm text-gray-500">You don't have any confirmed event registrations yet.</p>
                        <div class="mt-6">
                            <a href="{{ route('events.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                Browse Events
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div id="successModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mt-4" id="successTitle">Check-in Successful!</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500" id="successMessage"></p>
                <p class="text-sm text-gray-500 mt-2" id="successDetails"></p>
            </div>
            <div class="items-center px-4 py-3">
                <button id="closeSuccessModal" class="px-4 py-2 bg-green-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-300">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Error Modal -->
<div id="errorModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mt-4">Check-in Failed</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500" id="errorMessage"></p>
            </div>
            <div class="items-center px-4 py-3">
                <button id="closeErrorModal" class="px-4 py-2 bg-red-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let videoStream = null;
let canvas = null;
let context = null;
let scanning = false;

// Initialize camera scanner when page loads
document.addEventListener('DOMContentLoaded', function() {
    initializeCameraScanner();
});

// Initialize camera scanner using native HTML5
function initializeCameraScanner() {
    const qrReader = document.getElementById('qr-reader');
    
    // Create camera interface
    qrReader.innerHTML = `
        <div class="text-center">
            <video id="camera" class="w-full max-w-md mx-auto rounded-lg border-2 border-gray-300" autoplay muted></video>
            <canvas id="canvas" class="hidden"></canvas>
            <div class="mt-4 space-y-2">
                <button id="startScan" onclick="startScanning()" 
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition-colors duration-200">
                    Start Camera Scanner
                </button>
                <button id="stopScan" onclick="stopScanning()" 
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors duration-200 hidden">
                    Stop Scanner
                </button>
            </div>
            <div class="mt-2 text-sm text-gray-600">
                Point your camera at a QR code to scan
            </div>
        </div>
    `;
    
    // Get video and canvas elements
    const video = document.getElementById('camera');
    canvas = document.getElementById('canvas');
    context = canvas.getContext('2d');
    
    // Request camera access
    navigator.mediaDevices.getUserMedia({ 
        video: { 
            facingMode: 'environment',
            width: { ideal: 640 },
            height: { ideal: 480 }
        } 
    })
    .then(function(stream) {
        videoStream = stream;
        video.srcObject = stream;
        video.play();
        
        // Show start button
        document.getElementById('startScan').classList.remove('hidden');
    })
    .catch(function(err) {
        console.error('Camera access error:', err);
        qrReader.innerHTML = `
            <div class="text-center p-4">
                <div class="text-red-600 mb-2">
                    <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <p class="text-sm text-gray-600">Camera access denied or not available</p>
                <p class="text-xs text-gray-500 mt-1">Please use manual input below</p>
            </div>
        `;
    });
}

// Start scanning for QR codes
function startScanning() {
    if (!videoStream) return;
    
    scanning = true;
    document.getElementById('startScan').classList.add('hidden');
    document.getElementById('stopScan').classList.remove('hidden');
    
    // Start scanning loop
    scanFrame();
}

// Stop scanning
function stopScanning() {
    scanning = false;
    document.getElementById('startScan').classList.remove('hidden');
    document.getElementById('stopScan').classList.add('hidden');
}

// Scan video frames for QR codes
function scanFrame() {
    if (!scanning) return;
    
    const video = document.getElementById('camera');
    const canvas = document.getElementById('canvas');
    const context = canvas.getContext('2d');
    
    // Set canvas size to match video
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    
    // Draw current video frame to canvas
    context.drawImage(video, 0, 0, canvas.width, canvas.height);
    
    // Get image data for processing
    const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
    
    // Try to detect QR code using existing QR code system
    // This will use your existing SimpleSoftwareIO QR code library
    detectQRCode(imageData);
    
    // Continue scanning if still active
    if (scanning) {
        requestAnimationFrame(scanFrame);
    }
}

// Detect QR code using existing system
function detectQRCode(imageData) {
    // For now, we'll use a simple approach
    // In a real implementation, you could use your existing QR code library
    // to process the image data and detect QR codes
    
    // This is a placeholder - you can enhance this with your existing QR code detection
    // For now, we'll rely on the manual input method
}

// Process QR code from manual input (existing functionality)
function processQrCode() {
    const qrCode = document.getElementById('qr_code').value.trim();
    
    if (!qrCode) {
        showError('Please enter a QR code.');
        return;
    }
    
    // Show loading state
    const button = event.target;
    const originalText = button.textContent;
    button.disabled = true;
    button.textContent = 'Processing...';
    
    fetch('{{ route("self-checkin.qr-code") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ qr_code: qrCode })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess('Check-in Successful!', data.message, `Check-in time: ${data.checkin_time}`);
            document.getElementById('qr_code').value = '';
            // Reload page to update registration list
            setTimeout(() => location.reload(), 2000);
        } else {
            showError(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('An error occurred. Please try again.');
    })
    .finally(() => {
        button.disabled = false;
        button.textContent = originalText;
    });
}

// Process QR code check-in (manual input)
function processQrCode() {
    const qrCode = document.getElementById('qr_code').value.trim();
    
    if (!qrCode) {
        showError('Please enter a QR code.');
        return;
    }
    
    // Show loading state
    const button = event.target;
    const originalText = button.textContent;
    button.disabled = true;
    button.textContent = 'Processing...';
    
    fetch('{{ route("self-checkin.qr-code") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ qr_code: qrCode })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess('Check-in Successful!', data.message, `Check-in time: ${data.checkin_time}`);
            document.getElementById('qr_code').value = '';
            // Reload page to update registration list
            setTimeout(() => location.reload(), 2000);
        } else {
            showError(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('An error occurred. Please try again.');
    })
    .finally(() => {
        button.disabled = false;
        button.textContent = originalText;
    });
}

// Manual check-in
function manualCheckIn() {
    const registrationCode = document.getElementById('registration_code').value.trim();
    
    if (!registrationCode) {
        showError('Please enter a registration code.');
        return;
    }
    
    // Show loading state
    const button = event.target;
    const originalText = button.textContent;
    button.disabled = true;
    button.textContent = 'Processing...';
    
    fetch('{{ route("self-checkin.manual") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ registration_code: registrationCode })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess('Check-in Successful!', data.message, `Check-in time: ${data.checkin_time}`);
            document.getElementById('registration_code').value = '';
            // Reload page to update registration list
            setTimeout(() => location.reload(), 2000);
        } else {
            showError(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('An error occurred. Please try again.');
    })
    .finally(() => {
        button.disabled = false;
        button.textContent = originalText;
    });
}

// Check in from registration list
function checkInRegistration(registrationCode) {
    document.getElementById('registration_code').value = registrationCode;
    manualCheckIn();
}

// Show success modal
function showSuccess(title, message, details) {
    document.getElementById('successTitle').textContent = title;
    document.getElementById('successMessage').textContent = message;
    document.getElementById('successDetails').textContent = details;
    document.getElementById('successModal').classList.remove('hidden');
}

// Show error modal
function showError(message) {
    document.getElementById('errorMessage').textContent = message;
    document.getElementById('errorModal').classList.remove('hidden');
}

// Close modals
document.getElementById('closeSuccessModal').addEventListener('click', function() {
    document.getElementById('successModal').classList.add('hidden');
});

document.getElementById('closeErrorModal').addEventListener('click', function() {
    document.getElementById('errorModal').classList.add('hidden');
});

// Close modals when clicking outside
window.addEventListener('click', function(event) {
    const successModal = document.getElementById('successModal');
    const errorModal = document.getElementById('errorModal');
    
    if (event.target === successModal) {
        successModal.classList.add('hidden');
    }
    if (event.target === errorModal) {
        errorModal.classList.add('hidden');
    }
});
</script>
@endsection
