<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Goals\Models\Goal;
use App\Domains\Planning\Models\Session;
use App\Domains\Planning\Models\SessionBlock;
use App\Domains\User\Models\User;
use App\Services\GoalProgressService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoalSessionIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private GoalProgressService $goalProgressService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->goalProgressService = app(GoalProgressService::class);
    }

    /** @test */
    public function it_updates_daily_minutes_goal_progress_from_sessions()
    {
        // Создаем цель на ежедневные минуты
        $goal = Goal::factory()->create([
            'user_id' => $this->user->id,
            'type' => Goal::TYPE_DAILY_MINUTES,
            'target' => ['value' => 30, 'period' => 'daily'],
            'progress' => null,
            'is_active' => true,
        ]);

        // Создаем завершенную сессию с блоками
        $session = Session::factory()->completed()->create([
            'user_id' => $this->user->id,
            'actual_duration' => 45,
            'created_at' => Carbon::now(),
        ]);

        SessionBlock::factory()->create([
            'practice_session_id' => $session->id,
            'status' => SessionBlock::STATUS_COMPLETED,
            'actual_duration' => 25,
        ]);

        SessionBlock::factory()->create([
            'practice_session_id' => $session->id,
            'status' => SessionBlock::STATUS_COMPLETED,
            'actual_duration' => 20,
        ]);

        // Обновляем прогресс
        $updatedGoals = $this->goalProgressService->updateProgressFromSessions($this->user);

        $goal->refresh();
        $this->assertCount(1, $updatedGoals);
        $this->assertEquals(45, $goal->getCurrentValue());
        $this->assertEquals(100, $goal->getProgressPercentage()); // 45/30 * 100 = 150%, но ограничено до 100%
    }

    /** @test */
    public function it_updates_weekly_sessions_goal_progress_from_sessions()
    {
        // Создаем цель на еженедельные сессии
        $goal = Goal::factory()->create([
            'user_id' => $this->user->id,
            'type' => Goal::TYPE_WEEKLY_SESSIONS,
            'target' => ['value' => 3, 'period' => 'weekly'],
            'progress' => null,
            'is_active' => true,
        ]);

        // Создаем несколько завершенных сессий
        Session::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'status' => Session::STATUS_COMPLETED,
            'created_at' => Carbon::now(),
        ]);

        Session::factory()->create([
            'user_id' => $this->user->id,
            'status' => Session::STATUS_PLANNED, // Не завершенная
            'created_at' => Carbon::now(),
        ]);

        // Обновляем прогресс
        $updatedGoals = $this->goalProgressService->updateProgressFromSessions($this->user);

        $goal->refresh();
        $this->assertCount(1, $updatedGoals);
        $this->assertEquals(2, $goal->getCurrentValue());
        $this->assertEquals(67, $goal->getProgressPercentage()); // 2/3 * 100 = 67%
    }

    /** @test */
    public function it_updates_streak_days_goal_progress_from_sessions()
    {
        // Создаем цель на серию дней
        $goal = Goal::factory()->create([
            'user_id' => $this->user->id,
            'type' => Goal::TYPE_STREAK_DAYS,
            'target' => ['value' => 7, 'period' => 'streak'],
            'progress' => null,
            'is_active' => true,
        ]);

        // Создаем сессии за последние 3 дня
        Session::factory()->completed()->create([
            'user_id' => $this->user->id,
            'created_at' => Carbon::now()->subDays(2),
        ]);

        Session::factory()->completed()->create([
            'user_id' => $this->user->id,
            'created_at' => Carbon::now()->subDays(1),
        ]);

        Session::factory()->completed()->create([
            'user_id' => $this->user->id,
            'created_at' => Carbon::now(),
        ]);

        // Обновляем прогресс
        $updatedGoals = $this->goalProgressService->updateProgressFromSessions($this->user);

        $goal->refresh();
        $this->assertCount(1, $updatedGoals);
        $this->assertEquals(3, $goal->getCurrentValue());
        $this->assertEquals(43, $goal->getProgressPercentage()); // 3/7 * 100 = 43%
    }

    /** @test */
    public function it_updates_exercise_type_goal_progress_from_sessions()
    {
        // Создаем цель на тип упражнения
        $goal = Goal::factory()->create([
            'user_id' => $this->user->id,
            'type' => Goal::TYPE_EXERCISE_TYPE,
            'target' => ['value' => 60, 'period' => 'daily', 'exercise_type' => SessionBlock::TYPE_TECHNIQUE],
            'progress' => null,
            'is_active' => true,
        ]);

        // Создаем сессию с блоками разных типов
        $session = Session::factory()->completed()->create([
            'user_id' => $this->user->id,
            'created_at' => Carbon::now(),
        ]);

        // Блок с техникой (должен засчитаться)
        SessionBlock::factory()->create([
            'practice_session_id' => $session->id,
            'status' => SessionBlock::STATUS_COMPLETED,
            'actual_duration' => 30,
            'type' => SessionBlock::TYPE_TECHNIQUE,
        ]);

        // Блок с репертуаром (не должен засчитаться)
        SessionBlock::factory()->create([
            'practice_session_id' => $session->id,
            'status' => SessionBlock::STATUS_COMPLETED,
            'actual_duration' => 20,
            'type' => SessionBlock::TYPE_REPERTOIRE,
        ]);

        // Обновляем прогресс
        $updatedGoals = $this->goalProgressService->updateProgressFromSessions($this->user);

        $goal->refresh();
        $this->assertCount(1, $updatedGoals);
        $this->assertEquals(30, $goal->getCurrentValue());
        $this->assertEquals(50, $goal->getProgressPercentage()); // 30/60 * 100 = 50%
    }

    /** @test */
    public function it_marks_goals_as_completed_when_progress_reaches_target()
    {
        // Создаем цель на ежедневные минуты
        $goal = Goal::factory()->create([
            'user_id' => $this->user->id,
            'type' => Goal::TYPE_DAILY_MINUTES,
            'target' => ['value' => 30, 'period' => 'daily'],
            'progress' => null,
            'is_active' => true,
            'is_completed' => false,
        ]);

        // Создаем сессию с достаточной продолжительностью
        $session = Session::factory()->completed()->create([
            'user_id' => $this->user->id,
            'actual_duration' => 35,
            'created_at' => Carbon::now(),
        ]);

        SessionBlock::factory()->create([
            'practice_session_id' => $session->id,
            'status' => SessionBlock::STATUS_COMPLETED,
            'actual_duration' => 35,
        ]);

        // Обновляем прогресс
        $updatedGoals = $this->goalProgressService->updateProgressFromSessions($this->user);
        $completedGoals = $this->goalProgressService->checkAndCompleteGoals($this->user);

        $goal->refresh();
        
        $this->assertCount(1, $updatedGoals);
        $this->assertCount(1, $completedGoals);
        $this->assertTrue($goal->is_completed);
        $this->assertNotNull($goal->completed_at);
    }

    /** @test */
    public function it_updates_progress_after_session_completion()
    {
        // Создаем цель
        $goal = Goal::factory()->create([
            'user_id' => $this->user->id,
            'type' => Goal::TYPE_DAILY_MINUTES,
            'target' => ['value' => 30, 'period' => 'daily'],
            'progress' => null,
            'is_active' => true,
        ]);

        // Создаем сессию
        $session = Session::factory()->create([
            'user_id' => $this->user->id,
            'status' => Session::STATUS_PLANNED,
            'created_at' => Carbon::now(),
        ]);

        SessionBlock::factory()->create([
            'practice_session_id' => $session->id,
            'status' => SessionBlock::STATUS_COMPLETED,
            'actual_duration' => 25,
        ]);

        // Завершаем сессию
        $session->update([
            'status' => Session::STATUS_COMPLETED,
            'completed_at' => now(),
            'actual_duration' => 25,
        ]);

        // Обновляем прогресс после завершения сессии
        $updatedGoals = $this->goalProgressService->updateProgressAfterSession($session);

        $goal->refresh();
        $this->assertCount(1, $updatedGoals);
        $this->assertEquals(25, $goal->getCurrentValue());
    }

    /** @test */
    public function it_updates_progress_after_session_block_completion()
    {
        // Создаем цель
        $goal = Goal::factory()->create([
            'user_id' => $this->user->id,
            'type' => Goal::TYPE_DAILY_MINUTES,
            'target' => ['value' => 30, 'period' => 'daily'],
            'progress' => null,
            'is_active' => true,
        ]);

        // Создаем сессию и блок
        $session = Session::factory()->create([
            'user_id' => $this->user->id,
            'status' => Session::STATUS_ACTIVE,
            'created_at' => Carbon::now(),
        ]);

        $sessionBlock = SessionBlock::factory()->create([
            'practice_session_id' => $session->id,
            'status' => SessionBlock::STATUS_PLANNED,
            'actual_duration' => 0,
        ]);

        // Завершаем блок
        $sessionBlock->update([
            'status' => SessionBlock::STATUS_COMPLETED,
            'actual_duration' => 20,
            'completed_at' => now(),
        ]);

        // Обновляем прогресс после завершения блока
        $updatedGoals = $this->goalProgressService->updateProgressAfterSessionBlock($sessionBlock);

        $goal->refresh();
        $this->assertCount(1, $updatedGoals);
        $this->assertEquals(20, $goal->getCurrentValue());
    }

    /** @test */
    public function it_calculates_progress_for_specific_date_range()
    {
        // Создаем цель
        $goal = Goal::factory()->create([
            'user_id' => $this->user->id,
            'type' => Goal::TYPE_DAILY_MINUTES,
            'target' => ['value' => 30, 'period' => 'daily'],
            'progress' => null,
            'is_active' => true,
        ]);

        $yesterday = Carbon::yesterday();
        $today = Carbon::now();

        // Создаем сессии за вчера и сегодня
        Session::factory()->completed()->create([
            'user_id' => $this->user->id,
            'actual_duration' => 25,
            'created_at' => $yesterday,
        ]);

        Session::factory()->completed()->create([
            'user_id' => $this->user->id,
            'actual_duration' => 35,
            'created_at' => $today,
        ]);

        // Обновляем прогресс только за сегодня
        $updatedGoals = $this->goalProgressService->updateProgressFromSessions(
            $this->user,
            $today->copy()->startOfDay(),
            $today->copy()->endOfDay()
        );

        $goal->refresh();
        $this->assertCount(1, $updatedGoals);
        $this->assertEquals(35, $goal->getCurrentValue());
    }

    /** @test */
    public function it_handles_multiple_goals_of_different_types()
    {
        // Создаем несколько целей разных типов
        $dailyGoal = Goal::factory()->create([
            'user_id' => $this->user->id,
            'type' => Goal::TYPE_DAILY_MINUTES,
            'target' => ['value' => 30, 'period' => 'daily'],
            'progress' => null,
            'is_active' => true,
        ]);

        $weeklyGoal = Goal::factory()->create([
            'user_id' => $this->user->id,
            'type' => Goal::TYPE_WEEKLY_SESSIONS,
            'target' => ['value' => 2, 'period' => 'weekly'],
            'progress' => null,
            'is_active' => true,
        ]);

        // Создаем сессии
        Session::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'status' => Session::STATUS_COMPLETED,
            'actual_duration' => 20,
            'created_at' => Carbon::now(),
        ]);

        // Обновляем прогресс
        $updatedGoals = $this->goalProgressService->updateProgressFromSessions($this->user);

        $dailyGoal->refresh();
        $weeklyGoal->refresh();

        $this->assertCount(2, $updatedGoals);
        $this->assertEquals(40, $dailyGoal->getCurrentValue());
        $this->assertEquals(2, $weeklyGoal->getCurrentValue());
        $this->assertTrue($weeklyGoal->is_completed); // 2/2 = 100%
    }

    /** @test */
    public function it_ignores_inactive_goals()
    {
        // Создаем активную и неактивную цели
        $activeGoal = Goal::factory()->create([
            'user_id' => $this->user->id,
            'type' => Goal::TYPE_DAILY_MINUTES,
            'target' => ['value' => 30, 'period' => 'daily'],
            'progress' => null,
            'is_active' => true,
        ]);

        $inactiveGoal = Goal::factory()->create([
            'user_id' => $this->user->id,
            'type' => Goal::TYPE_DAILY_MINUTES,
            'target' => ['value' => 30, 'period' => 'daily'],
            'progress' => null,
            'is_active' => false,
        ]);

        // Создаем сессию
        Session::factory()->completed()->create([
            'user_id' => $this->user->id,
            'actual_duration' => 25,
            'created_at' => Carbon::now(),
        ]);

        // Обновляем прогресс
        $updatedGoals = $this->goalProgressService->updateProgressFromSessions($this->user);

        $activeGoal->refresh();
        $inactiveGoal->refresh();

        $this->assertCount(1, $updatedGoals);
        $this->assertEquals(25, $activeGoal->getCurrentValue());
        $this->assertEquals(0, $inactiveGoal->getCurrentValue()); // Не обновился
    }

    /** @test */
    public function it_gets_goal_progress_stats()
    {
        // Создаем несколько целей
        Goal::factory()->create([
            'user_id' => $this->user->id,
            'type' => Goal::TYPE_DAILY_MINUTES,
            'target' => ['value' => 30, 'period' => 'daily'],
            'progress' => ['current' => 25, 'total' => 30],
            'is_active' => true,
            'is_completed' => false,
        ]);

        Goal::factory()->create([
            'user_id' => $this->user->id,
            'type' => Goal::TYPE_WEEKLY_SESSIONS,
            'target' => ['value' => 2, 'period' => 'weekly'],
            'progress' => ['current' => 2, 'total' => 2],
            'is_active' => true,
            'is_completed' => true,
        ]);

        // Получаем статистику
        $stats = $this->goalProgressService->getGoalProgressStats($this->user);

        $this->assertEquals(2, $stats['total_goals']);
        $this->assertEquals(1, $stats['completed_goals']);
        $this->assertEquals(1, $stats['active_goals']);
        $this->assertArrayHasKey('goals_by_type', $stats);
        $this->assertArrayHasKey('average_progress', $stats);
    }
}