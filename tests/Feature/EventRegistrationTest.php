<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Event;
use App\Models\Registration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

class EventRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    /** @test */
    public function user_can_register_for_event()
    {
        $user = $this->createUser();
        $event = $this->createEvent([
            'max_capacity' => 100,
            'registration_deadline' => now()->addDays(1),
        ]);

        $this->actingAs($user);

        $response = $this->post("/events/{$event->slug}/register");

        $response->assertRedirect();
        $this->assertDatabaseHas('registrations', [
            'user_id' => $user->id,
            'event_id' => $event->id,
            'status' => 'confirmed',
        ]);
    }

    /** @test */
    public function user_cannot_register_for_full_event()
    {
        $user = $this->createUser();
        $event = $this->createEvent([
            'max_capacity' => 1,
            'registration_deadline' => now()->addDays(1),
        ]);

        // Fill the event
        $this->createRegistration([
            'event_id' => $event->id,
            'status' => 'confirmed',
        ]);

        $this->actingAs($user);

        $response = $this->post("/events/{$event->slug}/register");

        $response->assertSessionHasErrors();
        $this->assertDatabaseMissing('registrations', [
            'user_id' => $user->id,
            'event_id' => $event->id,
        ]);
    }

    /** @test */
    public function user_cannot_register_for_past_deadline()
    {
        $user = $this->createUser();
        $event = $this->createEvent([
            'registration_deadline' => now()->subDays(1),
        ]);

        $this->actingAs($user);

        $response = $this->post("/events/{$event->slug}/register");

        $response->assertSessionHasErrors();
        $this->assertDatabaseMissing('registrations', [
            'user_id' => $user->id,
            'event_id' => $event->id,
        ]);
    }

    /** @test */
    public function user_cannot_register_twice_for_same_event()
    {
        $user = $this->createUser();
        $event = $this->createEvent();

        $this->actingAs($user);

        // First registration
        $this->post("/events/{$event->slug}/register");
        
        // Second registration attempt
        $response = $this->post("/events/{$event->slug}/register");

        $response->assertSessionHasErrors();
        $this->assertDatabaseCount('registrations', 1);
    }

    /** @test */
    public function user_cannot_register_for_draft_event()
    {
        $user = $this->createUser();
        $event = $this->createEvent(['status' => 'draft']);

        $this->actingAs($user);

        $response = $this->post("/events/{$event->slug}/register");

        $response->assertStatus(404);
        $this->assertDatabaseMissing('registrations', [
            'user_id' => $user->id,
            'event_id' => $event->id,
        ]);
    }

    /** @test */
    public function user_cannot_register_for_cancelled_event()
    {
        $user = $this->createUser();
        $event = $this->createEvent(['status' => 'cancelled']);

        $this->actingAs($user);

        $response = $this->post("/events/{$event->slug}/register");

        $response->assertSessionHasErrors();
        $this->assertDatabaseMissing('registrations', [
            'user_id' => $user->id,
            'event_id' => $event->id,
        ]);
    }

    /** @test */
    public function registration_generates_unique_code()
    {
        $user = $this->createUser();
        $event = $this->createEvent();

        $this->actingAs($user);

        $response = $this->post("/events/{$event->slug}/register");

        $registration = Registration::where('user_id', $user->id)
            ->where('event_id', $event->id)
            ->first();

        $this->assertNotNull($registration->registration_code);
        $this->assertGreaterThan(10, strlen($registration->registration_code));
    }

    /** @test */
    public function registration_creates_qr_code_data()
    {
        $user = $this->createUser();
        $event = $this->createEvent();

        $this->actingAs($user);

        $response = $this->post("/events/{$event->slug}/register");

        $registration = Registration::where('user_id', $user->id)
            ->where('event_id', $event->id)
            ->first();

        $this->assertNotNull($registration->qr_code_data);
        $this->assertStringContainsString($registration->id, $registration->qr_code_data);
    }

    /** @test */
    public function user_can_view_their_registrations()
    {
        $user = $this->createUser();
        $event = $this->createEvent();
        $registration = $this->createRegistration([
            'user_id' => $user->id,
            'event_id' => $event->id,
        ]);

        $this->actingAs($user);

        $response = $this->get('/my-registrations');

        $response->assertStatus(200);
        $response->assertSee($event->title);
    }

    /** @test */
    public function user_can_cancel_registration()
    {
        $user = $this->createUser();
        $event = $this->createEvent();
        $registration = $this->createRegistration([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'status' => 'confirmed',
        ]);

        $this->actingAs($user);

        $response = $this->delete("/registrations/{$registration->id}");

        $response->assertRedirect();
        $this->assertDatabaseHas('registrations', [
            'id' => $registration->id,
            'status' => 'cancelled',
        ]);
    }

    /** @test */
    public function user_cannot_cancel_others_registration()
    {
        $user1 = $this->createUser();
        $user2 = $this->createUser();
        $event = $this->createEvent();
        $registration = $this->createRegistration([
            'user_id' => $user1->id,
            'event_id' => $event->id,
        ]);

        $this->actingAs($user2);

        $response = $this->delete("/registrations/{$registration->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('registrations', [
            'id' => $registration->id,
            'status' => 'confirmed',
        ]);
    }

    /** @test */
    public function registration_shows_correct_capacity_status()
    {
        $event = $this->createEvent([
            'max_capacity' => 2,
        ]);

        // First registration
        $user1 = $this->createUser();
        $this->actingAs($user1);
        $this->post("/events/{$event->slug}/register");

        // Second registration
        $user2 = $this->createUser();
        $this->actingAs($user2);
        $this->post("/events/{$event->slug}/register");

        // Third registration should fail
        $user3 = $this->createUser();
        $this->actingAs($user3);
        $response = $this->post("/events/{$event->slug}/register");

        $response->assertSessionHasErrors();
        $this->assertDatabaseCount('registrations', 2);
    }

    /** @test */
    public function registration_deadline_enforcement()
    {
        $event = $this->createEvent([
            'registration_deadline' => now()->addMinutes(1),
        ]);

        $user = $this->createUser();
        $this->actingAs($user);

        // Registration should work before deadline
        $response = $this->post("/events/{$event->slug}/register");
        $response->assertRedirect();

        // Wait for deadline to pass
        $this->travel(2)->minutes();

        // Registration should fail after deadline
        $user2 = $this->createUser();
        $this->actingAs($user2);
        $response = $this->post("/events/{$event->slug}/register");
        $response->assertSessionHasErrors();

        $this->travel(0)->minutes(); // Reset time
    }
}
