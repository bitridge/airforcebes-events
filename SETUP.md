# AirforceBES Events - Setup Documentation

## What's Been Completed

### 1. Laravel Breeze Authentication Setup ✅
- Installed Laravel Breeze with Blade templates
- Configured authentication views and routes
- Set up Tailwind CSS for styling
- Generated application key

### 2. Basic Configuration ✅
- Updated app name to "AirforceBES Events"
- Configured timezone support via environment variables
- Set up proper application defaults

### 3. Role-Based Authentication System ✅
- Extended User model with roles: `admin` and `attendee`
- Added additional user fields: `phone`, `organization`
- Created AdminMiddleware for role-based access control
- Registered middleware in bootstrap/app.php

### 4. Event Management Structure ✅
- Created Models: `Event`, `Registration`, `CheckIn`
- Created Controllers: `EventController`, `RegistrationController`, `CheckInController`, `Admin\EventController`
- Created Services: `EventService`, `RegistrationService`, `CheckInService`
- Set up database migrations for all tables

### 5. Route Structure ✅
- Public routes: Event listing and details
- Authenticated routes: Registration management, check-in
- Admin routes: Admin dashboard, event management
- Proper middleware protection

### 6. Database Setup ✅
- Created migrations for users role extension
- Created migrations for events, registrations, check_ins tables
- Applied all migrations
- Created test users via seeders

## Test Accounts Created

### Admin User
- **Email**: admin@airforcebes.org
- **Password**: password
- **Role**: admin
- **Access**: Full admin dashboard and event management

### Test Attendee
- **Email**: attendee@example.com
- **Password**: password
- **Role**: attendee
- **Access**: Event registration and check-in

## Directory Structure Created

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── EventController.php
│   │   ├── RegistrationController.php
│   │   ├── CheckInController.php
│   │   └── Admin/
│   │       └── EventController.php
│   └── Middleware/
│       └── AdminMiddleware.php
├── Models/
│   ├── Event.php
│   ├── Registration.php
│   ├── CheckIn.php
│   └── User.php (extended)
└── Services/
    ├── EventService.php
    ├── RegistrationService.php
    └── CheckInService.php
```

## Next Steps

### Immediate Development Tasks
1. **Complete Model Relationships**: Define all Eloquent relationships in models
2. **Update Migrations**: Add proper fields to events, registrations, and check_ins tables
3. **Implement Controllers**: Add logic to all controller methods
4. **Create Views**: Design Blade templates for all functionality
5. **Add Validation**: Create form request classes for input validation

### Feature Implementation Priority
1. Event creation and management (admin)
2. Event listing and details (public)
3. User registration system
4. QR code generation for registrations
5. Check-in system (QR code + manual)
6. Admin dashboard with statistics
7. Email notifications
8. Reporting and exports

## Running the Application

1. **Serve the application**:
   ```bash
   php artisan serve
   ```

2. **Access URLs**:
   - Home: http://localhost:8000
   - Login: http://localhost:8000/login
   - Register: http://localhost:8000/register
   - Admin Dashboard: http://localhost:8000/admin/dashboard (admin only)

3. **Development commands**:
   ```bash
   # Watch for frontend changes
   npm run dev
   
   # Run migrations
   php artisan migrate
   
   # Run seeders
   php artisan db:seed
   ```

## Environment Configuration

Key environment variables that should be set:
- `APP_NAME="AirforceBES Events"`
- `APP_TIMEZONE="America/New_York"` (or appropriate timezone)
- Database configuration
- Mail configuration for notifications

## Security Features Implemented

- CSRF protection on all forms
- Role-based access control via middleware
- Proper password hashing
- Session management via Laravel Breeze
- Input validation structure ready

## Technology Stack

- **Backend**: Laravel 10
- **Frontend**: Blade templates with Tailwind CSS and Alpine.js
- **Authentication**: Laravel Breeze (extended)
- **Database**: MySQL (configured)
- **Build Tool**: Vite
- **Styling**: Tailwind CSS

The foundation is now ready for full event management system development!
