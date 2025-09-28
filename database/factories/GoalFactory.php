<?php

namespace Database\Factories;

use App\Domains\Goals\Models\Goal;
use App\Domains\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domains\Goals\Models\Goal>
 */
class GoalFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Goal::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = $this->faker->randomElement(Goal::TYPES);
        
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'type' => $type,
            'target' => $this->getTargetForType($type),
            'progress' => null,
            'start_date' => $this->faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'end_date' => $this->faker->optional(0.3)->dateTimeBetween('now', '+3 months')?->format('Y-m-d'),
            'is_active' => $this->faker->boolean(80), // 80% chance of being active
            'is_completed' => false,
            'completed_at' => null,
        ];
    }

    /**
     * Get target data based on goal type
     */
    private function getTargetForType(string $type): array
    {
        return match ($type) {
            Goal::TYPE_DAILY_MINUTES => [
                'value' => $this->faker->numberBetween(15, 120),
                'period' => 'daily',
            ],
            Goal::TYPE_WEEKLY_SESSIONS => [
                'value' => $this->faker->numberBetween(3, 7),
                'period' => 'weekly',
            ],
            Goal::TYPE_STREAK_DAYS => [
                'value' => $this->faker->numberBetween(7, 30),
                'period' => 'streak',
            ],
            Goal::TYPE_EXERCISE_TYPE => [
                'value' => $this->faker->numberBetween(30, 90),
                'exercise_type' => $this->faker->randomElement(['technique', 'repertoire', 'improvisation']),
                'period' => 'weekly',
            ],
            Goal::TYPE_MONTHLY_MINUTES => [
                'value' => $this->faker->numberBetween(300, 1800),
                'period' => 'monthly',
            ],
            Goal::TYPE_YEARLY_SESSIONS => [
                'value' => $this->faker->numberBetween(50, 200),
                'period' => 'yearly',
            ],
            default => [
                'value' => $this->faker->numberBetween(1, 100),
                'period' => 'custom',
            ],
        };
    }

    /**
     * Indicate that the goal is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
            'is_completed' => false,
        ]);
    }

    /**
     * Indicate that the goal is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
            'is_completed' => true,
            'completed_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'progress' => [
                'current' => $attributes['target']['value'],
                'total' => $attributes['target']['value'],
            ],
        ]);
    }

    /**
     * Indicate that the goal is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
            'end_date' => $this->faker->dateTimeBetween('-1 month', '-1 day')->format('Y-m-d'),
        ]);
    }

    /**
     * Indicate that the goal is of a specific type.
     */
    public function ofType(string $type): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => $type,
            'target' => $this->getTargetForType($type),
        ]);
    }

    /**
     * Indicate that the goal has progress.
     */
    public function withProgress(int $current, int $total = null): static
    {
        return $this->state(fn (array $attributes) => [
            'progress' => [
                'current' => $current,
                'total' => $total ?? $attributes['target']['value'],
            ],
        ]);
    }

    /**
     * Indicate that the goal is for daily minutes.
     */
    public function dailyMinutes(int $minutes = 30): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Goal::TYPE_DAILY_MINUTES,
            'title' => "Практиковать {$minutes} минут в день",
            'target' => [
                'value' => $minutes,
                'period' => 'daily',
            ],
        ]);
    }

    /**
     * Indicate that the goal is for weekly sessions.
     */
    public function weeklySessions(int $sessions = 5): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Goal::TYPE_WEEKLY_SESSIONS,
            'title' => "Провести {$sessions} сессий в неделю",
            'target' => [
                'value' => $sessions,
                'period' => 'weekly',
            ],
        ]);
    }

    /**
     * Indicate that the goal is for streak days.
     */
    public function streakDays(int $days = 7): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Goal::TYPE_STREAK_DAYS,
            'title' => "Практиковать {$days} дней подряд",
            'target' => [
                'value' => $days,
                'period' => 'streak',
            ],
        ]);
    }
}