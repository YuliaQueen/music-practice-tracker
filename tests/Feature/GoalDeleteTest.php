<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Goals\Models\Goal;
use App\Domains\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoalDeleteTest extends TestCase
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
    public function user_can_delete_their_own_goal()
    {
        $this->actingAs($this->user);

        $goal = Goal::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Тестовая цель',
        ]);

        $response = $this->delete(route('goals.destroy', $goal));

        $response->assertRedirect(route('goals.index'));
        $response->assertSessionHas('success', 'Цель успешно удалена');

        $this->assertSoftDeleted('goals', [
            'id' => $goal->id,
            'user_id' => $this->user->id,
        ]);
    }

    /** @test */
    public function user_cannot_delete_other_users_goal()
    {
        $this->actingAs($this->user);

        $otherGoal = Goal::factory()->create([
            'user_id' => $this->otherUser->id,
            'title' => 'Чужая цель',
        ]);

        $response = $this->delete(route('goals.destroy', $otherGoal));

        $response->assertStatus(403);

        $this->assertDatabaseHas('goals', [
            'id' => $otherGoal->id,
            'user_id' => $this->otherUser->id,
            'deleted_at' => null,
        ]);
    }

    /** @test */
    public function guest_cannot_delete_goal()
    {
        $goal = Goal::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->delete(route('goals.destroy', $goal));

        $response->assertRedirect('/login');

        $this->assertDatabaseHas('goals', [
            'id' => $goal->id,
            'deleted_at' => null,
        ]);
    }

    /** @test */
    public function deleted_goal_is_not_shown_in_index()
    {
        $this->actingAs($this->user);

        $goal = Goal::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Цель для удаления',
        ]);

        // Удаляем цель
        $goal->delete();

        $response = $this->get(route('goals.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) =>
            $page->component('Goals/Index')
                ->has('goals', 0)
        );
    }

    /** @test */
    public function deleted_goal_cannot_be_accessed()
    {
        $this->actingAs($this->user);

        $goal = Goal::factory()->create([
            'user_id' => $this->user->id,
        ]);

        // Удаляем цель
        $goal->delete();

        $response = $this->get(route('goals.show', $goal));

        $response->assertStatus(404);
    }

    /** @test */
    public function deleted_goal_cannot_be_edited()
    {
        $this->actingAs($this->user);

        $goal = Goal::factory()->create([
            'user_id' => $this->user->id,
        ]);

        // Удаляем цель
        $goal->delete();

        $response = $this->get(route('goals.edit', $goal));

        $response->assertStatus(404);
    }

    /** @test */
    public function deleted_goal_cannot_be_updated()
    {
        $this->actingAs($this->user);

        $goal = Goal::factory()->create([
            'user_id' => $this->user->id,
        ]);

        // Удаляем цель
        $goal->delete();

        $updateData = [
            'title' => 'Обновленная цель',
            'type' => Goal::TYPE_DAILY_MINUTES,
            'target' => ['value' => 30, 'period' => 'daily'],
            'start_date' => now()->format('Y-m-d'),
        ];

        $response = $this->put(route('goals.update', $goal), $updateData);

        $response->assertStatus(404);
    }

    /** @test */
    public function deleted_goal_cannot_have_progress_updated()
    {
        $this->actingAs($this->user);

        $goal = Goal::factory()->create([
            'user_id' => $this->user->id,
        ]);

        // Удаляем цель
        $goal->delete();

        $progressData = [
            'current' => 25,
            'total' => 30,
        ];

        $response = $this->patch(route('goals.progress', $goal), $progressData);

        $response->assertStatus(404);
    }

    /** @test */
    public function deleted_goal_cannot_be_completed()
    {
        $this->actingAs($this->user);

        $goal = Goal::factory()->create([
            'user_id' => $this->user->id,
        ]);

        // Удаляем цель
        $goal->delete();

        $response = $this->post(route('goals.complete', $goal));

        $response->assertStatus(404);
    }

    /** @test */
    public function deleted_goal_cannot_be_toggled()
    {
        $this->actingAs($this->user);

        $goal = Goal::factory()->create([
            'user_id' => $this->user->id,
        ]);

        // Удаляем цель
        $goal->delete();

        $response = $this->post(route('goals.toggle', $goal));

        $response->assertStatus(404);
    }

    /** @test */
    public function deleting_goal_removes_it_from_goal_progress_calculation()
    {
        $this->actingAs($this->user);

        $goal = Goal::factory()->create([
            'user_id' => $this->user->id,
            'type' => Goal::TYPE_DAILY_MINUTES,
            'target' => ['value' => 30, 'period' => 'daily'],
        ]);

        // Проверяем, что цель учитывается в статистике
        $response = $this->get(route('goals.index'));
        $response->assertInertia(fn ($page) =>
            $page->has('goals', 1)
        );

        // Удаляем цель
        $goal->delete();

        // Проверяем, что цель больше не учитывается
        $response = $this->get(route('goals.index'));
        $response->assertInertia(fn ($page) =>
            $page->has('goals', 0)
        );
    }

    /** @test */
    public function deleting_goal_with_progress_preserves_data_for_analytics()
    {
        $this->actingAs($this->user);

        $goal = Goal::factory()->create([
            'user_id' => $this->user->id,
            'type' => Goal::TYPE_DAILY_MINUTES,
            'target' => ['value' => 30, 'period' => 'daily'],
            'progress' => ['current' => 25, 'total' => 30],
            'is_completed' => false,
        ]);

        // Удаляем цель (мягкое удаление)
        $goal->delete();

        // Проверяем, что данные сохранились в базе
        $this->assertSoftDeleted('goals', [
            'id' => $goal->id,
            'progress' => json_encode(['current' => 25, 'total' => 30]),
        ]);

        // Проверяем, что цель не отображается в интерфейсе
        $response = $this->get(route('goals.index'));
        $response->assertInertia(fn ($page) =>
            $page->has('goals', 0)
        );
    }

    /** @test */
    public function deleting_multiple_goals_works_correctly()
    {
        $this->actingAs($this->user);

        $goals = Goal::factory()->count(3)->create([
            'user_id' => $this->user->id,
        ]);

        // Удаляем все цели
        foreach ($goals as $goal) {
            $response = $this->delete(route('goals.destroy', $goal));
            $response->assertRedirect(route('goals.index'));
        }

        // Проверяем, что все цели удалены
        $response = $this->get(route('goals.index'));
        $response->assertInertia(fn ($page) =>
            $page->has('goals', 0)
        );

        // Проверяем в базе данных
        foreach ($goals as $goal) {
            $this->assertSoftDeleted('goals', ['id' => $goal->id]);
        }
    }

    /** @test */
    public function deleting_goal_shows_success_message()
    {
        $this->actingAs($this->user);

        $goal = Goal::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Цель для удаления',
        ]);

        $response = $this->delete(route('goals.destroy', $goal));

        $response->assertRedirect(route('goals.index'));
        $response->assertSessionHas('success', 'Цель успешно удалена');
    }

    /** @test */
    public function deleting_nonexistent_goal_returns_404()
    {
        $this->actingAs($this->user);

        $response = $this->delete(route('goals.destroy', 99999));

        $response->assertStatus(404);
    }

    /** @test */
    public function deleting_goal_does_not_affect_other_users_goals()
    {
        $this->actingAs($this->user);

        // Создаем цели для обоих пользователей
        $userGoal = Goal::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Цель пользователя',
        ]);

        $otherGoal = Goal::factory()->create([
            'user_id' => $this->otherUser->id,
            'title' => 'Цель другого пользователя',
        ]);

        // Удаляем цель пользователя
        $response = $this->delete(route('goals.destroy', $userGoal));
        $response->assertRedirect(route('goals.index'));

        // Проверяем, что цель пользователя удалена
        $this->assertSoftDeleted('goals', ['id' => $userGoal->id]);

        // Проверяем, что цель другого пользователя не затронута
        $this->assertDatabaseHas('goals', [
            'id' => $otherGoal->id,
            'user_id' => $this->otherUser->id,
            'deleted_at' => null,
        ]);
    }
}