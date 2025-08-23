<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use App\Models\EventCategory;
use App\Models\EventSeries;
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
        $startDate = $this->faker->dateTimeBetween('now', '+6 months');
        $endDate = $this->faker->dateTimeBetween($startDate, '+1 year');
        $registrationDeadline = $this->faker->dateTimeBetween('now', $startDate);
        
        $title = $this->faker->sentence(4);
        $slug = Str::slug($title);
        
        return [
            'title' => $title,
            'description' => $this->faker->paragraphs(3, true),
            'slug' => $slug,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'start_time' => $this->faker->time('H:i:s'),
            'end_time' => $this->faker->time('H:i:s'),
            'venue' => $this->faker->address(),
            'max_capacity' => $this->faker->numberBetween(10, 500),
            'registration_deadline' => $registrationDeadline,
            'status' => $this->faker->randomElement(['draft', 'published', 'completed', 'cancelled']),
            'featured_image' => $this->faker->imageUrl(800, 600, 'events'),
            'created_by' => User::factory(),
            'category_id' => EventCategory::factory(),
            'tags' => $this->faker->randomElements(['Technology', 'Business', 'Education', 'Healthcare', 'Finance', 'Marketing', 'Design', 'Leadership'], $this->faker->numberBetween(1, 4)),
            'series_id' => null,
            'series_order' => null,
            'has_waitlist' => $this->faker->boolean(30),
            'waitlist_capacity' => function (array $attributes) {
                return $attributes['has_waitlist'] ? $this->faker->numberBetween(5, 50) : null;
            },
            'early_bird_enabled' => $this->faker->boolean(40),
            'early_bird_deadline' => function (array $attributes) {
                return $attributes['early_bird_enabled'] ? $this->faker->dateTimeBetween('now', $attributes['start_date'])->format('Y-m-d H:i:s') : null;
            },
            'early_bird_price' => function (array $attributes) {
                return $attributes['early_bird_enabled'] ? $this->faker->randomFloat(2, 10, 100) : null;
            },
            'regular_price' => function (array $attributes) {
                return $attributes['early_bird_enabled'] ? $this->faker->randomFloat(2, 20, 200) : null;
            },
            'requires_confirmation' => $this->faker->boolean(20),
            'confirmation_message' => function (array $attributes) {
                return $attributes['requires_confirmation'] ? $this->faker->sentence() : null;
            },
            'has_custom_fields' => $this->faker->boolean(25),
            'is_archived' => false,
            'archived_at' => null,
            'archived_by' => null,
        ];
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
     * Indicate that the event is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
        ]);
    }

    /**
     * Indicate that the event is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
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
     * Indicate that the event is upcoming.
     */
    public function upcoming(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_date' => $this->faker->dateTimeBetween('+1 day', '+6 months')->format('Y-m-d'),
            'status' => 'published',
        ]);
    }

    /**
     * Indicate that the event is past.
     */
    public function past(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_date' => $this->faker->dateTimeBetween('-1 year', '-1 day')->format('Y-m-d'),
            'end_date' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'status' => 'completed',
        ]);
    }

    /**
     * Indicate that the event has waitlist enabled.
     */
    public function withWaitlist(): static
    {
        return $this->state(fn (array $attributes) => [
            'has_waitlist' => true,
            'waitlist_capacity' => $this->faker->numberBetween(5, 50),
        ]);
    }

    /**
     * Indicate that the event has early bird pricing.
     */
    public function withEarlyBird(): static
    {
        return $this->state(fn (array $attributes) => [
            'early_bird_enabled' => true,
            'early_bird_deadline' => $this->faker->dateTimeBetween('now', $attributes['start_date'])->format('Y-m-d H:i:s'),
            'early_bird_price' => $this->faker->randomFloat(2, 10, 100),
            'regular_price' => $this->faker->randomFloat(2, 20, 200),
        ]);
    }

    /**
     * Indicate that the event requires confirmation.
     */
    public function requiringConfirmation(): static
    {
        return $this->state(fn (array $attributes) => [
            'requires_confirmation' => true,
            'confirmation_message' => $this->faker->sentence(),
        ]);
    }

    /**
     * Indicate that the event has custom fields.
     */
    public function withCustomFields(): static
    {
        return $this->state(fn (array $attributes) => [
            'has_custom_fields' => true,
        ]);
    }

    /**
     * Indicate that the event is archived.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_archived' => true,
            'archived_at' => now(),
            'archived_by' => User::factory(),
        ]);
    }

    /**
     * Indicate that the event is part of a series.
     */
    public function inSeries(EventSeries $series, int $order = 1): static
    {
        return $this->state(fn (array $attributes) => [
            'series_id' => $series->id,
            'series_order' => $order,
        ]);
    }

    /**
     * Indicate that the event is full (no available capacity).
     */
    public function full(): static
    {
        return $this->state(fn (array $attributes) => [
            'max_capacity' => 10,
            'status' => 'published',
        ]);
    }

    /**
     * Indicate that the event has unlimited capacity.
     */
    public function unlimited(): static
    {
        return $this->state(fn (array $attributes) => [
            'max_capacity' => null,
        ]);
    }

    /**
     * Indicate that the event is free.
     */
    public function free(): static
    {
        return $this->state(fn (array $attributes) => [
            'early_bird_enabled' => false,
            'regular_price' => null,
        ]);
    }

    /**
     * Indicate that the event is paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'regular_price' => $this->faker->randomFloat(2, 20, 200),
        ]);
    }
}
