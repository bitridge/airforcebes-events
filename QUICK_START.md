# AirforceBES Events - Quick Start Guide

## 🚀 Getting Started

Your AirforceBES Events management system is ready to use! Follow these steps to start the development server and begin using the application.

## 📋 Prerequisites

Ensure you have:
- ✅ PHP 8.1+
- ✅ Composer
- ✅ Node.js & NPM
- ✅ MySQL database
- ✅ Git

## ⚡ Quick Start Commands

### 1. Start the Development Server
```bash
# Start Laravel development server
php artisan serve

# In a separate terminal, start Vite for frontend assets
npm run dev
```

### 2. Access the Application
- **Main Site**: http://localhost:8000
- **Login**: http://localhost:8000/login
- **Register**: http://localhost:8000/register
- **Admin Dashboard**: http://localhost:8000/admin/dashboard

## 🔑 Test Accounts

### Admin Access
```
Email: admin@airforcebes.org
Password: password
Role: Full admin access
```

### Event Manager
```
Email: admin2@airforcebes.org
Password: password
Role: Admin access
```

### Attendee Access
```
Email: attendee@example.com
Password: password
Role: Standard user
```

## 🎯 What You Can Test Right Now

### As Admin (admin@airforcebes.org):
1. **Login** → Go to `/admin/dashboard`
2. **View Events** → Visit `/admin/events`
3. **Create Events** → Click "Create New Event"
4. **Manage Registrations** → View event registrations
5. **Check-in Users** → Process check-ins

### As Attendee (attendee@example.com):
1. **View Events** → Visit `/events`
2. **Register for Events** → Click on an event and register
3. **My Registrations** → View `/my-registrations`
4. **Check-in** → Use `/check-in` (if registered)

## 📊 Sample Data Available

The system comes pre-loaded with:
- **5 Users** (2 admins, 3 attendees)
- **5 Events** (4 published, 1 draft)
- **8 Registrations** across multiple events
- **2 Check-ins** for testing

### Sample Events Created:
1. **AirforceBES Annual Conference 2024**
2. **Cybersecurity in Defense Systems Workshop**
3. **Advanced Aircraft Technology Symposium**
4. **Leadership Development Retreat**
5. **Emergency Response Training** (draft)

## 🛠️ Development Commands

### Database Operations
```bash
# Fresh migration with sample data
php artisan migrate:fresh --seed

# Run migrations only
php artisan migrate

# Seed data only
php artisan db:seed

# Check database status
php artisan migrate:status
```

### Testing & Debugging
```bash
# Open Laravel Tinker (REPL)
php artisan tinker

# View routes
php artisan route:list

# Clear application cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Generate new app key
php artisan key:generate
```

### Asset Management
```bash
# Build assets for development
npm run dev

# Build assets for production
npm run build

# Watch for changes
npm run dev --watch
```

## 🗂️ Important Files to Know

### Configuration
- `.env` - Environment variables (database, app settings)
- `config/app.php` - Application configuration
- `routes/web.php` - All application routes

### Models (Business Logic)
- `app/Models/User.php` - User management with roles
- `app/Models/Event.php` - Event management
- `app/Models/Registration.php` - Registration handling
- `app/Models/CheckIn.php` - Check-in functionality

### Controllers (Request Handling)
- `app/Http/Controllers/EventController.php` - Public event operations
- `app/Http/Controllers/Admin/EventController.php` - Admin event management
- `app/Http/Controllers/RegistrationController.php` - Registration management
- `app/Http/Controllers/CheckInController.php` - Check-in operations

### Services (Business Logic)
- `app/Services/EventService.php` - Event business rules
- `app/Services/RegistrationService.php` - Registration logic
- `app/Services/CheckInService.php` - Check-in processing

## 🔧 Environment Configuration

Update your `.env` file as needed:

```env
APP_NAME="AirforceBES Events"
APP_ENV=local
APP_DEBUG=true
APP_TIMEZONE="America/New_York"

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=airforcebes_events
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

## 📱 Testing the System

### Test Registration Flow:
1. Login as attendee
2. Go to `/events`
3. Click on an event
4. Register for the event
5. Check your registration in `/my-registrations`

### Test Admin Flow:
1. Login as admin
2. Go to `/admin/dashboard`
3. Create a new event at `/admin/events/create`
4. View registrations for events
5. Process check-ins

### Test Check-in Flow:
1. Have a registration (from above)
2. Go to `/check-in`
3. Enter registration code
4. Verify check-in was recorded

## 🚨 Troubleshooting

### Common Issues:

**Database Connection Error:**
```bash
# Check your .env file database settings
# Ensure MySQL is running
# Verify database exists
```

**Asset Compilation Issues:**
```bash
# Clear npm cache
npm cache clean --force

# Reinstall dependencies
rm -rf node_modules package-lock.json
npm install

# Rebuild assets
npm run build
```

**Permission Issues:**
```bash
# Fix storage permissions
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
```

**Migration Issues:**
```bash
# Reset database completely
php artisan migrate:fresh --seed
```

## 📚 Next Steps

Now that the foundation is ready, you can:

1. **Customize Views** - Create beautiful Blade templates
2. **Add Validation** - Implement form request validation
3. **Email Integration** - Set up registration notifications
4. **QR Code Generation** - Add QR codes for registrations
5. **File Uploads** - Enable event image uploads
6. **Advanced Features** - Add waiting lists, payments, etc.

## 📖 Documentation

Refer to these files for detailed information:
- `SETUP.md` - Complete setup documentation
- `DATABASE_SCHEMA.md` - Database structure and relationships
- `IMPLEMENTATION_SUMMARY.md` - Complete feature overview

## 🎉 You're Ready!

Your AirforceBES Events management system foundation is complete and ready for development. All core functionality is implemented and tested.

**Happy Coding! 🚀**
