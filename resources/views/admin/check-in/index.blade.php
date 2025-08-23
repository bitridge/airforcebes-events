<x-app-layout>
    <x-slot name="title">Event Check-in - {{ config('app.name') }}</x-slot>

    <!-- Custom Styles for Check-in Interface -->
    @push('head')
    <style>
        .scanner-container {
            position: relative;
            max-width: 600px;
            margin: 0 auto;
            background: #f8fafc;
            border: 2px dashed #cbd5e1;
            border-radius: 1rem;
            padding: 3rem 2rem;
            text-align: center;
        }
        
        .scanner-active {
            border-color: #3b82f6;
            background: #eff6ff;
        }
        
        .camera-icon {
            width: 120px;
            height: 120px;
            margin: 0 auto 2rem;
            color: #94a3b8;
        }
        
        .scanner-active .camera-icon {
            color: #3b82f6;
        }
        
        #video {
            width: 100%;
            max-width: 500px;
            height: auto;
            border-radius: 0.5rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .success-animation {
            animation: pulse 0.6s ease-in-out;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .check-in-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }
        
        .check-in-card:hover {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
    </style>
    @endpush

    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-4xl font-bold text-gray-900 mb-4">Event Check-in</h1>
                <p class="text-xl text-gray-600">Scan participant QR codes to check them into the event</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- QR Scanner Section -->
                <div class="lg:col-span-2">
                    <div class="check-in-card p-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">📷 QR Code Scanner</h2>
                        
                        <!-- Scanner Container -->
                        <div id="scanner-container" class="scanner-container">
                            <div class="camera-icon">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            
                            <h3 class="text-xl font-semibold text-gray-900 mb-4">QR Code Scanner</h3>
                            <p class="text-gray-600 mb-6">Click the button below to start the QR code scanner</p>
                            
                            <!-- Video Element (Hidden Initially) -->
                            <video id="video" style="display: none;" autoplay playsinline></video>
                            
                            <!-- Scanner Controls -->
                            <div id="scanner-controls">
                                <button id="start-camera" class="bg-slate-800 hover:bg-slate-900 text-white px-8 py-4 rounded-lg text-lg font-semibold transition-colors duration-200 flex items-center mx-auto">
                                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    Start Camera
                                </button>
                                
                                <button id="stop-camera" class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors duration-200 ml-4" style="display: none;">
                                    Stop Camera
                                </button>
                            </div>
                        </div>

                        <!-- Manual Input Fallback -->
                        <div class="mt-8 p-6 bg-gray-50 rounded-lg">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Manual Code Entry</h3>
                            <div class="flex space-x-4">
                                <input type="text" id="manual-code" placeholder="Enter registration code..." 
                                       class="flex-1 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-lg">
                                <button id="manual-submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors duration-200">
                                    Check In
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Today's Events -->
                    <div class="check-in-card p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Today's Events</h3>
                        @if($todaysEvents->count() > 0)
                            <div class="space-y-3">
                                @foreach($todaysEvents as $event)
                                    <div class="p-3 bg-gray-50 rounded-lg">
                                        <h4 class="font-medium text-gray-900">{{ $event->title }}</h4>
                                        <p class="text-sm text-gray-600">{{ $event->venue }}</p>
                                        <div class="mt-2 flex justify-between text-xs text-gray-500">
                                            <span>{{ $event->confirmed_registrations_count }} registered</span>
                                            <span>{{ $event->check_ins_count }} checked in</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 text-sm">No events today</p>
                        @endif
                    </div>

                    <!-- Recent Check-ins -->
                    <div class="check-in-card p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Check-ins</h3>
                        @if($recentCheckIns->count() > 0)
                            <div class="space-y-3 max-h-64 overflow-y-auto">
                                @foreach($recentCheckIns as $checkIn)
                                    <div class="flex items-center space-x-3 p-2 bg-green-50 rounded-lg">
                                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate">
                                                {{ $checkIn->registration->user->name }}
                                            </p>
                                            <p class="text-xs text-gray-500 truncate">
                                                {{ $checkIn->registration->event->title }}
                                            </p>
                                            <p class="text-xs text-gray-400">
                                                {{ $checkIn->checked_in_at->diffForHumans() }}
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 text-sm">No recent check-ins</p>
                        @endif
                    </div>

                    <!-- Quick Actions -->
                    <div class="check-in-card p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                        <div class="space-y-3">
                            <a href="{{ route('admin.check-in.manual') }}" 
                               class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white py-3 px-4 rounded-lg font-medium transition-colors duration-200">
                                Manual Check-in
                            </a>
                            <a href="{{ route('admin.events.index') }}" 
                               class="block w-full text-center bg-gray-600 hover:bg-gray-700 text-white py-3 px-4 rounded-lg font-medium transition-colors duration-200">
                                Manage Events
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="success-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" style="display: none;">
        <div class="bg-white rounded-2xl p-8 max-w-md w-full mx-4 text-center">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Check-in Successful!</h3>
            <div id="success-details" class="text-gray-600 mb-6"></div>
            <button id="close-modal" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors duration-200">
                Continue
            </button>
        </div>
    </div>

    <!-- Error Modal -->
    <div id="error-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" style="display: none;">
        <div class="bg-white rounded-2xl p-8 max-w-md w-full mx-4 text-center">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Check-in Failed</h3>
            <div id="error-details" class="text-gray-600 mb-6"></div>
            <button id="close-error-modal" class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors duration-200">
                Try Again
            </button>
        </div>
    </div>

    @push('scripts')
    <script src="https://unpkg.com/jsqr@1.4.0/dist/jsQR.js"></script>
    <script>
        class QRScanner {
            constructor() {
                this.video = document.getElementById('video');
                this.canvas = document.createElement('canvas');
                this.context = this.canvas.getContext('2d');
                this.stream = null;
                this.scanning = false;
                this.initializeEventListeners();
            }

            initializeEventListeners() {
                // Start camera button
                document.getElementById('start-camera').addEventListener('click', () => {
                    this.startCamera();
                });

                // Stop camera button
                document.getElementById('stop-camera').addEventListener('click', () => {
                    this.stopCamera();
                });

                // Manual submit
                document.getElementById('manual-submit').addEventListener('click', () => {
                    const code = document.getElementById('manual-code').value.trim();
                    if (code) {
                        this.checkInByCode(code);
                    }
                });

                // Enter key for manual input
                document.getElementById('manual-code').addEventListener('keypress', (e) => {
                    if (e.key === 'Enter') {
                        const code = e.target.value.trim();
                        if (code) {
                            this.checkInByCode(code);
                        }
                    }
                });

                // Modal close buttons
                document.getElementById('close-modal').addEventListener('click', () => {
                    this.hideModal('success-modal');
                });

                document.getElementById('close-error-modal').addEventListener('click', () => {
                    this.hideModal('error-modal');
                });
            }

            async startCamera() {
                try {
                    this.stream = await navigator.mediaDevices.getUserMedia({
                        video: { 
                            facingMode: 'environment',
                            width: { ideal: 640 },
                            height: { ideal: 480 }
                        }
                    });

                    this.video.srcObject = this.stream;
                    this.video.style.display = 'block';
                    
                    document.getElementById('start-camera').style.display = 'none';
                    document.getElementById('stop-camera').style.display = 'inline-block';
                    document.getElementById('scanner-container').classList.add('scanner-active');

                    this.scanning = true;
                    this.scanQR();

                    // Play success sound
                    this.playSound('start');

                } catch (error) {
                    console.error('Error accessing camera:', error);
                    this.showError('Unable to access camera. Please check permissions.');
                }
            }

            stopCamera() {
                this.scanning = false;
                
                if (this.stream) {
                    this.stream.getTracks().forEach(track => track.stop());
                    this.stream = null;
                }

                this.video.style.display = 'none';
                document.getElementById('start-camera').style.display = 'inline-block';
                document.getElementById('stop-camera').style.display = 'none';
                document.getElementById('scanner-container').classList.remove('scanner-active');
            }

            scanQR() {
                if (!this.scanning) return;

                if (this.video.readyState === this.video.HAVE_ENOUGH_DATA) {
                    this.canvas.width = this.video.videoWidth;
                    this.canvas.height = this.video.videoHeight;
                    this.context.drawImage(this.video, 0, 0, this.canvas.width, this.canvas.height);

                    const imageData = this.context.getImageData(0, 0, this.canvas.width, this.canvas.height);
                    const code = jsQR(imageData.data, imageData.width, imageData.height);

                    if (code) {
                        this.processQRCode(code.data);
                        return; // Stop scanning after successful read
                    }
                }

                requestAnimationFrame(() => this.scanQR());
            }

            async processQRCode(qrData) {
                try {
                    const response = await fetch('{{ route("admin.check-in.scan") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify({ qr_data: qrData })
                    });

                    const result = await response.json();

                    if (result.success) {
                        this.showSuccess(result.registration, result.message);
                        this.playSound('success');
                    } else {
                        this.showError(result.message);
                        this.playSound('error');
                    }

                } catch (error) {
                    console.error('Check-in error:', error);
                    this.showError('Network error. Please try again.');
                    this.playSound('error');
                }

                // Resume scanning after 3 seconds
                setTimeout(() => {
                    if (this.scanning) {
                        this.scanQR();
                    }
                }, 3000);
            }

            async checkInByCode(code) {
                try {
                    const response = await fetch('{{ route("admin.check-in.code") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify({ registration_code: code })
                    });

                    const result = await response.json();

                    if (result.success) {
                        this.showSuccess(result.registration, result.message);
                        this.playSound('success');
                        document.getElementById('manual-code').value = '';
                    } else {
                        this.showError(result.message);
                        this.playSound('error');
                    }

                } catch (error) {
                    console.error('Check-in error:', error);
                    this.showError('Network error. Please try again.');
                    this.playSound('error');
                }
            }

            showSuccess(registration, message) {
                const details = `
                    <p class="font-medium">${registration.user_name}</p>
                    <p class="text-sm">${registration.event_title}</p>
                    <p class="text-xs text-gray-500">Code: ${registration.code}</p>
                `;
                document.getElementById('success-details').innerHTML = details;
                this.showModal('success-modal');
            }

            showError(message) {
                document.getElementById('error-details').textContent = message;
                this.showModal('error-modal');
            }

            showModal(modalId) {
                document.getElementById(modalId).style.display = 'flex';
                document.querySelector(`#${modalId} > div`).classList.add('success-animation');
            }

            hideModal(modalId) {
                document.getElementById(modalId).style.display = 'none';
            }

            playSound(type) {
                // Create audio context for sound feedback
                const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                
                let frequency;
                switch (type) {
                    case 'success':
                        frequency = 800;
                        break;
                    case 'error':
                        frequency = 300;
                        break;
                    case 'start':
                        frequency = 600;
                        break;
                    default:
                        frequency = 500;
                }

                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();

                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);

                oscillator.frequency.value = frequency;
                oscillator.type = 'sine';

                gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);

                oscillator.start(audioContext.currentTime);
                oscillator.stop(audioContext.currentTime + 0.3);
            }
        }

        // Initialize scanner when page loads
        document.addEventListener('DOMContentLoaded', () => {
            new QRScanner();
        });
    </script>
    @endpush
</x-app-layout>
