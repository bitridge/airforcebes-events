<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Card - {{ $registration->registration_code }}</title>
    <style>
        @media print {
            body { 
                margin: 0; 
                padding: 0;
                font-size: 10px;
                line-height: 1.1;
            }
            .no-print { display: none !important; }
            .container { max-width: none; margin: 0; }
            .registration-card { 
                page-break-inside: avoid;
                box-shadow: none;
                border: 1px solid #000;
                margin: 0;
                max-height: 50vh;
                overflow: hidden;
            }
            .card-header { padding: 8px; }
            .card-header h2 { font-size: 14px; }
            .card-body { padding: 10px; }
            .card-content { gap: 10px; }
            .info-row { margin: 3px 0; padding: 2px 0; }
            .info-label { font-size: 8px; margin-bottom: 1px; }
            .info-value { font-size: 11px; }
            .qr-code { padding: 5px; }
            .qr-code img { width: 60px; height: 60px; }
            .qr-title { font-size: 9px; margin-bottom: 5px; }
            .qr-instructions { font-size: 7px; margin-top: 3px; }
        }
        
        * {
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 10px;
            background: #f5f5f5;
            font-size: 12px;
            line-height: 1.2;
        }
        
        .container {
            max-width: 500px;
            margin: 0 auto;
        }
        
        .registration-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 20px;
        }
        
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px;
            text-align: center;
        }
        
        .card-header h2 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
        }
        
        .card-body {
            padding: 15px;
        }
        
        .card-content {
            display: flex;
            gap: 15px;
            align-items: flex-start;
        }
        
        .attendee-info {
            flex: 1;
        }
        
        .qr-section {
            flex-shrink: 0;
            text-align: center;
        }
        
        .info-row {
            margin: 6px 0;
            padding: 4px 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: #495057;
            display: block;
            margin-bottom: 2px;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        
        .info-value {
            color: #212529;
            font-weight: 500;
            font-size: 13px;
        }
        
        .qr-code {
            display: inline-block;
            padding: 8px;
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            margin: 0 auto;
        }
        
        .qr-code img {
            width: 80px;
            height: 80px;
        }
        
        .qr-title {
            margin: 0 0 8px 0;
            color: #333;
            font-size: 11px;
            font-weight: 600;
        }
        
        .qr-instructions {
            margin-top: 6px;
            color: #666;
            font-size: 9px;
            max-width: 100px;
        }
        
        .print-button {
            background: #007bff;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            margin: 20px 10px;
        }
        
        .print-button:hover {
            background: #0056b3;
        }
        
        .back-button {
            background: #6c757d;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            margin: 20px 10px;
            text-decoration: none;
            display: inline-block;
        }
        
        .back-button:hover {
            background: #545b62;
        }
        
        @media (max-width: 600px) {
            .card-content {
                flex-direction: column;
                gap: 20px;
            }
            
            .qr-section {
                order: -1;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="registration-card">
            <div class="card-header">
                <h2>{{ $registration->event->title }}</h2>
            </div>
            
            <div class="card-body">
                <div class="card-content">
                    <div class="attendee-info">
                        <div class="info-row">
                            <span class="info-label">Attendee Name</span>
                            <span class="info-value">{{ $registration->user->name }}</span>
                        </div>
                        
                        @if($registration->user->organization)
                            <div class="info-row">
                                <span class="info-label">Organization Name</span>
                                <span class="info-value">{{ $registration->user->organization }}</span>
                            </div>
                        @endif
                        
                        <div class="info-row">
                            <span class="info-label">Registration Number</span>
                            <span class="info-value">{{ $registration->registration_code }}</span>
                        </div>
                        
                        @if($registration->user->email)
                            <div class="info-row">
                                <span class="info-label">Email</span>
                                <span class="info-value">{{ $registration->user->email }}</span>
                            </div>
                        @endif
                        
                        @if($registration->user->phone)
                            <div class="info-row">
                                <span class="info-label">Phone</span>
                                <span class="info-value">{{ $registration->user->phone }}</span>
                            </div>
                        @endif
                        
                        <div class="info-row">
                            <span class="info-label">Event Date</span>
                            <span class="info-value">{{ $registration->event->start_date->format('M j, Y') }}</span>
                        </div>
                        
                        @if($registration->event->start_time)
                            <div class="info-row">
                                <span class="info-label">Event Time</span>
                                <span class="info-value">
                                    {{ \Carbon\Carbon::createFromFormat('H:i', $registration->event->start_time)->format('g:i A') }}
                                    @if($registration->event->end_time)
                                        - {{ \Carbon\Carbon::createFromFormat('H:i', $registration->event->end_time)->format('g:i A') }}
                                    @endif
                                </span>
                            </div>
                        @endif
                        
                        <div class="info-row">
                            <span class="info-label">Venue</span>
                            <span class="info-value">{{ $registration->event->venue }}</span>
                        </div>
                    </div>
                    
                    <div class="qr-section">
                        <h3 class="qr-title">QR Code</h3>
                        <div class="qr-code">
                            @if($registration->qr_code_data)
                                {!! QrCode::format('svg')->size(80)->margin(1)->generate($registration->qr_code_data) !!}
                            @else
                                <div style="width: 80px; height: 80px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; color: #666; border-radius: 4px;">
                                    <div style="text-align: center;">
                                        <div style="font-size: 24px; margin-bottom: 3px;">📱</div>
                                        <div style="font-size: 8px;">No QR Code</div>
                                        <div style="font-size: 6px; margin-top: 1px;">Available</div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="qr-instructions">
                            Scan this QR code at the event entrance for check-in
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="no-print" style="text-align: center;">
            <button class="print-button" onclick="window.print()">Print Registration Card</button>
            <a href="{{ route('admin.registrations.show', $registration) }}" class="back-button">Back to Registration</a>
            <a href="{{ route('admin.registrations.index') }}" class="back-button">All Registrations</a>
        </div>
    </div>
</body>
</html>
