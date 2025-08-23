<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = \App\Models\User::where('email', 'admin@airforcebes.org')->first();
        $attendees = \App\Models\User::where('role', 'attendee')->get();

        // Create upcoming events
        $event1 = \App\Models\Event::create([
            'title' => 'AirforceBES Annual Conference 2024',
            'description' => 'Join us for our annual conference featuring the latest in defense technology, innovation, and strategic planning. This comprehensive event brings together industry leaders, military personnel, and technology experts for three days of presentations, workshops, and networking opportunities.',
            'slug' => 'airforcebes-annual-conference-2024',
            'start_date' => now()->addMonths(2)->format('Y-m-d'),
            'end_date' => now()->addMonths(2)->addDays(2)->format('Y-m-d'),
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'venue' => 'Washington Convention Center, Washington D.C.',
            'max_capacity' => 500,
            'registration_deadline' => now()->addMonths(1)->addWeeks(3),
            'status' => 'published',
            'created_by' => $admin->id,
        ]);

        $event2 = \App\Models\Event::create([
            'title' => 'Cybersecurity in Defense Systems Workshop',
            'description' => 'A hands-on workshop focusing on cybersecurity best practices in defense systems. Participants will learn about threat assessment, vulnerability management, and implementation of security protocols in critical infrastructure.',
            'slug' => 'cybersecurity-defense-systems-workshop',
            'start_date' => now()->addWeeks(6)->format('Y-m-d'),
            'end_date' => now()->addWeeks(6)->format('Y-m-d'),
            'start_time' => '09:00:00',
            'end_time' => '16:00:00',
            'venue' => 'Pentagon Conference Center, Arlington, VA',
            'max_capacity' => 50,
            'registration_deadline' => now()->addWeeks(5),
            'status' => 'published',
            'created_by' => $admin->id,
        ]);

        $event3 = \App\Models\Event::create([
            'title' => 'Advanced Aircraft Technology Symposium',
            'description' => 'Explore the latest developments in aircraft technology, including unmanned systems, advanced materials, and next-generation propulsion systems. This technical symposium is designed for engineers and technical professionals.',
            'slug' => 'advanced-aircraft-technology-symposium',
            'start_date' => now()->addWeeks(8)->format('Y-m-d'),
            'end_date' => now()->addWeeks(8)->addDays(1)->format('Y-m-d'),
            'start_time' => '08:30:00',
            'end_time' => '17:30:00',
            'venue' => 'Edwards Air Force Base, California',
            'max_capacity' => 200,
            'registration_deadline' => now()->addWeeks(7),
            'status' => 'published',
            'created_by' => $admin->id,
        ]);

        $event4 = \App\Models\Event::create([
            'title' => 'Leadership Development Retreat',
            'description' => 'A three-day leadership development retreat for military and civilian leaders. Focus on strategic thinking, team building, and leadership in complex environments.',
            'slug' => 'leadership-development-retreat',
            'start_date' => now()->addMonths(3)->format('Y-m-d'),
            'end_date' => now()->addMonths(3)->addDays(2)->format('Y-m-d'),
            'start_time' => '08:00:00',
            'end_time' => '18:00:00',
            'venue' => 'Blue Ridge Mountains Resort, Virginia',
            'max_capacity' => 75,
            'registration_deadline' => now()->addMonths(2)->addWeeks(2),
            'status' => 'published',
            'created_by' => $admin->id,
        ]);

        // Draft event
        $event5 = \App\Models\Event::create([
            'title' => 'Emergency Response Training',
            'description' => 'Comprehensive emergency response training covering crisis management, disaster response, and emergency communications.',
            'slug' => 'emergency-response-training',
            'start_date' => now()->addMonths(4)->format('Y-m-d'),
            'end_date' => now()->addMonths(4)->format('Y-m-d'),
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'venue' => 'TBD',
            'max_capacity' => 100,
            'registration_deadline' => now()->addMonths(3)->addWeeks(2),
            'status' => 'draft',
            'created_by' => $admin->id,
        ]);

        // Create some registrations for the events
        if ($attendees->count() > 0) {
            // Register attendees for the conference
            foreach ($attendees->take(3) as $attendee) {
                $registration = \App\Models\Registration::create([
                    'event_id' => $event1->id,
                    'user_id' => $attendee->id,
                    'registration_code' => strtoupper(\Illuminate\Support\Str::random(8)),
                    'registration_date' => now()->subDays(rand(1, 30)),
                    'status' => 'confirmed',
                ]);

                // Check in some attendees
                if (rand(1, 3) === 1) {
                    \App\Models\CheckIn::create([
                        'registration_id' => $registration->id,
                        'checked_in_at' => $registration->registration_date->addDays(rand(1, 5)),
                        'check_in_method' => 'qr',
                    ]);
                }
            }

            // Register attendees for cybersecurity workshop
            foreach ($attendees->take(2) as $attendee) {
                \App\Models\Registration::create([
                    'event_id' => $event2->id,
                    'user_id' => $attendee->id,
                    'registration_code' => strtoupper(\Illuminate\Support\Str::random(8)),
                    'registration_date' => now()->subDays(rand(1, 15)),
                    'status' => 'confirmed',
                ]);
            }

            // Register attendees for aircraft symposium
            foreach ($attendees as $attendee) {
                \App\Models\Registration::create([
                    'event_id' => $event3->id,
                    'user_id' => $attendee->id,
                    'registration_code' => strtoupper(\Illuminate\Support\Str::random(8)),
                    'registration_date' => now()->subDays(rand(1, 20)),
                    'status' => 'confirmed',
                ]);
            }
        }

        \Illuminate\Support\Facades\Log::info('Event seeder completed', [
            'events_created' => 5,
            'registrations_created' => $attendees->count() * 3, // Approximate
        ]);
    }
}
