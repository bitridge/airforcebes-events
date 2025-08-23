# Event Registration System Documentation

## Overview

This document describes the comprehensive event registration functionality for the AirforceBES Events management system. The system includes QR code generation, email confirmations, user management, and comprehensive validation.

---

## 🎯 System Features

### Core Registration Features
- ✅ **User Authentication Required**: Login/redirect flow for guests
- ✅ **Event Validation**: Capacity, deadline, and status checks
- ✅ **Unique Registration Codes**: 8-character alphanumeric codes
- ✅ **QR Code Generation**: SVG format with comprehensive data
- ✅ **Email Confirmations**: Professional templates with attachments
- ✅ **Registration Management**: View, download, and cancel functionality
- ✅ **Duplicate Prevention**: One registration per user per event
- ✅ **Real-time Validation**: Comprehensive error handling

---

## 📋 Registration Process Flow

### 1. User Authentication Check
```php
// Route protection and redirect
Route::middleware('auth')->group(function () {
    Route::post('/events/{event}/register', [RegistrationController::class, 'store']);
});
```

### 2. Event Eligibility Validation
**File:** `app/Http/Requests/StoreRegistrationRequest.php`

**Validation Checks:**
- Event exists and is published
- Event capacity not exceeded
- Registration deadline not passed
- Event has not started
- User not already registered
- User account is active

### 3. Registration Creation Process
**File:** `app/Http/Controllers/RegistrationController.php` → `store()`

**Steps:**
1. **Database Transaction Begin**
2. **Create Registration Record**
   - Generate unique 8-character code
   - Set status to 'confirmed'
   - Store registration timestamp
3. **Generate QR Code Data**
   - Event and user information
   - Check-in URL with code
   - Registration metadata
4. **Create QR Code Image**
   - 300x300px SVG format
   - Store in `storage/app/public/qr_codes/`
5. **Database Transaction Commit**
6. **Send Confirmation Email**
   - Professional HTML template
   - QR code attachment
   - Event details and instructions

### 4. Success/Error Handling
- **Success**: Redirect with confirmation message
- **Error**: Database rollback and error logging
- **Email Failure**: Log error but don't fail registration

---

## 🔐 Validation System

### Authorization Rules
**File:** `app/Http/Requests/StoreRegistrationRequest.php`

```php
public function authorize(): bool
{
    if (!auth()->check()) {
        return false; // Redirect to login
    }
    
    $event = $this->route('event');
    return $event && $event->canRegister(auth()->user());
}
```

### Validation Rules
```php
public function rules(): array
{
    return [
        'event_id' => ['required', 'exists:events,id'],
        'terms_accepted' => ['required', 'accepted'],
        'emergency_contact_name' => ['nullable', 'string', 'max:255'],
        'emergency_contact_phone' => ['nullable', 'string', 'max:20'],
        'dietary_requirements' => ['nullable', 'string', 'max:1000'],
        'special_accommodations' => ['nullable', 'string', 'max:1000'],
    ];
}
```

### Custom Validation Logic
**Advanced Checks:**
- Already registered prevention
- Event capacity validation
- Registration deadline enforcement
- Event status verification
- User eligibility confirmation

### Error Messages
**Custom Messages:**
- User-friendly error descriptions
- Specific failure reasons
- Actionable guidance
- Professional tone

---

## 📧 Email Confirmation System

### Email Template
**File:** `resources/views/emails/registration-confirmation.blade.php`

**Features:**
- **Professional Design**: Corporate branding with AF colors
- **Responsive Layout**: Mobile-friendly email design
- **Complete Event Details**: Date, time, venue, organizer
- **Registration Information**: Code, status, timestamp
- **Check-in Instructions**: Multiple check-in methods
- **QR Code Attachment**: Downloadable SVG file
- **Contact Information**: Support details and hours
- **Action Links**: View event, manage registrations

### Email Configuration
**File:** `app/Mail/RegistrationConfirmation.php`

**Settings:**
- **Queue Integration**: Implements `ShouldQueue` for performance
- **Dynamic Subject**: Includes event title
- **Professional Headers**: From AF domain with reply-to
- **Attachment Handling**: Auto-attaches QR code if available
- **Error Resilience**: Email failures don't break registration

