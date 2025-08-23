# AirforceBES Events - Database Schema Documentation

## Overview

This document describes the complete database schema for the AirforceBES Events management system. The schema is designed to support event creation, user registration, and check-in functionality with proper relationships and constraints.

## Tables

### 1. users (Extended)

The users table extends Laravel's default authentication table with additional fields for the event management system.

**Fields:**
```sql
id                  BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT
name                VARCHAR(255) NOT NULL
email               VARCHAR(255) NOT NULL UNIQUE
email_verified_at   TIMESTAMP NULL
password            VARCHAR(255) NOT NULL
role                ENUM('admin', 'attendee') DEFAULT 'attendee'
phone               VARCHAR(255) NULL
organization        VARCHAR(255) NULL
is_active           BOOLEAN DEFAULT TRUE
created_by          BIGINT UNSIGNED NULL
remember_token      VARCHAR(100) NULL
created_at          TIMESTAMP NULL
updated_at          TIMESTAMP NULL
```

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (email)
- INDEX (role)
- INDEX (is_active)
- INDEX (created_by)

**Foreign Keys:**
- created_by → users(id) ON DELETE SET NULL

**Relationships:**
- Self-referencing: creator() and createdUsers()
- Has many: registrations, createdEvents

---

### 2. events

Central table for storing event information.

**Fields:**
```sql
id                    BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT
title                 VARCHAR(255) NOT NULL
description           TEXT NOT NULL
slug                  VARCHAR(255) NOT NULL UNIQUE
start_date            DATE NOT NULL
end_date              DATE NOT NULL
start_time            TIME NOT NULL
end_time              TIME NOT NULL
venue                 VARCHAR(255) NOT NULL
max_capacity          INTEGER NULL
registration_deadline DATETIME NULL
status                ENUM('draft', 'published', 'completed', 'cancelled') DEFAULT 'draft'
featured_image        VARCHAR(255) NULL
created_by            BIGINT UNSIGNED NOT NULL
created_at            TIMESTAMP NULL
updated_at            TIMESTAMP NULL
```

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (slug)
- INDEX (start_date)
- INDEX (status)
- INDEX (created_by)
- COMPOSITE INDEX (status, start_date) -- for event listings

**Foreign Keys:**
- created_by → users(id) ON DELETE CASCADE

**Relationships:**
- Belongs to: creator (User)
- Has many: registrations, confirmedRegistrations

---

### 3. registrations

Stores user registrations for events with unique registration codes.

**Fields:**
```sql
id                BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT
event_id          BIGINT UNSIGNED NOT NULL
user_id           BIGINT UNSIGNED NOT NULL
registration_code VARCHAR(12) NOT NULL UNIQUE
qr_code_data      TEXT NULL
registration_date DATETIME NOT NULL
status            ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'confirmed'
created_at        TIMESTAMP NULL
updated_at        TIMESTAMP NULL
```

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (registration_code)
- UNIQUE (event_id, user_id) -- prevents duplicate registrations
- INDEX (event_id, user_id)
- INDEX (event_id)
- INDEX (user_id)
- INDEX (status)
- INDEX (registration_date)

**Foreign Keys:**
- event_id → events(id) ON DELETE CASCADE
- user_id → users(id) ON DELETE CASCADE

**Relationships:**
- Belongs to: event, user
- Has one: checkIn

---

### 4. check_ins

Records check-in information for registered attendees.

**Fields:**
```sql
id             BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT
registration_id BIGINT UNSIGNED NOT NULL UNIQUE
checked_in_at  DATETIME NOT NULL
checked_in_by  BIGINT UNSIGNED NULL
check_in_method ENUM('qr', 'manual', 'id') DEFAULT 'qr'
created_at     TIMESTAMP NULL
updated_at     TIMESTAMP NULL
```

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (registration_id) -- one check-in per registration
- INDEX (checked_in_at)
- INDEX (checked_in_by)
- INDEX (check_in_method)

**Foreign Keys:**
- registration_id → registrations(id) ON DELETE CASCADE
- checked_in_by → users(id) ON DELETE SET NULL

**Relationships:**
- Belongs to: registration, checkedInBy (User)
- Has one through: user, event

---

## Entity Relationship Diagram

```
┌─────────────┐       ┌─────────────┐       ┌─────────────┐       ┌─────────────┐
│    users    │       │   events    │       │registrations│       │  check_ins  │
├─────────────┤       ├─────────────┤       ├─────────────┤       ├─────────────┤
│ id (PK)     │◄──────┤ created_by  │       │ id (PK)     │       │ id (PK)     │
│ name        │       │ id (PK)     │◄──────┤ event_id    │◄──────┤registration_│
│ email       │       │ title       │       │ user_id     │       │ id (UK)     │
│ role        │       │ slug (UK)   │       │ reg_code(UK)│       │ checked_in_at│
│ phone       │   ┌───┤ status      │       │ status      │       │ checked_in_by│
│ organization│   │   │ ...         │       │ ...         │       │ method      │
│ is_active   │   │   └─────────────┘       └─────────────┘       └─────────────┘
│ created_by  │───┘                                │                       │
│ ...         │                                    │                       │
└─────────────┘                                    │                       │
       │                                           │                       │
       └───────────────────────────────────────────┘                       │
                                                                           │
       ┌───────────────────────────────────────────────────────────────────┘
       │
       ▼
┌─────────────┐
│    users    │ (checked_in_by)
│ id (PK)     │
└─────────────┘
```

