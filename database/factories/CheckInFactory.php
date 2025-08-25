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
            'check_in_method' => fake()->randomElement(['qr', 'manual', 'id']),
        ];
    }

    /**
     * Indicate that the check-in was done via QR code.
     */
    public function qrCode(): static
    {
        return $this->state(fn (array $attributes) => [
            'check_in_method' => 'qr',
        ]);
    }

    /**
     * Indicate that the check-in was done manually.
     */
    public function manual(): static
    {
        return $this->state(fn (array $attributes) => [
            'check_in_method' => 'manual',
        ]);
    }

    /**
     * Indicate that the check-in was done via ID.
     */
    public function id(): static
    {
        return $this->state(fn (array $attributes) => [
            'check_in_method' => 'id',
        ]);
    }

    /**
     * Indicate that the check-in has notes.
     */
    public function withNotes(): static
    {
        return $this->state(fn (array $attributes) => [
            'notes' => fake()->sentence(),
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
