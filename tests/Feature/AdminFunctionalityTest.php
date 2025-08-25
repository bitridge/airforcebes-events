<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Event;
use App\Models\Registration;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AdminFunctionalityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /** @test */
    public function admin_can_access_dashboard()
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin);

        $response = $this->get('/admin');

        $response->assertStatus(200);
        $response->assertSee('Dashboard');
    }

    /** @test */
    public function admin_can_create_event()
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin);

        $eventData = [
            'title' => 'Test Event',
            'description' => 'Test Description',
            'slug' => 'test-event',
            'start_date' => now()->addDays(7)->format('Y-m-d'),
            'end_date' => now()->addDays(7)->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '17:00',
            'venue' => 'Test Venue',
            'max_capacity' => 100,
            'registration_deadline' => now()->addDays(6)->format('Y-m-d'),
            'status' => 'draft',
        ];

        $response = $this->post('/admin/events', $eventData);

        $response->assertRedirect();
        $this->assertDatabaseHas('events', [
            'title' => 'Test Event',
            'slug' => 'test-event',
        ]);
    }

    /** @test */
    public function admin_can_edit_event()
    {
        $admin = $this->createAdminUser();
        $event = $this->createEvent(['created_by' => $admin->id]);
        $this->actingAs($admin);

        $updatedData = [
            'title' => 'Updated Event Title',
            'description' => 'Updated Description',
            'venue' => 'Updated Venue',
        ];

        $response = $this->put("/admin/events/{$event->id}", $updatedData);

        $response->assertRedirect();
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'title' => 'Updated Event Title',
            'venue' => 'Updated Venue',
        ]);
    }

    /** @test */
    public function admin_can_delete_event()
    {
        $admin = $this->createAdminUser();
        $event = $this->createEvent(['created_by' => $admin->id]);
        $this->actingAs($admin);

        $response = $this->delete("/admin/events/{$event->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('events', ['id' => $event->id]);
    }

    /** @test */
    public function admin_can_view_event_registrations()
    {
        $admin = $this->createAdminUser();
        $event = $this->createEvent();
        $registration = $this->createRegistration(['event_id' => $event->id]);
        $this->actingAs($admin);

        $response = $this->get("/admin/events/{$event->id}/registrations");

        $response->assertStatus(200);
        $response->assertSee($registration->user->name);
    }

    /** @test */
    public function admin_can_export_registrations()
    {
        $admin = $this->createAdminUser();
        $event = $this->createEvent();
        $registration = $this->createRegistration(['event_id' => $event->id]);
        $this->actingAs($admin);

        $response = $this->get("/admin/events/{$event->id}/export");

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    /** @test */
    public function admin_can_manage_user_roles()
    {
        $admin = $this->createAdminUser();
        $user = $this->createUser();
        $this->actingAs($admin);

        $response = $this->put("/admin/users/{$user->id}", [
            'role' => 'admin',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'role' => 'admin',
        ]);
    }

    /** @test */
    public function admin_can_access_settings()
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin);

        $response = $this->get('/admin/settings');

        $response->assertStatus(200);
        $response->assertSee('Settings Management');
    }

    /** @test */
    public function admin_can_update_general_settings()
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin);

        $settingData = [
            'app.name' => 'Updated App Name',
            'app.description' => 'Updated Description',
        ];

        $response = $this->post('/admin/settings/general', [
            'settings' => $settingData,
        ]);

        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('settings', [
            'key' => 'app.name',
            'value' => 'Updated App Name',
        ]);
    }

    /** @test */
    public function admin_can_upload_logo()
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin);

        $file = UploadedFile::fake()->image('logo.png', 200, 200);

        $response = $this->post('/admin/settings/general', [
            'settings' => [
                'app.logo' => $file,
            ],
        ]);

        $response->assertJson(['success' => true]);
        
        $setting = Setting::where('key', 'app.logo')->first();
        $this->assertNotNull($setting->value);
        Storage::disk('public')->assertExists($setting->value);
    }

    /** @test */
    public function admin_can_update_appearance_settings()
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin);

        $appearanceData = [
            'appearance.primary_color' => '#ff0000',
            'appearance.secondary_color' => '#00ff00',
            'appearance.theme' => 'dark',
        ];

        $response = $this->post('/admin/settings/appearance', [
            'settings' => $appearanceData,
        ]);

        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('settings', [
            'key' => 'appearance.primary_color',
            'value' => '#ff0000',
        ]);
    }

    /** @test */
    public function admin_can_test_smtp_settings()
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin);

        $smtpData = [
            'mail.smtp_host' => 'smtp.gmail.com',
            'mail.smtp_port' => '587',
            'mail.smtp_username' => 'test@example.com',
            'mail.smtp_password' => 'password123',
            'mail.smtp_encryption' => 'tls',
        ];

        $response = $this->post('/admin/settings/smtp', [
            'settings' => $smtpData,
        ]);

        $response->assertJson(['success' => true]);
        
        // Test SMTP connection
        $testResponse = $this->post('/admin/settings/test-smtp', [
            'smtp_settings' => [
                'test_email' => 'test@example.com',
            ],
        ]);

        $testResponse->assertJson(['success' => true]);
    }

    /** @test */
    public function admin_can_view_reports()
    {
        $admin = $this->createAdminUser();
        $event = $this->createEvent();
        $registration = $this->createRegistration(['event_id' => $event->id]);
        $this->actingAs($admin);

        $response = $this->get('/admin/reports');

        $response->assertStatus(200);
        $response->assertSee('Reports & Analytics');
    }

    /** @test */
    public function admin_can_generate_event_report()
    {
        $admin = $this->createAdminUser();
        $event = $this->createEvent();
        $registration = $this->createRegistration(['event_id' => $event->id]);
        $this->actingAs($admin);

        $response = $this->post('/admin/reports/event', [
            'event_id' => $event->id,
            'report_type' => 'registrations',
        ]);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    /** @test */
    public function admin_can_bulk_update_registrations()
    {
        $admin = $this->createAdminUser();
        $event = $this->createEvent();
        
        // Create multiple registrations
        $registrations = [];
        for ($i = 0; $i < 3; $i++) {
            $user = $this->createUser();
            $registration = $this->createRegistration([
                'user_id' => $user->id,
                'event_id' => $event->id,
                'status' => 'pending',
            ]);
            $registrations[] = $registration;
        }

        $this->actingAs($admin);

        $registrationIds = collect($registrations)->pluck('id')->toArray();

        $response = $this->post('/admin/registrations/bulk-update', [
            'registration_ids' => $registrationIds,
            'action' => 'confirm',
        ]);

        $response->assertJson(['success' => true]);
        
        foreach ($registrations as $registration) {
            $this->assertDatabaseHas('registrations', [
                'id' => $registration->id,
                'status' => 'confirmed',
            ]);
        }
    }

    /** @test */
    public function admin_can_duplicate_event()
    {
        $admin = $this->createAdminUser();
        $event = $this->createEvent(['created_by' => $admin->id]);
        $this->actingAs($admin);

        $response = $this->post("/admin/events/{$event->id}/duplicate");

        $response->assertRedirect();
        
        $duplicatedEvent = Event::where('title', $event->title . ' (Copy)')->first();
        $this->assertNotNull($duplicatedEvent);
        $this->assertEquals($event->description, $duplicatedEvent->description);
        $this->assertEquals($event->venue, $duplicatedEvent->venue);
    }

    /** @test */
    public function admin_can_manage_event_categories()
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin);

        $categoryData = [
            'name' => 'Test Category',
            'description' => 'Test Category Description',
            'color' => '#ff0000',
        ];

        $response = $this->post('/admin/categories', $categoryData);

        $response->assertRedirect();
        $this->assertDatabaseHas('event_categories', [
            'name' => 'Test Category',
        ]);
    }

    /** @test */
    public function admin_can_view_analytics()
    {
        $admin = $this->createAdminUser();
        $event = $this->createEvent();
        $registration = $this->createRegistration(['event_id' => $event->id]);
        $this->actingAs($admin);

        $response = $this->get('/admin/analytics');

        $response->assertStatus(200);
        $response->assertSee('Analytics Dashboard');
    }

    /** @test */
    public function admin_can_export_attendee_list()
    {
        $admin = $this->createAdminUser();
        $event = $this->createEvent();
        $registration = $this->createRegistration(['event_id' => $event->id]);
        $this->actingAs($admin);

        $response = $this->get("/admin/events/{$event->id}/attendees/export");

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    /** @test */
    public function admin_can_send_bulk_emails()
    {
        $admin = $this->createAdminUser();
        $event = $this->createEvent();
        $registration = $this->createRegistration(['event_id' => $event->id]);
        $this->actingAs($admin);

        $emailData = [
            'subject' => 'Test Email',
            'message' => 'Test Message',
            'recipients' => 'all_registrants',
            'event_id' => $event->id,
        ];

        $response = $this->post('/admin/communications/send-email', $emailData);

        $response->assertJson(['success' => true]);
    }
}
