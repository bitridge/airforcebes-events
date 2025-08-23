<?php

namespace Database\Factories;

use App\Models\EventCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EventCategory>
 */
class EventCategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = EventCategory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);
        
        return [
            'name' => ucwords($name),
            'slug' => Str::slug($name),
            'description' => $this->faker->sentence(),
            'color' => $this->faker->hexColor(),
            'icon' => $this->faker->randomElement(['calendar', 'academic-cap', 'user-group', 'presentation-chart-line', 'wrench-screwdriver', 'code-bracket', 'computer-desktop', 'users']),
            'is_active' => true,
            'sort_order' => $this->faker->numberBetween(1, 100),
        ];
    }

    /**
     * Indicate that the category is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the category is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the category has a specific name.
     */
    public function withName(string $name): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $name,
            'slug' => Str::slug($name),
        ]);
    }

    /**
     * Indicate that the category has a specific color.
     */
    public function withColor(string $color): static
    {
        return $this->state(fn (array $attributes) => [
            'color' => $color,
        ]);
    }

    /**
     * Indicate that the category has a specific icon.
     */
    public function withIcon(string $icon): static
    {
        return $this->state(fn (array $attributes) => [
            'icon' => $icon,
        ]);
    }

    /**
     * Indicate that the category has a specific sort order.
     */
    public function withSortOrder(int $order): static
    {
        return $this->state(fn (array $attributes) => [
            'sort_order' => $order,
        ]);
    }

    /**
     * Indicate that the category has a description.
     */
    public function withDescription(): static
    {
        return $this->state(fn (array $attributes) => [
            'description' => $this->faker->paragraph(),
        ]);
    }

    /**
     * Indicate that the category has no description.
     */
    public function withoutDescription(): static
    {
        return $this->state(fn (array $attributes) => [
            'description' => null,
        ]);
    }

    /**
     * Indicate that the category is for conferences.
     */
    public function conferences(): static
    {
        return $this->withName('Conferences')
            ->withColor('#3B82F6')
            ->withIcon('academic-cap');
    }

    /**
     * Indicate that the category is for workshops.
     */
    public function workshops(): static
    {
        return $this->withName('Workshops')
            ->withColor('#10B981')
            ->withIcon('wrench-screwdriver');
    }

    /**
     * Indicate that the category is for seminars.
     */
    public function seminars(): static
    {
        return $this->withName('Seminars')
            ->withColor('#F59E0B')
            ->withIcon('presentation-chart-line');
    }

    /**
     * Indicate that the category is for networking events.
     */
    public function networking(): static
    {
        return $this->withName('Networking Events')
            ->withColor('#8B5CF6')
            ->withIcon('user-group');
    }

    /**
     * Indicate that the category is for training programs.
     */
    public function training(): static
    {
        return $this->withName('Training Programs')
            ->withColor('#EF4444')
            ->withIcon('academic-cap');
    }

    /**
     * Indicate that the category is for webinars.
     */
    public function webinars(): static
    {
        return $this->withName('Webinars')
            ->withColor('#06B6D4')
            ->withIcon('computer-desktop');
    }

    /**
     * Indicate that the category is for hackathons.
     */
    public function hackathons(): static
    {
        return $this->withName('Hackathons')
            ->withColor('#84CC16')
            ->withIcon('code-bracket');
    }

    /**
     * Indicate that the category is for meetups.
     */
    public function meetups(): static
    {
        return $this->withName('Meetups')
            ->withColor('#F97316')
            ->withIcon('users');
    }
}
