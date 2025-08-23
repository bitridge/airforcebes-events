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
                'name' => 'Admin User',
                'email' => 'admin@airforcebes.org',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'phone' => '+1-555-0100',
                'organization' => 'AirforceBES',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Create additional admin users
        \App\Models\User::firstOrCreate(
            ['email' => 'admin2@airforcebes.org'],
            [
                'name' => 'Event Manager',
                'email' => 'admin2@airforcebes.org',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'phone' => '+1-555-0101',
                'organization' => 'AirforceBES',
                'is_active' => true,
                'created_by' => $admin->id,
                'email_verified_at' => now(),
            ]
        );

        // Create test attendees
        \App\Models\User::firstOrCreate(
            ['email' => 'attendee@example.com'],
            [
                'name' => 'John Doe',
                'email' => 'attendee@example.com',
                'password' => bcrypt('password'),
                'role' => 'attendee',
                'phone' => '+1-555-0200',
                'organization' => 'Test Organization',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        \App\Models\User::firstOrCreate(
            ['email' => 'jane.smith@example.com'],
            [
                'name' => 'Jane Smith',
                'email' => 'jane.smith@example.com',
                'password' => bcrypt('password'),
                'role' => 'attendee',
                'phone' => '+1-555-0201',
                'organization' => 'Corporate Partners',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        \App\Models\User::firstOrCreate(
            ['email' => 'mike.wilson@example.com'],
            [
                'name' => 'Mike Wilson',
                'email' => 'mike.wilson@example.com',
                'password' => bcrypt('password'),
                'role' => 'attendee',
                'phone' => '+1-555-0202',
                'organization' => 'Tech Innovators',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
