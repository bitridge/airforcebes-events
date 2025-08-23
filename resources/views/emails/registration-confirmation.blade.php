<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Registration Confirmation</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8fafc;
        }
        .email-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
        }
        .header .subtitle {
            margin: 8px 0 0 0;
            font-size: 14px;
            opacity: 0.9;
        }
        .content {
            padding: 30px;
        }
        .success-badge {
            background: #10b981;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 20px;
        }
        .event-card {
            background: #f1f5f9;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #dc2626;
        }
        .event-title {
            font-size: 20px;
            font-weight: 700;
            color: #1e293b;
            margin: 0 0 12px 0;
        }
        .event-details {
            display: grid;
            gap: 8px;
        }
        .detail-row {
            display: flex;
            align-items: center;
            font-size: 14px;
            color: #64748b;
        }
        .detail-icon {
            width: 16px;
            height: 16px;
            margin-right: 8px;
            flex-shrink: 0;
        }
        .registration-info {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .reg-code {
            font-family: 'Courier New', monospace;
            font-size: 18px;
            font-weight: bold;
            color: #dc2626;
            background: white;
            padding: 8px 12px;
            border-radius: 4px;
            border: 2px dashed #dc2626;
            display: inline-block;
            margin: 8px 0;
        }
        .checkin-instructions {
            background: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .qr-info {
            text-align: center;
            background: #f8fafc;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .contact-info {
            background: #f1f5f9;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .footer {
            background: #1e293b;
            color: #94a3b8;
            padding: 20px 30px;
            text-align: center;
            font-size: 12px;
        }
        .footer a {
            color: #60a5fa;
            text-decoration: none;
        }
        .btn-primary {
            background: #dc2626;
            color: white;
            padding: 12px 24px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            margin: 10px 0;
        }
        @media (max-width: 600px) {
            body {
                padding: 10px;
            }
            .content {
                padding: 20px;
            }
            .header {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <h1>Registration Confirmed!</h1>
            <div class="subtitle">Air Force Life Cycle Management Center</div>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="success-badge">✓ Registration Successful</div>
            
            <p>Dear {{ $user->name }},</p>
            
            <p>Your registration for the following event has been confirmed:</p>

            <!-- Event Card -->
            <div class="event-card">
                <div class="event-title">{{ $event->title }}</div>
                <div class="event-details">
                    <div class="detail-row">
                        <svg class="detail-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <strong>Date:</strong>&nbsp;{{ $event->formatted_date_range }}
                    </div>
                    <div class="detail-row">
                        <svg class="detail-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <strong>Time:</strong>&nbsp;{{ $event->formatted_time_range }}
                    </div>
                    <div class="detail-row">
                        <svg class="detail-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <strong>Venue:</strong>&nbsp;{{ $event->venue }}
                    </div>
                    <div class="detail-row">
                        <svg class="detail-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <strong>Organizer:</strong>&nbsp;{{ $event->creator->name }}
                    </div>
                </div>
            </div>

            <!-- Registration Information -->
            <div class="registration-info">
                <h3 style="margin: 0 0 12px 0; color: #1e40af;">Your Registration Details</h3>
                <p style="margin: 8px 0;">
                    <strong>Registration Code:</strong><br>
                    <span class="reg-code">{{ $registration->registration_code }}</span>
                </p>
                <p style="margin: 8px 0; font-size: 14px; color: #64748b;">
                    <strong>Registered:</strong> {{ $registration->formatted_registration_date }}<br>
                    <strong>Status:</strong> Confirmed
                </p>
            </div>

            <!-- Check-in Instructions -->
            <div class="checkin-instructions">
                <h3 style="margin: 0 0 12px 0; color: #d97706;">📱 Check-in Instructions</h3>
                <p>On the day of the event, you can check in using one of these methods:</p>
                <ul style="margin: 8px 0; padding-left: 20px;">
                    <li><strong>QR Code:</strong> Show your QR code (attached) to event staff</li>
                    <li><strong>Registration Code:</strong> Provide your code: <strong>{{ $registration->registration_code }}</strong></li>
                    <li><strong>Self Check-in:</strong> Visit the check-in station and scan your QR code</li>
                </ul>
            </div>

            <!-- QR Code Information -->
            <div class="qr-info">
                <h3 style="margin: 0 0 12px 0;">📄 QR Code Attachment</h3>
                <p>Your personalized QR code is attached to this email. You can:</p>
                <ul style="text-align: left; display: inline-block; margin: 8px 0;">
                    <li>Save it to your phone</li>
                    <li>Print it for easy access</li>
                    <li>Show it directly from your email</li>
                </ul>
                <p style="font-size: 12px; color: #64748b; margin-top: 12px;">
                    Can't find the attachment? <a href="{{ route('registrations.index') }}" style="color: #dc2626;">Download it from your account</a>
                </p>
            </div>

            <!-- Action Button -->
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ route('events.show', $event->slug) }}" class="btn-primary">View Event Details</a>
            </div>

            <!-- Important Notes -->
            <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 15px; margin: 20px 0;">
                <h4 style="margin: 0 0 8px 0; color: #dc2626;">Important Notes:</h4>
                <ul style="margin: 0; padding-left: 20px; font-size: 14px;">
                    <li>Please arrive 15 minutes before the event start time</li>
                    <li>Bring a valid government ID for verification</li>
                    <li>Check-in closes 30 minutes after event start time</li>
                    @if($event->max_capacity)
                        <li>This event has limited capacity - please don't miss it!</li>
                    @endif
                    <li>Contact us if you need to cancel your registration</li>
                </ul>
            </div>

            <!-- Contact Information -->
            <div class="contact-info">
                <h3 style="margin: 0 0 12px 0;">Need Help?</h3>
                <p style="font-size: 14px; margin: 4px 0;">
                    <strong>Event Support:</strong> <a href="mailto:events@airforcebes.mil" style="color: #dc2626;">events@airforcebes.mil</a><br>
                    <strong>Phone:</strong> (937) 255-1234<br>
                    <strong>Hours:</strong> Monday - Friday, 8:00 AM - 5:00 PM EST
                </p>
                <p style="font-size: 12px; color: #64748b; margin-top: 12px;">
                    For technical issues with registration or check-in, please contact our support team at least 24 hours before the event.
                </p>
            </div>

            <p style="margin-top: 30px;">
                We look forward to seeing you at the event!<br><br>
                <strong>Air Force Life Cycle Management Center<br>
                Events Team</strong>
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p style="margin: 0;">
                Air Force Life Cycle Management Center<br>
                Wright-Patterson AFB, OH 45433<br>
                <a href="{{ route('events.index') }}">Browse More Events</a> | 
                <a href="{{ route('registrations.index') }}">Manage Registrations</a>
            </p>
            <p style="margin: 10px 0 0 0; font-size: 10px; color: #64748b;">
                This is an automated message. Please do not reply to this email.<br>
                For support, contact events@airforcebes.mil
            </p>
        </div>
    </div>
</body>
</html>
