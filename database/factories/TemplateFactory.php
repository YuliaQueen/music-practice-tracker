<?php

namespace Database\Factories;

use App\Domains\Planning\Models\Template;
use App\Domains\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domains\Planning\Models\Template>
 */
class TemplateFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Template::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'category' => $this->faker->randomElement(Template::CATEGORIES),
            'difficulty_level' => $this->faker->randomElement(Template::DIFFICULTY_LEVELS),
            'total_duration' => $this->faker->numberBetween(30, 120),
            'is_public' => $this->faker->boolean(20), // 20% chance of being public
            'is_featured' => $this->faker->boolean(10), // 10% chance of being featured
            'usage_count' => $this->faker->numberBetween(0, 50),
            'tags' => $this->faker->words(3),
            'metadata' => null,
        ];
    }

    /**
     * Indicate that the template is public.
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => true,
        ]);
    }

    /**
     * Indicate that the template is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    /**
     * Indicate that the template is private.
     */
    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => false,
        ]);
    }

    /**
     * Indicate that the template is of a specific category.
     */
    public function category(string $category): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => $category,
        ]);
    }

    /**
     * Indicate that the template is of a specific difficulty level.
     */
    public function difficulty(string $difficulty): static
    {
        return $this->state(fn (array $attributes) => [
            'difficulty_level' => $difficulty,
        ]);
    }
}
