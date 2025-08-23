<?php

namespace Database\Factories;

use App\Models\EventFeedback;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EventFeedback>
 */
class EventFeedbackFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = EventFeedback::class;

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
            'rating' => $this->faker->numberBetween(1, 5),
            'comment' => $this->faker->optional(0.7)->paragraph(),
            'feedback_data' => [
                'event_organization' => $this->faker->numberBetween(1, 5),
                'venue_quality' => $this->faker->numberBetween(1, 5),
                'speaker_quality' => $this->faker->numberBetween(1, 5),
                'content_relevance' => $this->faker->numberBetween(1, 5),
                'overall_experience' => $this->faker->numberBetween(1, 5),
            ],
            'is_anonymous' => $this->faker->boolean(20),
            'is_approved' => true,
        ];
    }

    /**
     * Indicate that the feedback has a 5-star rating.
     */
    public function fiveStar(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => 5,
        ]);
    }

    /**
     * Indicate that the feedback has a 4-star rating.
     */
    public function fourStar(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => 4,
        ]);
    }

    /**
     * Indicate that the feedback has a 3-star rating.
     */
    public function threeStar(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => 3,
        ]);
    }

    /**
     * Indicate that the feedback has a 2-star rating.
     */
    public function twoStar(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => 2,
        ]);
    }

    /**
     * Indicate that the feedback has a 1-star rating.
     */
    public function oneStar(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => 1,
        ]);
    }

    /**
     * Indicate that the feedback is anonymous.
     */
    public function anonymous(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_anonymous' => true,
        ]);
    }

    /**
     * Indicate that the feedback is not anonymous.
     */
    public function named(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_anonymous' => false,
        ]);
    }

    /**
     * Indicate that the feedback is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_approved' => true,
        ]);
    }

    /**
     * Indicate that the feedback is not approved.
     */
    public function notApproved(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_approved' => false,
        ]);
    }

    /**
     * Indicate that the feedback is for a specific event.
     */
    public function forEvent(Event $event): static
    {
        return $this->state(fn (array $attributes) => [
            'event_id' => $event->id,
        ]);
    }

    /**
     * Indicate that the feedback is by a specific user.
     */
    public function byUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Indicate that the feedback has a comment.
     */
    public function withComment(): static
    {
        return $this->state(fn (array $attributes) => [
            'comment' => $this->faker->paragraph(),
        ]);
    }

    /**
     * Indicate that the feedback has no comment.
     */
    public function withoutComment(): static
    {
        return $this->state(fn (array $attributes) => [
            'comment' => null,
        ]);
    }

    /**
     * Indicate that the feedback has custom feedback data.
     */
    public function withCustomData(array $data): static
    {
        return $this->state(fn (array $attributes) => [
            'feedback_data' => $data,
        ]);
    }

    /**
     * Indicate that the feedback has a high rating (4-5 stars).
     */
    public function highRating(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => $this->faker->numberBetween(4, 5),
        ]);
    }

    /**
     * Indicate that the feedback has a low rating (1-2 stars).
     */
    public function lowRating(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => $this->faker->numberBetween(1, 2),
        ]);
    }

    /**
     * Indicate that the feedback has a medium rating (3 stars).
     */
    public function mediumRating(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => 3,
        ]);
    }
}
