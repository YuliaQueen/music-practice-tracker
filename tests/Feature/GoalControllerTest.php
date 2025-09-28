<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Goals\Models\Goal;
use App\Domains\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoalControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
    }

    /** @test */
    public function it_can_display_goals_index()
    {
        $this->actingAs($this->user);

        Goal::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->get(route('goals.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('Goals/Index')
                ->has('goals', 3)
                ->has('goalTypes')
        );
    }

    /** @test */
    public function it_can_display_create_goal_form()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('goals.create'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('Goals/Create')
                ->has('goalTypes')
        );
    }

    /** @test */
    public function it_can_create_a_goal()
    {
        $this->actingAs($this->user);

        $goalData = [
            'title' => 'Практиковать 30 минут в день',
            'description' => 'Ежедневная практика для поддержания навыков',
            'type' => Goal::TYPE_DAILY_MINUTES,
            'target' => ['value' => 30, 'period' => 'daily'],
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addMonth()->format('Y-m-d'),
        ];

        $response = $this->post(route('goals.store'), $goalData);

        $response->assertRedirect(route('goals.index'));
        $response->assertSessionHas('success', 'Цель успешно создана');

        $this->assertDatabaseHas('goals', [
            'user_id' => $this->user->id,
            'title' => 'Практиковать 30 минут в день',
            'type' => Goal::TYPE_DAILY_MINUTES,
            'is_active' => true,
            'is_completed' => false,
        ]);
    }

    /** @test */
    public function it_validates_goal_creation()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('goals.store'), []);

        $response->assertSessionHasErrors(['title', 'type', 'target.value', 'start_date']);
    }

    /** @test */
    public function it_can_display_goal_show_page()
    {
        $this->actingAs($this->user);

        $goal = Goal::factory()->create(['user_id' => $this->user->id]);

        $response = $this->get(route('goals.show', $goal));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('Goals/Show')
                ->has('goal')
                ->where('goal.id', $goal->id)
        );
    }

    /** @test */
    public function it_can_display_goal_edit_form()
    {
        $this->actingAs($this->user);

        $goal = Goal::factory()->create(['user_id' => $this->user->id]);

        $response = $this->get(route('goals.edit', $goal));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('Goals/Edit')
                ->has('goal')
                ->has('goalTypes')
                ->where('goal.id', $goal->id)
        );
    }

    /** @test */
    public function it_can_update_a_goal()
    {
        $this->actingAs($this->user);

        $goal = Goal::factory()->create(['user_id' => $this->user->id]);

        $updateData = [
            'title' => 'Обновленная цель',
            'description' => 'Новое описание',
            'type' => Goal::TYPE_WEEKLY_SESSIONS,
            'target' => ['value' => 5, 'period' => 'weekly'],
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addMonth()->format('Y-m-d'),
            'is_active' => true,
        ];

        $response = $this->put(route('goals.update', $goal), $updateData);

        $response->assertRedirect(route('goals.index'));
        $response->assertSessionHas('success', 'Цель успешно обновлена');

        $this->assertDatabaseHas('goals', [
            'id' => $goal->id,
            'title' => 'Обновленная цель',
            'type' => Goal::TYPE_WEEKLY_SESSIONS,
        ]);
    }

    /** @test */
    public function it_can_delete_a_goal()
    {
        $this->actingAs($this->user);

        $goal = Goal::factory()->create(['user_id' => $this->user->id]);

        $response = $this->delete(route('goals.destroy', $goal));

        $response->assertRedirect(route('goals.index'));
        $response->assertSessionHas('success', 'Цель успешно удалена');

        $this->assertSoftDeleted('goals', ['id' => $goal->id]);
    }

    /** @test */
    public function it_can_update_goal_progress()
    {
        $this->actingAs($this->user);

        $goal = Goal::factory()->create([
            'user_id' => $this->user->id,
            'target' => ['value' => 100, 'period' => 'daily']
        ]);

        $progressData = [
            'current' => 50,
            'total' => 100,
        ];

        $response = $this->patch(route('goals.progress', $goal), $progressData);

        $response->assertRedirect(route('goals.show', $goal));
        $response->assertSessionHas('success', 'Прогресс цели обновлен');

        $goal->refresh();
        $this->assertEquals(50, $goal->getCurrentValue());
        $this->assertEquals(50, $goal->getProgressPercentage());
    }

    /** @test */
    public function it_can_mark_goal_as_completed()
    {
        $this->actingAs($this->user);

        $goal = Goal::factory()->create([
            'user_id' => $this->user->id,
            'is_completed' => false
        ]);

        $response = $this->post(route('goals.complete', $goal));

        $response->assertRedirect(route('goals.index'));
        $response->assertSessionHas('success', 'Цель отмечена как завершенная');

        $goal->refresh();
        $this->assertTrue($goal->is_completed);
        $this->assertNotNull($goal->completed_at);
    }

    /** @test */
    public function it_can_toggle_goal_active_status()
    {
        $this->actingAs($this->user);

        $goal = Goal::factory()->create([
            'user_id' => $this->user->id,
            'is_active' => true
        ]);

        $response = $this->post(route('goals.toggle', $goal));

        $response->assertRedirect(route('goals.index'));
        $response->assertSessionHas('success', 'Цель деактивирована');

        $goal->refresh();
        $this->assertFalse($goal->is_active);

        // Toggle again
        $response = $this->post(route('goals.toggle', $goal));

        $goal->refresh();
        $this->assertTrue($goal->is_active);
    }

    /** @test */
    public function it_prevents_access_to_other_users_goals()
    {
        $this->actingAs($this->user);

        $otherGoal = Goal::factory()->create(['user_id' => $this->otherUser->id]);

        // Try to view
        $response = $this->get(route('goals.show', $otherGoal));
        $response->assertStatus(403);

        // Try to edit
        $response = $this->get(route('goals.edit', $otherGoal));
        $response->assertStatus(403);

        // Try to update
        $response = $this->put(route('goals.update', $otherGoal), [
            'title' => 'Hacked',
            'type' => Goal::TYPE_DAILY_MINUTES,
            'target' => ['value' => 1],
            'start_date' => now()->format('Y-m-d'),
        ]);
        $response->assertStatus(403);

        // Try to delete
        $response = $this->delete(route('goals.destroy', $otherGoal));
        $response->assertStatus(403);

        // Try to update progress
        $response = $this->patch(route('goals.progress', $otherGoal), [
            'current' => 100,
        ]);
        $response->assertStatus(403);

        // Try to complete
        $response = $this->post(route('goals.complete', $otherGoal));
        $response->assertStatus(403);

        // Try to toggle
        $response = $this->post(route('goals.toggle', $otherGoal));
        $response->assertStatus(403);
    }

    /** @test */
    public function it_only_shows_user_own_goals()
    {
        $this->actingAs($this->user);

        Goal::factory()->count(2)->create(['user_id' => $this->user->id]);
        Goal::factory()->count(3)->create(['user_id' => $this->otherUser->id]);

        $response = $this->get(route('goals.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->has('goals', 2)
        );
    }

    /** @test */
    public function it_requires_authentication()
    {
        $goal = Goal::factory()->create(['user_id' => $this->user->id]);

        $routes = [
            ['GET', route('goals.index')],
            ['GET', route('goals.create')],
            ['POST', route('goals.store')],
            ['GET', route('goals.show', $goal)],
            ['GET', route('goals.edit', $goal)],
            ['PUT', route('goals.update', $goal)],
            ['DELETE', route('goals.destroy', $goal)],
            ['PATCH', route('goals.progress', $goal)],
            ['POST', route('goals.complete', $goal)],
            ['POST', route('goals.toggle', $goal)],
        ];

        foreach ($routes as [$method, $url]) {
            $response = $this->call($method, $url);
            $response->assertRedirect('/login');
        }
    }

    /** @test */
    public function it_validates_goal_update()
    {
        $this->actingAs($this->user);

        $goal = Goal::factory()->create(['user_id' => $this->user->id]);

        $response = $this->put(route('goals.update', $goal), []);

        $response->assertSessionHasErrors(['title', 'type', 'target.value', 'start_date']);
    }

    /** @test */
    public function it_validates_progress_update()
    {
        $this->actingAs($this->user);

        $goal = Goal::factory()->create(['user_id' => $this->user->id]);

        $response = $this->patch(route('goals.progress', $goal), []);

        $response->assertSessionHasErrors(['current']);
    }

    /** @test */
    public function it_can_create_goal_without_end_date()
    {
        $this->actingAs($this->user);

        $goalData = [
            'title' => 'Бессрочная цель',
            'description' => 'Цель без даты окончания',
            'type' => Goal::TYPE_DAILY_MINUTES,
            'target' => ['value' => 30, 'period' => 'daily'],
            'start_date' => now()->format('Y-m-d'),
            'end_date' => null,
        ];

        $response = $this->post(route('goals.store'), $goalData);

        $response->assertRedirect(route('goals.index'));

        $this->assertDatabaseHas('goals', [
            'title' => 'Бессрочная цель',
            'end_date' => null,
        ]);
    }

    /** @test */
    public function it_can_create_goal_without_description()
    {
        $this->actingAs($this->user);

        $goalData = [
            'title' => 'Цель без описания',
            'description' => null,
            'type' => Goal::TYPE_DAILY_MINUTES,
            'target' => ['value' => 30, 'period' => 'daily'],
            'start_date' => now()->format('Y-m-d'),
        ];

        $response = $this->post(route('goals.store'), $goalData);

        $response->assertRedirect(route('goals.index'));

        $this->assertDatabaseHas('goals', [
            'title' => 'Цель без описания',
            'description' => null,
        ]);
    }

    /** @test */
    public function it_handles_different_goal_types()
    {
        $this->actingAs($this->user);

        $goalTypes = [
            Goal::TYPE_DAILY_MINUTES,
            Goal::TYPE_WEEKLY_SESSIONS,
            Goal::TYPE_STREAK_DAYS,
            Goal::TYPE_EXERCISE_TYPE,
            Goal::TYPE_MONTHLY_MINUTES,
            Goal::TYPE_YEARLY_SESSIONS,
        ];

        foreach ($goalTypes as $type) {
            $goalData = [
                'title' => "Цель типа {$type}",
                'type' => $type,
                'target' => ['value' => 10, 'period' => 'custom'],
                'start_date' => now()->format('Y-m-d'),
            ];

            $response = $this->post(route('goals.store'), $goalData);

            $response->assertRedirect(route('goals.index'));

            $this->assertDatabaseHas('goals', [
                'title' => "Цель типа {$type}",
                'type' => $type,
            ]);
        }
    }

    /** @test */
    public function it_can_update_progress_without_total()
    {
        $this->actingAs($this->user);

        $goal = Goal::factory()->create([
            'user_id' => $this->user->id,
            'target' => ['value' => 100, 'period' => 'daily']
        ]);

        $progressData = [
            'current' => 50,
            // 'total' is not provided, should use target value
        ];

        $response = $this->patch(route('goals.progress', $goal), $progressData);

        $response->assertRedirect(route('goals.show', $goal));

        $goal->refresh();
        $this->assertEquals(50, $goal->getCurrentValue());
        $this->assertEquals(100, $goal->progress['total']); // Should use target value
    }
}