### Email Content Structure
1. **Header Section**: Confirmation badge and AF branding
2. **Event Card**: Complete event information with icons
3. **Registration Details**: Code and status information
4. **Check-in Instructions**: Clear step-by-step guidance
5. **QR Code Information**: Attachment details and alternatives
6. **Important Notes**: Event-specific requirements and policies
7. **Contact Section**: Support information and hours
8. **Footer**: Legal information and additional links

---

## 📱 QR Code System

### QR Code Generation
**Package:** `simplesoftwareio/simple-qrcode`

**Configuration:**
```php
$qrCodeSvg = QrCode::size(300)
    ->format('svg')
    ->generate($registration->qr_code_data);
```

### QR Code Data Structure
```json
{
    "type": "event_registration",
    "registration_id": 123,
    "registration_code": "ABC12345",
    "event_id": 456,
    "user_id": 789,
    "event_title": "Event Name",
    "user_name": "John Doe",
    "registration_date": "2024-01-15T10:30:00Z",
    "check_in_url": "https://app.com/check-in?code=ABC12345"
}
```

### QR Code Storage
**Location:** `storage/app/public/qr_codes/`
**Filename:** `registration_{id}.svg`
**Format:** SVG (scalable, small file size)
**Size:** 300x300 pixels
**Cleanup:** Deleted when registration cancelled

### QR Code Security
- **Unique Data**: Registration-specific information
- **Tamper-Evident**: JSON structure with verification data
- **Time-Stamped**: Registration timestamp included
- **Event-Specific**: Tied to specific event and user

---

## 👤 User Registration Management

### My Registrations Page
**Route:** `GET /my-registrations`
**File:** `resources/views/registrations/index.blade.php`

**Features:**
- **Dashboard Statistics**: Total, confirmed, upcoming, attended counts
- **Registration Cards**: Comprehensive registration display
- **Status Indicators**: Visual status badges and icons
- **Action Buttons**: View event, download QR, cancel registration
- **Responsive Design**: Mobile-friendly card layout
- **Pagination**: Efficient handling of large registration lists

### Registration Card Information
**Each registration displays:**
- Event title and description
- Date, time, and venue
- Registration code and timestamp
- Status badges (confirmed, checked-in, cancelled)
- Event status (upcoming, past)
- Action buttons (contextual)

### Registration Actions
1. **View Event**: Link to event details page
2. **Download QR Code**: Direct SVG download
3. **Cancel Registration**: With confirmation dialog

### Empty State
- **Professional Design**: Encouraging message and clear action
- **Direct Links**: Browse events button
- **Helpful Guidance**: Next steps for new users

---

## 🚫 Registration Cancellation

### Cancellation Rules
**File:** `app/Models/Registration.php` → `canBeCancelled()`

**Conditions:**
- Registration status is 'confirmed'
- Event has not started
- User has not checked in
- Cancellation deadline not passed (if set)

### Cancellation Process
1. **Authorization Check**: User owns registration
2. **Eligibility Validation**: Can be cancelled
3. **Check-in Prevention**: Not already checked in
4. **Database Transaction**:
   - Update status to 'cancelled'
   - Delete QR code file
   - Log cancellation
5. **Success Feedback**: Confirmation message

### Cancellation Restrictions
- **After Check-in**: Cannot cancel after attending
- **Event Started**: No cancellation after start time
- **Invalid Status**: Only confirmed registrations
- **Owner Only**: Users can only cancel their own

---

## 🔄 QR Code Download System

### Download Functionality
**Route:** `GET /registrations/{registration}/qr-code`
**Method:** `downloadQrCode()`

**Process:**
1. **Authorization**: User owns registration
2. **Status Check**: Registration is confirmed
3. **File Existence**: Check if QR code exists
4. **Regeneration**: Create if missing
5. **Download**: Serve SVG file with proper headers

### File Handling
```php
return response($qrCodeContent)
    ->header('Content-Type', 'image/svg+xml')
    ->header('Content-Disposition', 'attachment; filename="registration_' . $code . '.svg"');
```

### Download Security
- **User Verification**: Only registration owner can download
- **Status Validation**: Only confirmed registrations
- **File Protection**: Proper content type and headers
- **Regeneration**: Auto-recreate missing files

---

## ⚠️ Error Handling & Edge Cases

### Registration Validation Errors
**Common Scenarios:**
- Event at capacity: Clear message with waiting list suggestion
- Registration deadline passed: Show deadline information
- Already registered: Link to existing registration
- Event not published: Redirect to events list
- User not authenticated: Redirect to login with return URL

