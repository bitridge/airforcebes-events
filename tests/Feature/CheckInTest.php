<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Event;
use App\Models\Registration;
use App\Models\CheckIn;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

class CheckInTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /** @test */
    public function admin_can_access_check_in_interface()
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin);

        $response = $this->get('/admin/check-in');

        $response->assertStatus(200);
        $response->assertSee('Check-in Station');
    }

    /** @test */
    public function non_admin_cannot_access_check_in_interface()
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $response = $this->get('/admin/check-in');

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_check_in_user_with_valid_registration()
    {
        $admin = $this->createAdminUser();
        $user = $this->createUser();
        $event = $this->createEvent();
        $registration = $this->createRegistration([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'status' => 'confirmed',
        ]);

        $this->actingAs($admin);

        $response = $this->post('/admin/check-in', [
            'registration_id' => $registration->id,
            'check_in_method' => 'manual',
        ]);

        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('check_ins', [
            'registration_id' => $registration->id,
            'checked_in_by' => $admin->id,
            'check_in_method' => 'manual',
        ]);
    }

    /** @test */
    public function admin_cannot_check_in_already_checked_in_user()
    {
        $admin = $this->createAdminUser();
        $user = $this->createUser();
        $event = $this->createEvent();
        $registration = $this->createRegistration([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'status' => 'confirmed',
        ]);

        // First check-in
        CheckIn::create([
            'registration_id' => $registration->id,
            'checked_in_by' => $admin->id,
            'check_in_method' => 'manual',
        ]);

        $this->actingAs($admin);

        // Second check-in attempt
        $response = $this->post('/admin/check-in', [
            'registration_id' => $registration->id,
            'check_in_method' => 'manual',
        ]);

        $response->assertJson(['success' => false]);
        $this->assertDatabaseCount('check_ins', 1);
    }

    /** @test */
    public function admin_can_check_in_user_by_registration_code()
    {
        $admin = $this->createAdminUser();
        $user = $this->createUser();
        $event = $this->createEvent();
        $registration = $this->createRegistration([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'status' => 'confirmed',
        ]);

        $this->actingAs($admin);

        $response = $this->post('/admin/check-in', [
            'registration_code' => $registration->registration_code,
            'check_in_method' => 'code',
        ]);

        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('check_ins', [
            'registration_id' => $registration->id,
            'checked_in_by' => $admin->id,
            'check_in_method' => 'code',
        ]);
    }

    /** @test */
    public function admin_can_check_in_user_by_email()
    {
        $admin = $this->createAdminUser();
        $user = $this->createUser();
        $event = $this->createEvent();
        $registration = $this->createRegistration([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'status' => 'confirmed',
        ]);

        $this->actingAs($admin);

        $response = $this->post('/admin/check-in', [
            'email' => $user->email,
            'event_id' => $event->id,
            'check_in_method' => 'email',
        ]);

        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('check_ins', [
            'registration_id' => $registration->id,
            'checked_in_by' => $admin->id,
            'check_in_method' => 'email',
        ]);
    }

    /** @test */
    public function check_in_fails_with_invalid_registration_code()
    {
        $admin = $this->createAdminUser();
        $this->actingAs($admin);

        $response = $this->post('/admin/check-in', [
            'registration_code' => 'INVALID123',
            'check_in_method' => 'code',
        ]);

        $response->assertJson(['success' => false]);
        $this->assertDatabaseCount('check_ins', 0);
    }

    /** @test */
    public function check_in_fails_with_cancelled_registration()
    {
        $admin = $this->createAdminUser();
        $user = $this->createUser();
        $event = $this->createEvent();
        $registration = $this->createRegistration([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'status' => 'cancelled',
        ]);

        $this->actingAs($admin);

        $response = $this->post('/admin/check-in', [
            'registration_id' => $registration->id,
            'check_in_method' => 'manual',
        ]);

        $response->assertJson(['success' => false]);
        $this->assertDatabaseCount('check_ins', 0);
    }

    /** @test */
    public function check_in_fails_with_pending_registration()
    {
        $admin = $this->createAdminUser();
        $user = $this->createUser();
        $event = $this->createEvent();
        $registration = $this->createRegistration([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'status' => 'pending',
        ]);

        $this->actingAs($admin);

        $response = $this->post('/admin/check-in', [
            'registration_id' => $registration->id,
            'check_in_method' => 'manual',
        ]);

        $response->assertJson(['success' => false]);
        $this->assertDatabaseCount('check_ins', 0);
    }

    /** @test */
    public function check_in_records_timestamp()
    {
        $admin = $this->createAdminUser();
        $user = $this->createUser();
        $event = $this->createEvent();
        $registration = $this->createRegistration([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'status' => 'confirmed',
        ]);

        $this->actingAs($admin);

        $checkInTime = now();
        $this->travelTo($checkInTime);

        $response = $this->post('/admin/check-in', [
            'registration_id' => $registration->id,
            'check_in_method' => 'manual',
        ]);

        $this->assertDatabaseHas('check_ins', [
            'registration_id' => $registration->id,
            'checked_in_at' => $checkInTime->toDateTimeString(),
        ]);

        $this->travel(0); // Reset time
    }

    /** @test */
    public function check_in_updates_registration_status()
    {
        $admin = $this->createAdminUser();
        $user = $this->createUser();
        $event = $this->createEvent();
        $registration = $this->createRegistration([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'status' => 'confirmed',
        ]);

        $this->actingAs($admin);

        $response = $this->post('/admin/check-in', [
            'registration_id' => $registration->id,
            'check_in_method' => 'manual',
        ]);

        $this->assertDatabaseHas('registrations', [
            'id' => $registration->id,
            'status' => 'checked_in',
        ]);
    }

    /** @test */
    public function bulk_check_in_works_with_multiple_registrations()
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
                'status' => 'confirmed',
            ]);
            $registrations[] = $registration;
        }

        $this->actingAs($admin);

        $registrationIds = collect($registrations)->pluck('id')->toArray();

        $response = $this->post('/admin/check-in/bulk', [
            'registration_ids' => $registrationIds,
            'check_in_method' => 'bulk',
        ]);

        $response->assertJson(['success' => true]);
        
        foreach ($registrations as $registration) {
            $this->assertDatabaseHas('check_ins', [
                'registration_id' => $registration->id,
                'checked_in_by' => $admin->id,
                'check_in_method' => 'bulk',
            ]);
        }
    }

    /** @test */
    public function check_in_interface_shows_registration_details()
    {
        $admin = $this->createAdminUser();
        $user = $this->createUser(['name' => 'John Doe']);
        $event = $this->createEvent(['title' => 'Test Event']);
        $registration = $this->createRegistration([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'status' => 'confirmed',
        ]);

        $this->actingAs($admin);

        $response = $this->get('/admin/check-in');

        $response->assertStatus(200);
        $response->assertSee('Check-in Station');
        $response->assertSee('QR Code Scanner');
        $response->assertSee('Manual Check-in');
    }

    /** @test */
    public function check_in_creates_audit_log()
    {
        $admin = $this->createAdminUser();
        $user = $this->createUser();
        $event = $this->createEvent();
        $registration = $this->createRegistration([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'status' => 'confirmed',
        ]);

        $this->actingAs($admin);

        $response = $this->post('/admin/check-in', [
            'registration_id' => $registration->id,
            'check_in_method' => 'manual',
            'notes' => 'Test check-in',
        ]);

        $this->assertDatabaseHas('check_ins', [
            'registration_id' => $registration->id,
            'checked_in_by' => $admin->id,
            'check_in_method' => 'manual',
            'notes' => 'Test check-in',
        ]);
    }
}
