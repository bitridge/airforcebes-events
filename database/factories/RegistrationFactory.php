<?php

namespace Database\Factories;

use App\Models\Registration;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Registration>
 */
class RegistrationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Registration::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'user_id' => User::factory(),
            'registration_code' => 'REG-' . Str::random(8),
            'qr_code_data' => json_encode([
                'registration_id' => fake()->numberBetween(1, 1000),
                'event_id' => fake()->numberBetween(1, 100),
                'hash' => Str::random(32),
            ]),
            'registration_date' => now(),
            'status' => 'confirmed',
            'notes' => null,
            'custom_field_values' => null,
        ];
    }

    /**
     * Indicate that the registration is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    /**
     * Indicate that the registration is confirmed.
     */
    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'confirmed',
        ]);
    }

    /**
     * Indicate that the registration is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }

    /**
     * Indicate that the registration is checked in.
     */
    public function checkedIn(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'checked_in',
        ]);
    }

    /**
     * Indicate that the registration has notes.
     */
    public function withNotes(): static
    {
        return $this->state(fn (array $attributes) => [
            'notes' => fake()->sentence(),
        ]);
    }

    /**
     * Indicate that the registration has custom field values.
     */
    public function withCustomFields(): static
    {
        return $this->state(fn (array $attributes) => [
            'custom_field_values' => json_encode([
                'dietary_restrictions' => fake()->randomElement(['None', 'Vegetarian', 'Vegan', 'Gluten-free']),
                'accessibility_needs' => fake()->randomElement(['None', 'Wheelchair access', 'Hearing assistance', 'Visual assistance']),
                'emergency_contact' => fake()->name() . ' - ' . fake()->phoneNumber(),
            ]),
        ]);
    }
}
