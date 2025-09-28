<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Domains\Goals\Models\Goal;
use App\Domains\User\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoalTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_can_create_a_goal()
    {
        $goal = Goal::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Практиковать 30 минут в день',
            'type' => Goal::TYPE_DAILY_MINUTES,
            'target' => ['value' => 30, 'period' => 'daily'],
        ]);

        $this->assertInstanceOf(Goal::class, $goal);
        $this->assertEquals('Практиковать 30 минут в день', $goal->title);
        $this->assertEquals(Goal::TYPE_DAILY_MINUTES, $goal->type);
        $this->assertEquals($this->user->id, $goal->user_id);
        $this->assertTrue($goal->is_active);
        $this->assertFalse($goal->is_completed);
    }

    /** @test */
    public function it_can_get_type_label()
    {
        $goal = Goal::factory()->create(['type' => Goal::TYPE_DAILY_MINUTES]);
        
        $this->assertEquals('Ежедневные минуты', $goal->getTypeLabel());
        
        $goal->type = Goal::TYPE_WEEKLY_SESSIONS;
        $this->assertEquals('Еженедельные сессии', $goal->getTypeLabel());
        
        $goal->type = Goal::TYPE_STREAK_DAYS;
        $this->assertEquals('Серия дней', $goal->getTypeLabel());
    }

    /** @test */
    public function it_can_get_type_icon()
    {
        $goal = Goal::factory()->create(['type' => Goal::TYPE_DAILY_MINUTES]);
        
        $this->assertEquals('📅', $goal->getTypeIcon());
        
        $goal->type = Goal::TYPE_WEEKLY_SESSIONS;
        $this->assertEquals('📊', $goal->getTypeIcon());
        
        $goal->type = Goal::TYPE_STREAK_DAYS;
        $this->assertEquals('🔥', $goal->getTypeIcon());
    }

    /** @test */
    public function it_can_get_type_color()
    {
        $goal = Goal::factory()->create(['type' => Goal::TYPE_DAILY_MINUTES]);
        
        $this->assertEquals('blue', $goal->getTypeColor());
        
        $goal->type = Goal::TYPE_WEEKLY_SESSIONS;
        $this->assertEquals('green', $goal->getTypeColor());
        
        $goal->type = Goal::TYPE_STREAK_DAYS;
        $this->assertEquals('orange', $goal->getTypeColor());
    }

    /** @test */
    public function it_can_get_target_value()
    {
        $goal = Goal::factory()->create([
            'target' => ['value' => 45, 'period' => 'daily']
        ]);
        
        $this->assertEquals(45, $goal->getTargetValue());
    }

    /** @test */
    public function it_can_get_current_value()
    {
        $goal = Goal::factory()->create([
            'progress' => ['current' => 25, 'total' => 30]
        ]);
        
        $this->assertEquals(25, $goal->getCurrentValue());
    }

    /** @test */
    public function it_returns_zero_for_current_value_when_no_progress()
    {
        $goal = Goal::factory()->create(['progress' => null]);
        
        $this->assertEquals(0, $goal->getCurrentValue());
    }

    /** @test */
    public function it_can_calculate_progress_percentage()
    {
        $goal = Goal::factory()->create([
            'target' => ['value' => 100],
            'progress' => ['current' => 50, 'total' => 100]
        ]);
        
        $this->assertEquals(50, $goal->getProgressPercentage());
    }

    /** @test */
    public function it_can_calculate_progress_percentage_with_different_totals()
    {
        $goal = Goal::factory()->create([
            'target' => ['value' => 100],
            'progress' => ['current' => 75, 'total' => 100]
        ]);
        
        $this->assertEquals(75, $goal->getProgressPercentage());
    }

    /** @test */
    public function it_caps_progress_percentage_at_100()
    {
        $goal = Goal::factory()->create([
            'target' => ['value' => 100],
            'progress' => ['current' => 150, 'total' => 100]
        ]);
        
        $this->assertEquals(100, $goal->getProgressPercentage());
    }

    /** @test */
    public function it_returns_zero_percentage_when_no_progress()
    {
        $goal = Goal::factory()->create([
            'target' => ['value' => 100],
            'progress' => null
        ]);
        
        $this->assertEquals(0, $goal->getProgressPercentage());
    }

    /** @test */
    public function it_can_get_remaining_value()
    {
        $goal = Goal::factory()->create([
            'target' => ['value' => 100],
            'progress' => ['current' => 30, 'total' => 100]
        ]);
        
        $this->assertEquals(70, $goal->getRemaining());
    }

    /** @test */
    public function it_returns_zero_remaining_when_goal_exceeded()
    {
        $goal = Goal::factory()->create([
            'target' => ['value' => 100],
            'progress' => ['current' => 150, 'total' => 100]
        ]);
        
        $this->assertEquals(0, $goal->getRemaining());
    }

    /** @test */
    public function it_can_update_progress()
    {
        $goal = Goal::factory()->create([
            'target' => ['value' => 100],
            'progress' => null
        ]);
        
        $goal->updateProgress(25, 100);
        
        $this->assertEquals(25, $goal->getCurrentValue());
        $this->assertEquals(100, $goal->progress['total']);
        $this->assertEquals(25, $goal->getProgressPercentage());
    }

    /** @test */
    public function it_marks_goal_as_completed_when_progress_reaches_target()
    {
        $goal = Goal::factory()->create([
            'target' => ['value' => 100],
            'progress' => null,
            'is_completed' => false
        ]);
        
        $goal->updateProgress(100, 100);
        
        $this->assertTrue($goal->is_completed);
        $this->assertNotNull($goal->completed_at);
    }

    /** @test */
    public function it_can_mark_goal_as_completed()
    {
        $goal = Goal::factory()->create([
            'is_completed' => false,
            'completed_at' => null
        ]);
        
        $goal->markAsCompleted();
        
        $this->assertTrue($goal->is_completed);
        $this->assertNotNull($goal->completed_at);
    }

    /** @test */
    public function it_can_check_if_goal_is_active()
    {
        $activeGoal = Goal::factory()->create(['is_active' => true, 'is_completed' => false]);
        $inactiveGoal = Goal::factory()->create(['is_active' => false]);
        $completedGoal = Goal::factory()->create(['is_active' => true, 'is_completed' => true]);
        
        $this->assertTrue($activeGoal->isActive());
        $this->assertFalse($inactiveGoal->isActive());
        $this->assertFalse($completedGoal->isActive());
    }

    /** @test */
    public function it_can_check_if_goal_is_completed()
    {
        $completedGoal = Goal::factory()->create(['is_completed' => true]);
        $incompleteGoal = Goal::factory()->create(['is_completed' => false]);
        
        $this->assertTrue($completedGoal->isCompleted());
        $this->assertFalse($incompleteGoal->isCompleted());
    }

    /** @test */
    public function it_can_check_if_goal_is_expired()
    {
        $expiredGoal = Goal::factory()->create([
            'end_date' => Carbon::yesterday()
        ]);
        $activeGoal = Goal::factory()->create([
            'end_date' => Carbon::tomorrow()
        ]);
        $noEndDateGoal = Goal::factory()->create(['end_date' => null]);
        
        $this->assertTrue($expiredGoal->isExpired());
        $this->assertFalse($activeGoal->isExpired());
        $this->assertFalse($noEndDateGoal->isExpired());
    }

    /** @test */
    public function it_can_get_description()
    {
        $goal = Goal::factory()->create([
            'type' => Goal::TYPE_DAILY_MINUTES,
            'target' => ['value' => 30, 'period' => 'daily']
        ]);
        
        $this->assertEquals('Практиковать 30 минут в день', $goal->getDescription());
        
        $goal->type = Goal::TYPE_WEEKLY_SESSIONS;
        $goal->target = ['value' => 5, 'period' => 'weekly'];
        $this->assertEquals('Провести 5 сессий в неделю', $goal->getDescription());
    }

    /** @test */
    public function it_can_scope_active_goals()
    {
        Goal::factory()->active()->create(['user_id' => $this->user->id]);
        Goal::factory()->create(['user_id' => $this->user->id, 'is_active' => false]);
        
        $activeGoals = Goal::active()->get();
        
        $this->assertCount(1, $activeGoals);
        $this->assertTrue($activeGoals->first()->is_active);
    }

    /** @test */
    public function it_can_scope_completed_goals()
    {
        Goal::factory()->completed()->create(['user_id' => $this->user->id]);
        Goal::factory()->create(['user_id' => $this->user->id, 'is_completed' => false]);
        
        $completedGoals = Goal::completed()->get();
        
        $this->assertCount(1, $completedGoals);
        $this->assertTrue($completedGoals->first()->is_completed);
    }

    /** @test */
    public function it_can_scope_goals_for_user()
    {
        $otherUser = User::factory()->create();
        
        Goal::factory()->create(['user_id' => $this->user->id]);
        Goal::factory()->create(['user_id' => $otherUser->id]);
        
        $userGoals = Goal::forUser($this->user->id)->get();
        
        $this->assertCount(1, $userGoals);
        $this->assertEquals($this->user->id, $userGoals->first()->user_id);
    }

    /** @test */
    public function it_can_scope_goals_by_type()
    {
        Goal::factory()->create([
            'user_id' => $this->user->id,
            'type' => Goal::TYPE_DAILY_MINUTES
        ]);
        Goal::factory()->create([
            'user_id' => $this->user->id,
            'type' => Goal::TYPE_WEEKLY_SESSIONS
        ]);
        
        $dailyGoals = Goal::ofType(Goal::TYPE_DAILY_MINUTES)->get();
        
        $this->assertCount(1, $dailyGoals);
        $this->assertEquals(Goal::TYPE_DAILY_MINUTES, $dailyGoals->first()->type);
    }

    /** @test */
    public function it_can_scope_current_goals()
    {
        // Активная, не завершенная цель без даты окончания
        Goal::factory()->create([
            'user_id' => $this->user->id,
            'is_active' => true,
            'is_completed' => false,
            'end_date' => null
        ]);
        
        // Активная цель с будущей датой окончания
        Goal::factory()->create([
            'user_id' => $this->user->id,
            'is_active' => true,
            'is_completed' => false,
            'end_date' => Carbon::tomorrow()
        ]);
        
        // Неактивная цель
        Goal::factory()->create([
            'user_id' => $this->user->id,
            'is_active' => false
        ]);
        
        // Завершенная цель
        Goal::factory()->create([
            'user_id' => $this->user->id,
            'is_active' => true,
            'is_completed' => true
        ]);
        
        // Истекшая цель
        Goal::factory()->create([
            'user_id' => $this->user->id,
            'is_active' => true,
            'is_completed' => false,
            'end_date' => Carbon::yesterday()
        ]);
        
        $currentGoals = Goal::current()->get();
        
        $this->assertCount(2, $currentGoals);
        foreach ($currentGoals as $goal) {
            $this->assertTrue($goal->is_active);
            $this->assertFalse($goal->is_completed);
            $this->assertFalse($goal->isExpired());
        }
    }

    /** @test */
    public function it_belongs_to_user()
    {
        $goal = Goal::factory()->create(['user_id' => $this->user->id]);
        
        $this->assertInstanceOf(User::class, $goal->user);
        $this->assertEquals($this->user->id, $goal->user->id);
    }

    /** @test */
    public function it_casts_target_and_progress_as_array()
    {
        $goal = Goal::factory()->create([
            'target' => ['value' => 30, 'period' => 'daily'],
            'progress' => ['current' => 15, 'total' => 30]
        ]);
        
        $this->assertIsArray($goal->target);
        $this->assertIsArray($goal->progress);
        $this->assertEquals(30, $goal->target['value']);
        $this->assertEquals(15, $goal->progress['current']);
    }

    /** @test */
    public function it_casts_dates_correctly()
    {
        $goal = Goal::factory()->create([
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
            'completed_at' => '2024-06-15 10:30:00'
        ]);
        
        $this->assertInstanceOf(Carbon::class, $goal->start_date);
        $this->assertInstanceOf(Carbon::class, $goal->end_date);
        $this->assertInstanceOf(Carbon::class, $goal->completed_at);
    }

    /** @test */
    public function it_casts_booleans_correctly()
    {
        $goal = Goal::factory()->create([
            'is_active' => true,
            'is_completed' => false
        ]);
        
        $this->assertTrue($goal->is_active);
        $this->assertFalse($goal->is_completed);
    }
}