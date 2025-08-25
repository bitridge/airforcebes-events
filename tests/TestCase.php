<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Set up the test environment
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Disable foreign key checks for faster testing
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }
    }

    /**
     * Clean up after each test
     */
    protected function tearDown(): void
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
        
        parent::tearDown();
    }

    /**
     * Create a test user with specific role
     */
    protected function createUser(array $attributes = [], string $role = 'attendee')
    {
        $user = \App\Models\User::factory()->create(array_merge([
            'role' => $role,
            'email' => 'test-' . uniqid() . '@example.com',
        ], $attributes));

        return $user;
    }

    /**
     * Create an admin user
     */
    protected function createAdminUser(array $attributes = [])
    {
        return $this->createUser($attributes, 'admin');
    }

    /**
     * Create a test event
     */
    protected function createEvent(array $attributes = [])
    {
        return \App\Models\Event::factory()->create(array_merge([
            'status' => 'published',
            'start_date' => now()->addDays(7),
            'end_date' => now()->addDays(7)->addHours(3),
        ], $attributes));
    }

    /**
     * Create a test registration
     */
    protected function createRegistration(array $attributes = [])
    {
        return \App\Models\Registration::factory()->create($attributes);
    }

    /**
     * Assert that a model has the expected relationships
     */
    protected function assertModelHasRelationships($model, array $relationships)
    {
        foreach ($relationships as $relationship) {
            $this->assertTrue(method_exists($model, $relationship), 
                "Model does not have relationship: {$relationship}");
        }
    }

    /**
     * Assert that a model has the expected attributes
     */
    protected function assertModelHasAttributes($model, array $attributes)
    {
        foreach ($attributes as $attribute) {
            $this->assertTrue(property_exists($model, $attribute) || 
                             method_exists($model, 'get' . ucfirst($attribute) . 'Attribute'), 
                "Model does not have attribute: {$attribute}");
        }
    }
}
