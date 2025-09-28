<?php

namespace Database\Factories;

use App\Domains\Planning\Models\Exercise;
use App\Domains\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domains\Planning\Models\Exercise>
 */
class ExerciseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Exercise::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'type' => $this->faker->randomElement(Exercise::TYPES),
            'planned_duration' => $this->faker->numberBetween(5, 60),
            'actual_duration' => null,
            'status' => Exercise::STATUS_PLANNED,
            'scheduled_for' => null,
            'started_at' => null,
            'completed_at' => null,
            'metadata' => null,
        ];
    }

    /**
     * Indicate that the exercise is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Exercise::STATUS_ACTIVE,
            'started_at' => now(),
        ]);
    }

    /**
     * Indicate that the exercise is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Exercise::STATUS_COMPLETED,
            'started_at' => now()->subMinutes(30),
            'completed_at' => now(),
            'actual_duration' => $this->faker->numberBetween(5, 60),
        ]);
    }

    /**
     * Indicate that the exercise is paused.
     */
    public function paused(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Exercise::STATUS_PAUSED,
            'started_at' => now()->subMinutes(15),
        ]);
    }

    /**
     * Indicate that the exercise is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Exercise::STATUS_CANCELLED,
        ]);
    }

    /**
     * Indicate that the exercise is of a specific type.
     */
    public function ofType(string $type): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => $type,
        ]);
    }

    /**
     * Indicate that the exercise is scheduled for a specific time.
     */
    public function scheduledFor(\DateTime $dateTime): static
    {
        return $this->state(fn (array $attributes) => [
            'scheduled_for' => $dateTime,
        ]);
    }
}