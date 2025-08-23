<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code - {{ $registration->registration_code }} - {{ config('app.name') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            background: white;
            color: #333;
            line-height: 1.4;
        }
        
        .print-container {
            max-width: 8.5in;
            margin: 0 auto;
            padding: 0.5in;
        }
        
        .header {
            text-align: center;
            border-bottom: 3px solid #1e293b;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }
        
        .logo-icon {
            width: 40px;
            height: 40px;
            background: #dc2626;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }
        
        .logo-text {
            text-align: left;
        }
        
        .logo-title {
            font-size: 24px;
            font-weight: bold;
            color: #1e293b;
        }
        
        .logo-subtitle {
            font-size: 12px;
            color: #64748b;
        }
        
        .main-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            align-items: start;
        }
        
        .qr-section {
            text-align: center;
        }
        
        .qr-code {
            background: white;
            padding: 20px;
            border: 3px solid #e2e8f0;
            border-radius: 12px;
            margin-bottom: 20px;
            display: inline-block;
        }
        
        .qr-code svg {
            width: 280px;
            height: 280px;
            display: block;
        }
        
        .registration-code {
            background: #f8fafc;
            border: 2px dashed #cbd5e1;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
        }
        
        .registration-code-label {
            font-size: 12px;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }
        
        .registration-code-value {
            font-size: 28px;
            font-family: 'Courier New', monospace;
            font-weight: bold;
            color: #dc2626;
            letter-spacing: 2px;
        }
        
        .details-section {
            padding-left: 20px;
        }
        
        .event-title {
            font-size: 22px;
            font-weight: bold;
            color: #1e293b;
            margin-bottom: 20px;
            line-height: 1.3;
        }
        
        .details-grid {
            display: grid;
            gap: 15px;
        }
        
        .detail-item {
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 10px;
        }
        
        .detail-label {
            font-size: 11px;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }
        
        .detail-value {
            font-size: 14px;
            color: #1e293b;
            font-weight: 500;
        }
        
        .instructions {
            grid-column: 1 / -1;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 20px;
            margin-top: 30px;
        }
        
        .instructions-title {
            font-size: 16px;
            font-weight: bold;
            color: #1e293b;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
        
        .instructions-list {
            list-style: none;
            counter-reset: step-counter;
        }
        
        .instructions-list li {
            counter-increment: step-counter;
            margin-bottom: 10px;
            padding-left: 30px;
            position: relative;
            font-size: 13px;
            line-height: 1.5;
        }
        
        .instructions-list li::before {
            content: counter(step-counter);
            position: absolute;
            left: 0;
            top: 0;
            width: 20px;
            height: 20px;
            background: #3b82f6;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: bold;
        }
        
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            font-size: 11px;
            color: #64748b;
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            background: #dcfce7;
            color: #166534;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        /* Print-specific styles */
        @media print {
            body {
                background: white !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            .print-container {
                padding: 0.25in;
            }
            
            .main-content {
                gap: 30px;
            }
            
            .qr-code {
                break-inside: avoid;
            }
            
            .instructions {
                break-inside: avoid;
            }
        }
        
        /* For smaller pages */
        @page {
            margin: 0.5in;
            size: letter;
        }
    </style>
</head>
<body>
    <div class="print-container">
        <!-- Header -->
        <div class="header">
            <div class="logo">
                <div class="logo-icon">
                    <svg width="24" height="24" fill="white" viewBox="0 0 24 24">
                        <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="logo-text">
                    <div class="logo-title">AirforceBES Events</div>
                    <div class="logo-subtitle">Air Force Life Cycle Management Center</div>
                </div>
            </div>
        </div>

        <div class="main-content">
            <!-- QR Code Section -->
            <div class="qr-section">
                <div class="status-badge">
                    ✓ Registration Confirmed
                </div>
                
                <div class="qr-code">
                    {!! $qr_code !!}
                </div>
                
                <div class="registration-code">
                    <div class="registration-code-label">Registration Code</div>
                    <div class="registration-code-value">{{ $registration->registration_code }}</div>
                </div>
                
                <p style="font-size: 12px; color: #64748b; margin-top: 10px;">
                    Use this code if QR scanning is not available
                </p>
            </div>

            <!-- Event Details Section -->
            <div class="details-section">
                <h1 class="event-title">{{ $event->title }}</h1>
                
                <div class="details-grid">
                    <div class="detail-item">
                        <div class="detail-label">Event Date</div>
                        <div class="detail-value">{{ $event->formatted_date_range }}</div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Event Time</div>
                        <div class="detail-value">{{ $event->formatted_time_range }}</div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Venue</div>
                        <div class="detail-value">{{ $event->venue }}</div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Attendee Name</div>
                        <div class="detail-value">{{ $user->name }}</div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Registration Date</div>
                        <div class="detail-value">{{ $registration->formatted_registration_date }}</div>
                    </div>
                    
                    @if($registration->isCheckedIn())
                        <div class="detail-item">
                            <div class="detail-label">Check-in Status</div>
                            <div class="detail-value" style="color: #166534;">✓ Checked In</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Check-in Instructions -->
        <div class="instructions">
            <h3 class="instructions-title">
                <svg width="20" height="20" fill="#3b82f6" viewBox="0 0 24 24" style="margin-right: 8px;">
                    <path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Check-in Instructions
            </h3>
            <ol class="instructions-list">
                <li>Arrive at the event venue at the scheduled time</li>
                <li>Locate the check-in station at the event entrance</li>
                <li>Show this QR code to event staff for scanning</li>
                <li>If QR scanning is unavailable, provide your registration code: <strong>{{ $registration->registration_code }}</strong></li>
                <li>Present a valid government-issued photo ID for verification</li>
                <li>Collect any event materials provided by staff</li>
            </ol>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>
                <strong>Air Force Life Cycle Management Center</strong><br>
                Wright-Patterson AFB, OH 45433<br>
                For event support: events@airforcebes.mil | (937) 255-1234
            </p>
            <p style="margin-top: 10px; font-size: 10px;">
                Generated on {{ $generated_at->format('F j, Y \a\t g:i A T') }}
            </p>
        </div>
    </div>

    <script>
        // Auto-print when page loads
        window.onload = function() {
            window.print();
        };
        
        // Close window after printing (if opened in new tab)
        window.onafterprint = function() {
            if (window.opener) {
                window.close();
            }
        };
    </script>
</body>
</html>
