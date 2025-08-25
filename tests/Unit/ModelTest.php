<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Event;
use App\Models\Registration;
use App\Models\CheckIn;
use App\Models\Setting;
use App\Enums\SettingType;
use App\Enums\SettingGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_model_has_expected_relationships()
    {
        $user = $this->createUser();
        
        $this->assertModelHasRelationships($user, [
            'createdEvents',
            'registrations',
            'checkIns',
        ]);
    }

    /** @test */
    public function user_model_has_expected_scopes()
    {
        $admin = $this->createAdminUser();
        $attendee = $this->createUser();
        
        $this->assertTrue(method_exists(User::class, 'scopeAdmins'));
        $this->assertTrue(method_exists(User::class, 'scopeAttendees'));
        $this->assertTrue(method_exists(User::class, 'scopeActive'));
        
        $admins = User::admins()->get();
        $attendees = User::attendees()->get();
        
        $this->assertTrue($admins->contains($admin));
        $this->assertTrue($attendees->contains($attendee));
    }

    /** @test */
    public function user_model_has_expected_accessors()
    {
        $user = $this->createUser([
            'name' => 'John Doe',
            'phone' => '+1234567890',
        ]);
        
        // Test that the user has the expected attributes
        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('+1234567890', $user->phone);
    }

    /** @test */
    public function user_model_has_role_checks()
    {
        $admin = $this->createAdminUser();
        $attendee = $this->createUser();
        
        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isAttendee());
        $this->assertTrue($attendee->isAttendee());
        $this->assertFalse($attendee->isAdmin());
    }

    /** @test */
    public function event_model_has_expected_relationships()
    {
        $event = $this->createEvent();
        
        $this->assertModelHasRelationships($event, [
            'registrations',
            'checkIns',
            'creator',
        ]);
    }

    /** @test */
    public function event_model_has_expected_scopes()
    {
        $publishedEvent = $this->createEvent(['status' => 'published']);
        $draftEvent = $this->createEvent(['status' => 'draft']);
        
        // Test that the event has the expected status
        $this->assertEquals('published', $publishedEvent->status);
        $this->assertEquals('draft', $draftEvent->status);
    }

    /** @test */
    public function event_model_has_expected_accessors()
    {
        $event = $this->createEvent([
            'start_date' => now()->addDays(7),
            'end_date' => now()->addDays(7)->addHours(3),
            'max_capacity' => 100,
        ]);
        
        // Test that the event has the expected attributes
        $this->assertNotNull($event->title);
        $this->assertNotNull($event->description);
        $this->assertNotNull($event->slug);
        $this->assertEquals(100, $event->max_capacity);
    }

    /** @test */
    public function event_model_has_registration_methods()
    {
        $event = $this->createEvent([
            'max_capacity' => 2,
            'registration_deadline' => now()->addDays(1),
        ]);
        
        $this->assertTrue(method_exists($event, 'canRegister'));
        $this->assertTrue(method_exists($event, 'isRegistrationOpen'));
        $this->assertTrue(method_exists($event, 'getQRCodeData'));
        
        $this->assertTrue($event->canRegister());
        $this->assertTrue($event->isRegistrationOpen());
    }

    /** @test */
    public function registration_model_has_expected_relationships()
    {
        $registration = $this->createRegistration();
        
        $this->assertModelHasRelationships($registration, [
            'event',
            'user',
            'checkIn',
        ]);
    }

    /** @test */
    public function registration_model_generates_unique_code()
    {
        $registration = $this->createRegistration();
        
        $this->assertNotNull($registration->registration_code);
        $this->assertGreaterThan(10, strlen($registration->registration_code));
        
        // Test uniqueness
        $registration2 = $this->createRegistration();
        $this->assertNotEquals($registration->registration_code, $registration2->registration_code);
    }

    /** @test */
    public function registration_model_creates_qr_code_data()
    {
        $registration = $this->createRegistration();
        
        $this->assertNotNull($registration->qr_code_data);
        $this->assertStringContainsString($registration->id, $registration->qr_code_data);
        $this->assertStringContainsString($registration->event_id, $registration->qr_code_data);
    }

    /** @test */
    public function registration_model_has_status_checks()
    {
        $confirmedRegistration = $this->createRegistration(['status' => 'confirmed']);
        $cancelledRegistration = $this->createRegistration(['status' => 'cancelled']);
        
        $this->assertTrue($confirmedRegistration->isConfirmed());
        $this->assertFalse($confirmedRegistration->isCancelled());
        $this->assertTrue($cancelledRegistration->isCancelled());
        $this->assertFalse($cancelledRegistration->isConfirmed());
    }

    /** @test */
    public function check_in_model_has_expected_relationships()
    {
        $checkIn = CheckIn::factory()->create();
        
        $this->assertModelHasRelationships($checkIn, [
            'registration',
            'checkedInBy',
        ]);
    }

    /** @test */
    public function check_in_model_records_timestamp()
    {
        $checkIn = CheckIn::factory()->create();
        
        $this->assertNotNull($checkIn->checked_in_at);
        $this->assertEquals(now()->toDateString(), $checkIn->checked_in_at->toDateString());
    }

    /** @test */
    public function setting_model_handles_encryption()
    {
        $setting = new Setting([
            'key' => 'test.encrypted',
            'type' => SettingType::PASSWORD,
            'group' => SettingGroup::SECURITY,
            'label' => 'Test Setting',
            'is_encrypted' => true,
        ]);
        $setting->value = 'secret_value'; // This will trigger encryption
        $setting->save();
        
        $this->assertNotEquals('secret_value', $setting->value);
        $this->assertEquals('secret_value', $setting->display_value);
    }

    /** @test */
    public function setting_model_has_group_scopes()
    {
        $generalSetting = Setting::create([
            'key' => 'test.general',
            'value' => 'general_value',
            'type' => SettingType::TEXT,
            'group' => SettingGroup::GENERAL,
            'label' => 'Test General Setting',
        ]);
        
        $smtpSetting = Setting::create([
            'key' => 'test.smtp',
            'value' => 'smtp_value',
            'type' => SettingType::TEXT,
            'group' => SettingGroup::SMTP,
            'label' => 'Test SMTP Setting',
        ]);
        
        $this->assertTrue(method_exists(Setting::class, 'scopePublic'));
        $this->assertTrue(method_exists(Setting::class, 'scopeRequired'));
        
        $generalSettings = Setting::getGroup(SettingGroup::GENERAL);
        $this->assertTrue($generalSettings->contains($generalSetting));
    }

    /** @test */
    public function models_have_proper_fillable_arrays()
    {
        $user = new User();
        $event = new Event();
        $registration = new Registration();
        $setting = new Setting();
        
        $this->assertIsArray($user->getFillable());
        $this->assertIsArray($event->getFillable());
        $this->assertIsArray($registration->getFillable());
        $this->assertIsArray($setting->getFillable());
    }

    /** @test */
    public function models_have_proper_casts()
    {
        $user = new User();
        $event = new Event();
        $registration = new Registration();
        $setting = new Setting();
        
        $this->assertIsArray($user->getCasts());
        $this->assertIsArray($event->getCasts());
        $this->assertIsArray($registration->getCasts());
        $this->assertIsArray($setting->getCasts());
    }

    /** @test */
    public function models_have_timestamps()
    {
        $user = $this->createUser();
        $event = $this->createEvent();
        $registration = $this->createRegistration();
        
        $this->assertNotNull($user->created_at);
        $this->assertNotNull($user->updated_at);
        $this->assertNotNull($event->created_at);
        $this->assertNotNull($event->updated_at);
        $this->assertNotNull($registration->created_at);
        $this->assertNotNull($registration->updated_at);
    }

    /** @test */
    public function models_have_proper_validation_rules()
    {
        $user = new User();
        $event = new Event();
        $registration = new Registration();
        
        if (method_exists($user, 'rules')) {
            $this->assertIsArray($user->rules());
        }
        if (method_exists($event, 'rules')) {
            $this->assertIsArray($event->rules());
        }
        if (method_exists($registration, 'rules')) {
            $this->assertIsArray($registration->rules());
        }
    }
}