### System Error Handling
**Database Failures:**
- Transaction rollback
- Error logging with context
- User-friendly error messages
- Graceful degradation

**Email Failures:**
- Registration still succeeds
- Error logged for admin review
- User informed of email issue
- Alternative contact provided

**File System Errors:**
- QR code regeneration on download
- Storage permission checks
- Cleanup on cancellation
- Error logging and monitoring

### Performance Considerations
**Database Optimization:**
- Eager loading relationships
- Indexed registration codes
- Efficient pagination
- Query optimization

**File Management:**
- SVG format for small file size
- Organized storage structure
- Automatic cleanup processes
- Caching strategies ready

---

## 🔐 Security Features

### Data Protection
- **Input Sanitization**: All user inputs validated and sanitized
- **SQL Injection Prevention**: Eloquent ORM protection
- **XSS Prevention**: Blade templating auto-escaping
- **CSRF Protection**: All forms include CSRF tokens

### Access Control
- **Authentication Required**: All registration actions require login
- **Authorization Checks**: Users can only access their own data
- **Event Validation**: Only published events accept registrations
- **Registration Ownership**: Strict ownership verification

### Privacy Protection
- **Personal Data**: Minimal data collection and storage
- **QR Code Security**: Registration-specific unique data
- **Email Privacy**: No sensitive data in email subject
- **File Access**: Proper authorization for downloads

---

## 📊 Analytics & Monitoring

### Registration Tracking
**Available Metrics:**
- Registration counts by event
- Registration success/failure rates
- Email delivery statistics
- QR code download counts
- Cancellation rates and reasons

### Error Monitoring
**Logged Events:**
- Registration failures with context
- Email delivery failures
- File system errors
- Validation failures
- Security violations

### Performance Metrics
**Database Performance:**
- Registration creation time
- Validation query performance
- Email queue processing time
- File generation speed

---

## 🚀 Future Enhancements

### Planned Features
- [ ] Waiting list functionality for full events
- [ ] Registration reminders via email/SMS
- [ ] Social media integration for sharing
- [ ] Calendar integration (iCal export)
- [ ] Mobile app for QR code storage
- [ ] Bulk registration for groups
- [ ] Registration analytics dashboard
- [ ] Custom registration fields per event

### Technical Improvements
- [ ] Redis caching for performance
- [ ] CDN integration for QR codes
- [ ] Advanced email templates
- [ ] SMS confirmation options
- [ ] API endpoints for mobile apps
- [ ] Real-time capacity updates
- [ ] Advanced reporting features

### User Experience Enhancements
- [ ] Progressive registration forms
- [ ] Social login integration
- [ ] Registration favorites/bookmarks
- [ ] Personal event calendars
- [ ] Registration sharing features
- [ ] Mobile-optimized QR codes
- [ ] Offline QR code storage

---

## 🧪 Testing Scenarios

### Registration Flow Testing
1. **Happy Path**: Complete registration with email confirmation
2. **Capacity Limits**: Registration when event is full
3. **Deadline Enforcement**: Registration after deadline
4. **Duplicate Prevention**: Multiple registration attempts
5. **Authentication Flow**: Guest user registration redirect

### Error Scenario Testing
1. **Database Failures**: Transaction rollback verification
2. **Email Failures**: Registration success with email error
3. **File System Issues**: QR code generation failures
4. **Network Issues**: Timeout handling and recovery

### Security Testing
1. **Authorization**: Access control verification
2. **Input Validation**: Malicious input handling
3. **CSRF Protection**: Form security verification
4. **File Access**: Unauthorized download prevention

---

## 📞 Support & Maintenance

### Admin Tools
- **Registration Management**: View and manage all registrations
- **Event Analytics**: Registration statistics and reports
- **Error Monitoring**: System health and error tracking
- **User Support**: Registration troubleshooting tools

### Maintenance Tasks
- **QR Code Cleanup**: Remove orphaned files
- **Email Queue Monitoring**: Ensure delivery success
- **Database Optimization**: Index maintenance and cleanup
- **Log Rotation**: Error and access log management

### User Support
- **Help Documentation**: Registration guide and FAQ
- **Email Support**: events@airforcebes.mil
- **Phone Support**: (937) 255-1234
- **Business Hours**: Monday-Friday, 8:00 AM - 5:00 PM EST

---

The registration system provides a complete, secure, and user-friendly event registration experience with professional email confirmations, QR code generation, and comprehensive management tools! 🎟️✨
