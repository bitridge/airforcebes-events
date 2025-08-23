<?php

namespace Database\Factories;

use App\Models\Waitlist;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Waitlist>
 */
class WaitlistFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Waitlist::class;

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
            'status' => 'waiting',
            'joined_at' => now(),
            'notified_at' => null,
            'notes' => $this->faker->optional(0.3)->sentence(),
        ];
    }

    /**
     * Indicate that the waitlist entry is waiting.
     */
    public function waiting(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'waiting',
            'notified_at' => null,
        ]);
    }

    /**
     * Indicate that the waitlist entry has been notified.
     */
    public function notified(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'notified',
            'notified_at' => now(),
        ]);
    }

    /**
     * Indicate that the waitlist entry has been registered.
     */
    public function registered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'registered',
            'notified_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    /**
     * Indicate that the waitlist entry has been cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }

    /**
     * Indicate that the waitlist entry is for a specific event.
     */
    public function forEvent(Event $event): static
    {
        return $this->state(fn (array $attributes) => [
            'event_id' => $event->id,
        ]);
    }

    /**
     * Indicate that the waitlist entry is by a specific user.
     */
    public function byUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Indicate that the waitlist entry was joined in the past.
     */
    public function joinedPast(): static
    {
        return $this->state(fn (array $attributes) => [
            'joined_at' => $this->faker->dateTimeBetween('-1 month', '-1 day'),
        ]);
    }

    /**
     * Indicate that the waitlist entry was joined recently.
     */
    public function joinedRecently(): static
    {
        return $this->state(fn (array $attributes) => [
            'joined_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    /**
     * Indicate that the waitlist entry has notes.
     */
    public function withNotes(): static
    {
        return $this->state(fn (array $attributes) => [
            'notes' => $this->faker->sentence(),
        ]);
    }

    /**
     * Indicate that the waitlist entry was notified at a specific time.
     */
    public function notifiedAt(string $time): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'notified',
            'notified_at' => $time,
        ]);
    }

    /**
     * Indicate that the waitlist entry was joined at a specific time.
     */
    public function joinedAt(string $time): static
    {
        return $this->state(fn (array $attributes) => [
            'joined_at' => $time,
        ]);
    }
}
