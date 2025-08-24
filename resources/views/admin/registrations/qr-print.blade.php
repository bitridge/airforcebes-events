<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code - {{ $registration->registration_code }} - {{ config('app.name') }}</title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none !important; }
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 20px;
            background: white;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            text-align: center;
        }
        
        .header {
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        
        .header h1 {
            margin: 0;
            color: #333;
            font-size: 24px;
        }
        
        .header p {
            margin: 10px 0 0 0;
            color: #666;
            font-size: 16px;
        }
        
        .qr-section {
            margin: 40px 0;
        }
        
        .qr-code {
            margin: 20px auto;
            padding: 20px;
            border: 2px solid #333;
            display: inline-block;
            background: white;
        }
        
        .registration-info {
            margin: 30px 0;
            text-align: left;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        
        .info-label {
            font-weight: bold;
            color: #333;
        }
        
        .info-value {
            color: #666;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #333;
            color: #666;
            font-size: 14px;
        }
        
        .print-button {
            background: #007bff;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            margin: 20px 0;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ config('app.name') }}</h1>
            <p>Registration QR Code</p>
        </div>
        
        <div class="qr-section">
            <h2>QR Code for Registration</h2>
            <div class="qr-code">
                @if($registration->qr_code_data)
                    {!! QrCode::format('svg')->size(200)->margin(1)->generate($registration->qr_code_data) !!}
                @else
                    <div style="width: 200px; height: 200px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; color: #666;">
                        No QR Code Available
                    </div>
                @endif
            </div>
        </div>
        
        <div class="registration-info">
            <div class="info-row">
                <span class="info-label">Registration Code:</span>
                <span class="info-value">{{ $registration->registration_code }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Attendee:</span>
                <span class="info-value">{{ $registration->user->name }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Email:</span>
                <span class="info-value">{{ $registration->user->email }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Event:</span>
                <span class="info-value">{{ $registration->event->title }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Event Date:</span>
                <span class="info-value">{{ $registration->event->start_date->format('M j, Y') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Venue:</span>
                <span class="info-value">{{ $registration->event->venue }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Registration Date:</span>
                <span class="info-value">{{ $registration->registration_date->format('M j, Y g:i A') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Status:</span>
                <span class="info-value">{{ ucfirst($registration->status) }}</span>
            </div>
        </div>
        
        <div class="footer">
            <p>Generated on {{ now()->format('M j, Y g:i A') }}</p>
            <p>This QR code is unique to this registration and should not be shared.</p>
        </div>
        
        <div class="no-print">
            <button class="print-button" onclick="window.print()">Print QR Code</button>
            <a href="{{ route('admin.registrations.show', $registration) }}" class="back-button">Back to Registration</a>
            <a href="{{ route('admin.registrations.index') }}" class="back-button">All Registrations</a>
        </div>
    </div>
</body>
</html>
