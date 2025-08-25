<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Card - {{ $event->title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .content {
            background: #f9fafb;
            padding: 30px;
            border-radius: 0 0 10px 10px;
        }
        .card {
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            padding: 25px;
            margin: 20px 0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .event-title {
            font-size: 24px;
            font-weight: bold;
            color: #dc2626;
            margin-bottom: 15px;
            text-align: center;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 8px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        .info-label {
            font-weight: bold;
            color: #6b7280;
        }
        .info-value {
            color: #374151;
        }
        .qr-section {
            text-align: center;
            margin: 25px 0;
            padding: 20px;
            background: #fef2f2;
            border-radius: 8px;
        }
        .qr-note {
            font-size: 14px;
            color: #6b7280;
            margin-top: 15px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding: 20px;
            color: #6b7280;
            font-size: 14px;
        }
        .button {
            display: inline-block;
            background: #dc2626;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            margin: 10px 5px;
        }
        .button:hover {
            background: #b91c1c;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🎫 Registration Card</h1>
        <p>Your registration has been approved!</p>
    </div>

    <div class="content">
        <div class="card">
            <div class="event-title">{{ $event->title }}</div>
            
            <div class="info-row">
                <span class="info-label">Registration Code:</span>
                <span class="info-value">{{ $registration->registration_code }}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Attendee:</span>
                <span class="info-value">{{ $user->name }}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Email:</span>
                <span class="info-value">{{ $user->email }}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Event Date:</span>
                <span class="info-value">{{ $event->start_date->format('F j, Y') }}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Time:</span>
                <span class="info-value">{{ $event->start_time }} - {{ $event->end_time }}</span>
            </div>
            
            <div class="info-row">
                <span class="info-label">Venue:</span>
                <span class="info-value">{{ $event->venue }}</span>
            </div>
            
            @if($registration->notes)
            <div class="info-row">
                <span class="info-label">Notes:</span>
                <span class="info-value">{{ $registration->notes }}</span>
            </div>
            @endif
        </div>

        <div class="qr-section">
            <h3>📱 Check-in QR Code</h3>
            <p>Your QR code is attached to this email. Present it at the event for quick check-in.</p>
            <div class="qr-note">
                <strong>Important:</strong> Keep this QR code safe and bring it with you to the event.
            </div>
        </div>

        <div style="text-align: center; margin: 25px 0;">
            <a href="{{ route('registrations.qr-view', $registration) }}" class="button">View QR Code Online</a>
            <a href="{{ route('registrations.qr-print', $registration) }}" class="button">Print Registration Card</a>
        </div>

        <div style="background: #f0f9ff; border: 1px solid #0ea5e9; border-radius: 8px; padding: 20px; margin: 20px 0;">
            <h4 style="color: #0369a1; margin-top: 0;">📋 Event Details</h4>
            <p style="margin-bottom: 10px;"><strong>Description:</strong> {{ Str::limit($event->description, 200) }}</p>
            @if($event->max_capacity)
            <p style="margin-bottom: 10px;"><strong>Capacity:</strong> {{ $event->max_capacity }} people</p>
            @endif
            @if($event->registration_deadline)
            <p style="margin-bottom: 0;"><strong>Registration Deadline:</strong> {{ $event->registration_deadline->format('F j, Y g:i A') }}</p>
            @endif
        </div>
    </div>

    <div class="footer">
        <p>Thank you for registering for this event!</p>
        <p>If you have any questions, please contact us at events@airforcebes.mil</p>
        <p><small>This is an automated message. Please do not reply to this email.</small></p>
    </div>
</body>
</html>