**Legend:**
- PK = Primary Key
- UK = Unique Key
- FK = Foreign Key
- ◄── = One-to-Many relationship

---

## Key Features & Constraints

### 1. **User Roles & Access Control**
- `admin`: Full system access, can create/manage events
- `attendee`: Can register for events and check-in

### 2. **Event Status Management**
- `draft`: Not visible to public, admin-only
- `published`: Open for registration
- `completed`: Event finished
- `cancelled`: Event cancelled

### 3. **Registration Constraints**
- Unique registration per user per event
- Registration codes are unique across all events
- Capacity management with max_capacity field
- Registration deadline enforcement

### 4. **Check-in Methods**
- `qr`: QR code scan (automatic)
- `manual`: Admin manual check-in
- `id`: ID-based verification

### 5. **Data Integrity**
- Foreign key constraints ensure referential integrity
- Unique constraints prevent duplicate registrations
- Cascade deletes maintain data consistency
- Soft relationships (SET NULL) preserve historical data

---

## Sample Queries

### Get all published upcoming events
```sql
SELECT * FROM events 
WHERE status = 'published' 
AND start_date >= CURDATE() 
ORDER BY start_date ASC;
```

### Get registration statistics for an event
```sql
SELECT 
    e.title,
    e.max_capacity,
    COUNT(r.id) as total_registrations,
    COUNT(c.id) as checked_in_count,
    (COUNT(r.id) - COUNT(c.id)) as not_checked_in
FROM events e
LEFT JOIN registrations r ON e.id = r.event_id AND r.status = 'confirmed'
LEFT JOIN check_ins c ON r.id = c.registration_id
WHERE e.id = ?
GROUP BY e.id;
```

### Get user's registration history
```sql
SELECT 
    e.title,
    e.start_date,
    r.registration_code,
    r.status as registration_status,
    c.checked_in_at,
    c.check_in_method
FROM registrations r
JOIN events e ON r.event_id = e.id
LEFT JOIN check_ins c ON r.id = c.registration_id
WHERE r.user_id = ?
ORDER BY e.start_date DESC;
```

---

## Performance Considerations

### 1. **Indexes**
- Composite index on (status, start_date) for event listings
- Individual indexes on frequently queried fields
- Unique constraints also serve as indexes

### 2. **Query Optimization**
- Use eager loading for relationships in Laravel
- Consider pagination for large datasets
- Cache frequent queries (event listings)

### 3. **Data Volume Estimates**
- Users: 1,000-10,000 records
- Events: 100-1,000 per year
- Registrations: 10,000-100,000 per year
- Check-ins: 80% of registrations

---

## Security Considerations

### 1. **Data Protection**
- User passwords are hashed using Laravel's bcrypt
- Registration codes are randomly generated
- Email verification required for accounts

### 2. **Access Control**
- Role-based permissions enforced in application layer
- Admin middleware protects sensitive routes
- User can only access their own registration data

### 3. **Data Integrity**
- Foreign key constraints prevent orphaned records
- Unique constraints prevent duplicate registrations
- Status enums prevent invalid states

---

## Migration Notes

### Database Migrations Applied:
1. `0001_01_01_000000_create_users_table` - Laravel default users table
2. `0001_01_01_000001_create_cache_table` - Laravel cache table
3. `0001_01_01_000002_create_jobs_table` - Laravel jobs table
4. `2025_08_23_100451_create_events_table` - Events table with full schema
5. `2025_08_23_100451_create_registrations_table` - Registrations with constraints
6. `2025_08_23_100452_create_check_ins_table` - Check-ins table
7. `2025_08_23_100546_add_role_and_fields_to_users_table` - User extensions

### Seeded Data:
- 5 Users (2 admins, 3 attendees)
- 5 Events (4 published, 1 draft)
- 8 Registrations
- 2 Check-ins

---

## Future Enhancements

### Potential Schema Extensions:
1. **Event Categories**: Add event_categories table
2. **Waiting Lists**: Add waiting_list table for full events
3. **Event Sessions**: Add sessions table for multi-session events
4. **Notifications**: Add notifications table for email/SMS tracking
5. **Event Images**: Add event_images table for multiple images
6. **Registration Forms**: Add custom_fields table for additional data
7. **Payment Integration**: Add payments table for paid events
8. **Event Ratings**: Add ratings table for post-event feedback

This schema provides a solid foundation for the AirforceBES Events management system with room for future growth and enhancements.
