<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create super admin user
        $admin = \App\Models\User::firstOrCreate(
            ['email' => 'admin@airforcebes.org'],
            [
                'first_name' => 'Admin',
                'last_name' => 'User',
                'email' => 'admin@airforcebes.org',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'phone' => '+1-555-0100',
                'organization_name' => 'AirforceBES',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Create additional admin users
        \App\Models\User::firstOrCreate(
            ['email' => 'admin2@airforcebes.org'],
            [
                'first_name' => 'Event',
                'last_name' => 'Manager',
                'email' => 'admin2@airforcebes.org',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'phone' => '+1-555-0101',
                'organization_name' => 'AirforceBES',
                'is_active' => true,
                'created_by' => $admin->id,
                'email_verified_at' => now(),
            ]
        );

        // Create test attendees
        \App\Models\User::firstOrCreate(
            ['email' => 'attendee@example.com'],
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'attendee@example.com',
                'password' => bcrypt('password'),
                'role' => 'attendee',
                'phone' => '+1-555-0200',
                'organization_name' => 'Test Organization',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        \App\Models\User::firstOrCreate(
            ['email' => 'jane.smith@example.com'],
            [
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'email' => 'jane.smith@example.com',
                'password' => bcrypt('password'),
                'role' => 'attendee',
                'phone' => '+1-555-0201',
                'organization_name' => 'Corporate Partners',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        \App\Models\User::firstOrCreate(
            ['email' => 'mike.wilson@example.com'],
            [
                'first_name' => 'Mike',
                'last_name' => 'Wilson',
                'email' => 'mike.wilson@example.com',
                'password' => bcrypt('password'),
                'role' => 'attendee',
                'phone' => '+1-555-0202',
                'organization_name' => 'Tech Innovators',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
