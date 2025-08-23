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
            'registration_code' => 'REG-' . strtoupper(Str::random(8)),
            'qr_code_data' => json_encode([
                'registration_id' => $this->faker->uuid(),
                'event_id' => $this->faker->uuid(),
                'security_hash' => Str::random(64),
                'expires_at' => now()->addDays(30)->toISOString(),
            ]),
            'registration_date' => now(),
            'status' => $this->faker->randomElement(['pending', 'confirmed', 'cancelled']),
            'notes' => $this->faker->optional(0.3)->sentence(),
            'custom_field_values' => null,
            'paid_amount' => null,
            'payment_status' => 'pending',
            'payment_date' => null,
            'payment_method' => null,
            'transaction_id' => null,
        ];
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
     * Indicate that the registration is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
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
     * Indicate that the registration is paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => 'paid',
            'paid_amount' => $this->faker->randomFloat(2, 20, 200),
            'payment_date' => now(),
            'payment_method' => $this->faker->randomElement(['credit_card', 'paypal', 'bank_transfer']),
            'transaction_id' => 'TXN-' . strtoupper(Str::random(12)),
        ]);
    }

    /**
     * Indicate that the registration has custom field values.
     */
    public function withCustomFields(): static
    {
        return $this->state(fn (array $attributes) => [
            'custom_field_values' => [
                'dietary_restrictions' => $this->faker->optional(0.3)->randomElement(['vegetarian', 'vegan', 'gluten_free', 'none']),
                'emergency_contact' => $this->faker->optional(0.7)->phoneNumber(),
                'special_requirements' => $this->faker->optional(0.2)->sentence(),
                'company' => $this->faker->optional(0.6)->company(),
                'job_title' => $this->faker->optional(0.5)->jobTitle(),
            ],
        ]);
    }

    /**
     * Indicate that the registration has notes.
     */
    public function withNotes(): static
    {
        return $this->state(fn (array $attributes) => [
            'notes' => $this->faker->sentence(),
        ]);
    }

    /**
     * Indicate that the registration is for a specific event.
     */
    public function forEvent(Event $event): static
    {
        return $this->state(fn (array $attributes) => [
            'event_id' => $event->id,
        ]);
    }

    /**
     * Indicate that the registration is by a specific user.
     */
    public function byUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Indicate that the registration has a specific status.
     */
    public function withStatus(string $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
        ]);
    }

    /**
     * Indicate that the registration has a specific payment status.
     */
    public function withPaymentStatus(string $paymentStatus): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => $paymentStatus,
        ]);
    }

    /**
     * Indicate that the registration was made in the past.
     */
    public function past(): static
    {
        return $this->state(fn (array $attributes) => [
            'registration_date' => $this->faker->dateTimeBetween('-6 months', '-1 day'),
        ]);
    }

    /**
     * Indicate that the registration was made recently.
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'registration_date' => $this->faker->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    /**
     * Indicate that the registration has been refunded.
     */
    public function refunded(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => 'refunded',
            'paid_amount' => $this->faker->randomFloat(2, 20, 200),
            'payment_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'payment_method' => $this->faker->randomElement(['credit_card', 'paypal', 'bank_transfer']),
            'transaction_id' => 'TXN-' . strtoupper(Str::random(12)),
        ]);
    }


}
