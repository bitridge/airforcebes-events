# Authentication System Documentation

## Overview

This document describes the comprehensive authentication system built on Laravel Breeze with extensive customizations for the AirforceBES Events management system. The system includes role-based authentication, enhanced registration, profile management, and mobile-responsive forms.

---

## 🔐 System Features

### Core Authentication Features
- ✅ **Extended Registration**: Phone, organization, and role selection
- ✅ **Role-based Authentication**: Admin and attendee roles
- ✅ **Custom Middleware**: AdminMiddleware and EventManagerMiddleware
- ✅ **Role-based Redirects**: Smart redirects after login/registration
- ✅ **Enhanced Password Reset**: Custom views with improved UX
- ✅ **Profile Management**: Complete user profile system
- ✅ **Mobile Responsive**: Tailwind CSS with responsive design
- ✅ **Account Security**: Active/inactive status management

---

## 👤 User Roles & Permissions

### Admin Role
**Capabilities:**
- Create and manage events
- View all registrations and check-ins
- Access admin dashboard
- Manage all users
- Perform manual check-ins
- View analytics and reports

**Default Redirect:** `/admin/dashboard`

### Attendee Role
**Capabilities:**
- Register for events
- View own registrations
- Self check-in via QR codes
- Manage own profile
- View public events

**Default Redirect:** `/events`

---

## 🛡️ Middleware System

### AdminMiddleware
**File:** `app/Http/Middleware/AdminMiddleware.php`

**Purpose:** Restricts access to admin-only routes

**Logic:**
```php
// Check authentication
if (!auth()->check()) return redirect()->route('login');

// Check admin role
if (!auth()->user()->isAdmin()) abort(403);
```

**Usage:**
```php
Route::middleware('admin')->group(function () {
    // Admin routes
});
```

### EventManagerMiddleware
**File:** `app/Http/Middleware/EventManagerMiddleware.php`

**Purpose:** Granular event management permissions

**Logic:**
- Admins can manage all events
- Users can only manage events they created
- Extensible for future permissions system

**Usage:**
```php
Route::middleware('event.manager')->group(function () {
    // Event management routes
});
```

---

## 📝 Registration System

### Extended Registration Form
**File:** `resources/views/auth/register.blade.php`

**Additional Fields:**
- **Phone Number** (optional): Formatted phone input with validation
- **Organization** (optional): User's company or organization
- **Role Selection** (required): Radio buttons for Admin/Attendee

**Validation Rules:**
```php
'name' => ['required', 'string', 'max:255', 'min:2'],
'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
'password' => ['required', 'confirmed', Rules\Password::defaults()],
'phone' => ['nullable', 'string', 'max:20', 'regex:/^[\+]?[0-9\s\-\(\)]+$/'],
'organization' => ['nullable', 'string', 'max:255'],
'role' => ['required', 'in:admin,attendee'],
```

**Features:**
- Mobile-responsive grid layout
- Clear role descriptions
- Real-time validation
- Phone number formatting hints
- Auto-focus and proper tab order

---

## 🔑 Login System

### Enhanced Login Flow
**File:** `resources/views/auth/login.blade.php`

**Features:**
- Clean, modern design
- Remember me functionality
- Forgot password link
- Account creation link
- Mobile-responsive layout

### Role-based Redirects
**File:** `app/Http/Controllers/Auth/AuthenticatedSessionController.php`

**Logic:**
```php
// Check if user is active
if (!$user->is_active) {
    // Logout and show error
}

// Role-based redirect
if ($user->isAdmin()) {
    return redirect()->route('admin.dashboard');
}
return redirect()->route('events.index');
```

---

## 🔄 Password Reset System

### Custom Password Reset Views

#### Forgot Password
**File:** `resources/views/auth/forgot-password.blade.php`
- Clear instructions
- Email input with validation
- Back to login link
- Mobile-responsive design

#### Reset Password
**File:** `resources/views/auth/reset-password.blade.php`
- Token-based validation
- Password confirmation
- Read-only email display
- Security guidelines

---

## 👤 Profile Management System

### Profile Overview
**Route:** `GET /profile`
**View:** `resources/views/profile/show.blade.php`

**Features:**
- User avatar with initials
- Contact information display
- Account statistics
- Upcoming events list
- Past events with attendance status
- Empty state for new users

### Profile Editing
**Route:** `GET /profile/edit`
**View:** `resources/views/profile/edit.blade.php`

**Sections:**
1. **Profile Information**
   - Name, email, phone, organization
   - Role display (read-only)
   - Email verification status

2. **Password Update**
   - Current password verification
   - New password with confirmation
   - Security requirements

3. **Account Deletion**
   - Password confirmation required
   - Prevents deletion with upcoming registrations
   - Deactivates instead of deleting for data integrity

### Profile Controller Methods
**File:** `app/Http/Controllers/ProfileController.php`

```php
public function show()        // Display profile overview
public function edit()        // Show edit form
public function update()      // Update profile information
public function updatePassword() // Change password
public function destroy()     // Deactivate account
```

---

## 🎨 UI/UX Design

### Design System
- **Framework:** Tailwind CSS
- **Color Scheme:** Indigo/Blue primary colors
- **Typography:** Figtree font family
- **Layout:** Mobile-first responsive design

