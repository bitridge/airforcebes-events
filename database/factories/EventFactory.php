<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Event::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('now', '+2 months');
        $endDate = fake()->dateTimeBetween($startDate, '+3 months');
        
        return [
            'title' => fake()->sentence(3),
            'description' => fake()->paragraphs(3, true),
            'category_id' => null,
            'tags' => json_encode(fake()->words(3)),
            'series_id' => null,
            'series_order' => null,
            'slug' => fake()->unique()->slug(),
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'start_time' => fake()->time('H:i'),
            'end_time' => fake()->time('H:i'),
            'venue' => fake()->company() . ' Conference Center',
            'max_capacity' => fake()->numberBetween(50, 500),
            'has_waitlist' => false,
            'waitlist_capacity' => null,
            'early_bird_enabled' => false,
            'early_bird_deadline' => null,
            'early_bird_price' => null,
            'regular_price' => null,
            'requires_confirmation' => false,
            'confirmation_message' => null,
            'has_custom_fields' => false,
            'is_archived' => false,
            'archived_at' => null,
            'archived_by' => null,
            'registration_deadline' => fake()->dateTimeBetween('now', $startDate)->format('Y-m-d'),
            'status' => 'published',
            'featured_image' => null,
            'created_by' => User::factory(),
        ];
    }

    /**
     * Indicate that the event is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
        ]);
    }

    /**
     * Indicate that the event is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
        ]);
    }

    /**
     * Indicate that the event is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'start_date' => fake()->dateTimeBetween('-2 months', '-1 month')->format('Y-m-d'),
            'end_date' => fake()->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
        ]);
    }

    /**
     * Indicate that the event is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }

    /**
     * Indicate that the event has waitlist enabled.
     */
    public function withWaitlist(): static
    {
        return $this->state(fn (array $attributes) => [
            'has_waitlist' => true,
            'waitlist_capacity' => fake()->numberBetween(10, 50),
        ]);
    }

    /**
     * Indicate that the event has early bird pricing.
     */
    public function withEarlyBird(): static
    {
        return $this->state(fn (array $attributes) => [
            'early_bird_enabled' => true,
            'early_bird_deadline' => fake()->dateTimeBetween('now', '+1 month')->format('Y-m-d'),
            'early_bird_price' => fake()->numberBetween(50, 200),
            'regular_price' => fake()->numberBetween(200, 500),
        ]);
    }

    /**
     * Indicate that the event has a small capacity.
     */
    public function smallCapacity(): static
    {
        return $this->state(fn (array $attributes) => [
            'max_capacity' => fake()->numberBetween(10, 50),
        ]);
    }

    /**
     * Indicate that the event has a large capacity.
     */
    public function largeCapacity(): static
    {
        return $this->state(fn (array $attributes) => [
            'max_capacity' => fake()->numberBetween(500, 2000),
        ]);
    }
}
