<?php

namespace Database\Factories;

use App\Domains\Planning\Models\Session;
use App\Domains\Planning\Models\SessionBlock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domains\Planning\Models\SessionBlock>
 */
class SessionBlockFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SessionBlock::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'practice_session_id' => Session::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'type' => $this->faker->randomElement(['warmup', 'technique', 'repertoire', 'improvisation', 'sight_reading', 'theory', 'break', 'custom']),
            'planned_duration' => $this->faker->numberBetween(5, 30),
            'actual_duration' => null,
            'status' => SessionBlock::STATUS_PLANNED,
            'sort_order' => 1,
            'started_at' => null,
            'completed_at' => null,
            'settings' => null,
        ];
    }

    /**
     * Indicate that the block is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SessionBlock::STATUS_ACTIVE,
            'started_at' => now(),
        ]);
    }

    /**
     * Indicate that the block is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SessionBlock::STATUS_COMPLETED,
            'started_at' => now()->subMinutes(20),
            'completed_at' => now(),
            'actual_duration' => $this->faker->numberBetween(5, 30),
        ]);
    }

    /**
     * Indicate that the block is paused.
     */
    public function paused(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SessionBlock::STATUS_PAUSED,
            'started_at' => now()->subMinutes(10),
        ]);
    }

    /**
     * Indicate that the block is of a specific type.
     */
    public function ofType(string $type): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => $type,
        ]);
    }

    /**
     * Indicate that the block has a specific sort order.
     */
    public function sortOrder(int $order): static
    {
        return $this->state(fn (array $attributes) => [
            'sort_order' => $order,
        ]);
    }
}