### Form Components
- **Input Fields:** Consistent styling with focus states
- **Validation:** Real-time error display
- **Buttons:** Primary/secondary button styles
- **Grid Layout:** Responsive 1-2 column layouts
- **Cards:** Rounded corners with shadows

### Mobile Responsiveness
- **Breakpoints:** Mobile, tablet, desktop
- **Stack Layout:** Forms stack on mobile
- **Touch Targets:** Properly sized for touch
- **Typography:** Scales appropriately

---

## 🔒 Security Features

### Account Status Management
```php
// User model methods
$user->isActive()     // Check if account is active
$user->activate()     // Activate account
$user->deactivate()   // Deactivate account
```

### Password Requirements
- Minimum 8 characters
- Laravel's default password rules
- Confirmation required
- Current password verification for changes

### Session Management
- Automatic session regeneration
- Remember me functionality
- Proper logout handling
- CSRF protection on all forms

### Data Validation
- Server-side validation for all inputs
- Phone number format validation
- Email uniqueness checks
- Role-based access control

---

## 🚦 Routes Structure

### Public Routes
```php
GET  /                    # Welcome page
GET  /events             # Public events listing
GET  /events/{slug}      # Event details
```

### Authentication Routes
```php
GET  /login              # Login form
POST /login              # Process login
GET  /register           # Registration form
POST /register           # Process registration
GET  /forgot-password    # Password reset request
POST /forgot-password    # Send reset email
GET  /reset-password/{token} # Reset form
POST /reset-password     # Process reset
```

### User Profile Routes
```php
GET    /profile          # Profile overview
GET    /profile/edit     # Profile edit form
PATCH  /profile          # Update profile
PATCH  /profile/password # Update password
DELETE /profile          # Delete account
```

### Admin Routes
```php
GET /admin/dashboard     # Admin dashboard
```

---

## 🧪 Testing & Validation

### Registration Testing
1. **Valid Registration:**
   - All required fields filled
   - Valid email format
   - Strong password
   - Role selection

2. **Validation Testing:**
   - Empty required fields
   - Invalid email format
   - Weak password
   - Invalid phone format
   - Duplicate email

### Login Testing
1. **Valid Login:**
   - Correct credentials
   - Role-based redirect
   - Remember me function

2. **Invalid Login:**
   - Wrong password
   - Non-existent email
   - Inactive account

### Profile Testing
1. **Profile Updates:**
   - Information changes
   - Password changes
   - Validation errors

2. **Account Management:**
   - Account deletion
   - Upcoming registrations check

---

## 🔧 Configuration

### Environment Variables
```env
APP_NAME="AirforceBES Events"
APP_TIMEZONE="UTC"
```

### Middleware Registration
**File:** `bootstrap/app.php`
```php
$middleware->alias([
    'admin' => \App\Http\Middleware\AdminMiddleware::class,
    'event.manager' => \App\Http\Middleware\EventManagerMiddleware::class,
]);
```

---

## 📱 Mobile Experience

### Registration Form
- **Layout:** 2-column grid on desktop, stacked on mobile
- **Role Selection:** Clear radio buttons with descriptions
- **Input Fields:** Full-width with proper spacing
- **Validation:** Inline error messages

### Login Form
- **Layout:** Single column, full-width button
- **Links:** Properly sized touch targets
- **Remember Me:** Mobile-friendly checkbox

### Profile Management
- **Overview:** Card-based layout
- **Statistics:** Grid layout that stacks on mobile
- **Forms:** Responsive grid for inputs

---

## 🚀 Usage Examples

### Creating a New User (Registration)
1. Navigate to `/register`
2. Fill in required information
3. Select role (Admin/Attendee)
4. Submit form
5. Automatic login and role-based redirect

### User Login
1. Navigate to `/login`
2. Enter email and password
3. Optional: Check "Remember me"
4. Submit form
5. Redirect to appropriate dashboard

### Profile Management
1. Access profile via navigation
2. View overview at `/profile`
3. Edit information at `/profile/edit`
4. Update sections independently
5. Change password securely

### Password Reset
1. Click "Forgot password" on login
2. Enter email address
3. Check email for reset link
4. Follow link to reset form
5. Set new password

---

## 🎯 Best Practices Implemented

### Security
- ✅ CSRF protection on all forms
- ✅ Password hashing with bcrypt
- ✅ Input validation and sanitization
- ✅ Session regeneration
- ✅ Account status management

### User Experience
- ✅ Clear error messages
- ✅ Success feedback
- ✅ Loading states
- ✅ Mobile responsiveness
- ✅ Accessible form design

### Code Quality
- ✅ Separation of concerns
- ✅ Consistent naming conventions
- ✅ Proper error handling
- ✅ Documentation
- ✅ Laravel best practices

---

## 🔮 Future Enhancements

### Planned Features
- [ ] Two-factor authentication
- [ ] Social media login
- [ ] Email verification workflow
- [ ] Advanced permissions system
- [ ] User avatar uploads
- [ ] Account recovery via phone
- [ ] Activity logging
- [ ] Password strength meter

### Extension Points
- **Middleware:** Easily add new permission layers
- **Roles:** Extend role system with custom roles
- **Profile:** Add custom profile fields
- **Validation:** Implement custom validation rules
- **UI:** Theme customization system

---

The authentication system is production-ready with comprehensive security, excellent user experience, and extensible architecture! 🔐✨
