<?php

namespace Database\Factories;

use App\Models\CheckIn;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CheckIn>
 */
class CheckInFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CheckIn::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'registration_id' => Registration::factory(),
            'checked_in_at' => now(),
            'checked_in_by' => User::factory(),
            'check_in_method' => $this->faker->randomElement(['qr', 'manual', 'id']),
            'notes' => $this->faker->optional(0.3)->sentence(),
        ];
    }

    /**
     * Indicate that the check-in was done via QR code.
     */
    public function viaQr(): static
    {
        return $this->state(fn (array $attributes) => [
            'check_in_method' => 'qr',
        ]);
    }

    /**
     * Indicate that the check-in was done manually.
     */
    public function viaManual(): static
    {
        return $this->state(fn (array $attributes) => [
            'check_in_method' => 'manual',
        ]);
    }

    /**
     * Indicate that the check-in was done via ID.
     */
    public function viaId(): static
    {
        return $this->state(fn (array $attributes) => [
            'check_in_method' => 'id',
        ]);
    }

    /**
     * Indicate that the check-in was done by a specific user.
     */
    public function byUser(User $user): static
{
        return $this->state(fn (array $attributes) => [
            'checked_in_by' => $user->id,
        ]);
    }

    /**
     * Indicate that the check-in was done for a specific registration.
     */
    public function forRegistration(Registration $registration): static
    {
        return $this->state(fn (array $attributes) => [
            'registration_id' => $registration->id,
        ]);
    }

    /**
     * Indicate that the check-in was done in the past.
     */
    public function past(): static
    {
        return $this->state(fn (array $attributes) => [
            'checked_in_at' => $this->faker->dateTimeBetween('-1 month', '-1 hour'),
        ]);
    }

    /**
     * Indicate that the check-in was done recently.
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'checked_in_at' => $this->faker->dateTimeBetween('-1 hour', 'now'),
        ]);
    }

    /**
     * Indicate that the check-in has notes.
     */
    public function withNotes(): static
    {
        return $this->state(fn (array $attributes) => [
            'notes' => $this->faker->sentence(),
        ]);
    }

    /**
     * Indicate that the check-in was done at a specific time.
     */
    public function atTime(string $time): static
    {
        return $this->state(fn (array $attributes) => [
            'checked_in_at' => $time,
        ]);
    }
}
