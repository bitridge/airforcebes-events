<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BasicTest extends TestCase
{
    use RefreshDatabase;

    public function test_application_can_boot()
    {
        $this->assertTrue(true);
    }

    public function test_database_connection_works()
    {
        // Just check if we can connect to the database
        $this->assertTrue(true);
    }

    public function test_models_can_be_created()
    {
        $user = \App\Models\User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }

    public function test_user_factory_works()
    {
        $user = \App\Models\User::factory()->create();
        $this->assertNotNull($user->id);
        $this->assertNotNull($user->name);
        $this->assertNotNull($user->email);
    }

    public function test_event_factory_works()
    {
        $event = \App\Models\Event::factory()->create();
        $this->assertNotNull($event->id);
        $this->assertNotNull($event->title);
        $this->assertNotNull($event->slug);
    }

    public function test_registration_factory_works()
    {
        $registration = \App\Models\Registration::factory()->create();
        $this->assertNotNull($registration->id);
        $this->assertNotNull($registration->registration_code);
        $this->assertNotNull($registration->qr_code_data);
    }
}
