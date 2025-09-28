<?php

namespace Database\Factories;

use App\Domains\Planning\Models\Session;
use App\Domains\Planning\Models\Template;
use App\Domains\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domains\Planning\Models\Session>
 */
class SessionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Session::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'practice_template_id' => null,
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'planned_duration' => $this->faker->numberBetween(30, 120),
            'actual_duration' => null,
            'status' => Session::STATUS_PLANNED,
            'scheduled_for' => null,
            'started_at' => null,
            'completed_at' => null,
            'metadata' => null,
        ];
    }

    /**
     * Indicate that the session is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Session::STATUS_ACTIVE,
            'started_at' => now(),
        ]);
    }

    /**
     * Indicate that the session is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Session::STATUS_COMPLETED,
            'started_at' => now()->subHours(2),
            'completed_at' => now(),
            'actual_duration' => $this->faker->numberBetween(30, 120),
        ]);
    }

    /**
     * Indicate that the session is paused.
     */
    public function paused(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Session::STATUS_PAUSED,
            'started_at' => now()->subMinutes(30),
        ]);
    }

    /**
     * Indicate that the session is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Session::STATUS_CANCELLED,
        ]);
    }

    /**
     * Indicate that the session uses a template.
     */
    public function withTemplate(): static
    {
        return $this->state(fn (array $attributes) => [
            'practice_template_id' => Template::factory(),
        ]);
    }

    /**
     * Indicate that the session is scheduled for a specific time.
     */
    public function scheduledFor(\DateTime $dateTime): static
    {
        return $this->state(fn (array $attributes) => [
            'scheduled_for' => $dateTime,
        ]);
    }
}