<?php

declare(strict_types=1);

namespace App\Services;

use Carbon\Carbon;
use App\Domains\User\Models\User;
use App\Domains\Goals\Models\Goal;
use App\Domains\Planning\Models\Session;
use App\Domains\Planning\Models\SessionBlock;
use App\Services\GoalProgress\Strategies\StreakDaysStrategy;
use App\Services\GoalProgress\Contracts\GoalProgressStrategy;
use App\Services\GoalProgress\Strategies\DailyMinutesStrategy;
use App\Services\GoalProgress\Strategies\ExerciseTypeStrategy;
use App\Services\GoalProgress\Strategies\MonthlyMinutesStrategy;
use App\Services\GoalProgress\Strategies\WeeklySessionsStrategy;
use App\Services\GoalProgress\Strategies\YearlySessionsStrategy;

class GoalProgressService
{
    /**
     * @var array<GoalProgressStrategy>
     */
    private array $strategies = [];

    public function __construct()
    {
        $this->strategies = [
            new DailyMinutesStrategy(),
            new WeeklySessionsStrategy(),
            new StreakDaysStrategy(),
            new ExerciseTypeStrategy(),
            new MonthlyMinutesStrategy(),
            new YearlySessionsStrategy(),
        ];
    }

    /**
     * Получить стратегию для конкретной цели
     */
    private function getStrategy(Goal $goal): ?GoalProgressStrategy
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->supports($goal)) {
                return $strategy;
            }
        }

        return null;
    }

    /**
     * Обновить прогресс всех активных целей пользователя на основе сессий
     */
    public function updateProgressFromSessions(User $user, ?Carbon $fromDate = null, ?Carbon $toDate = null): array
    {
        $fromDate = $fromDate ?? Carbon::now()->startOfDay();
        $toDate = $toDate ?? Carbon::now()->endOfDay();

        $activeGoals = $user->goals()->active()->get();
        $updatedGoals = [];

        foreach ($activeGoals as $goal) {
            $updatedGoal = $this->updateGoalProgress($goal, $fromDate, $toDate);
            $updatedGoals[] = $updatedGoal; // Всегда возвращаем обновленную цель
        }

        return $updatedGoals;
    }

    /**
     * Обновить прогресс конкретной цели на основе сессий
     */
    public function updateGoalProgress(Goal $goal, ?Carbon $fromDate = null, ?Carbon $toDate = null): ?Goal
    {
        $fromDate = $fromDate ?? Carbon::now()->startOfDay();
        $toDate = $toDate ?? Carbon::now()->endOfDay();

        $currentValue = $this->calculateCurrentValue($goal, $fromDate, $toDate);
        $totalValue = $this->calculateTotalValue($goal, $fromDate, $toDate);

        // Всегда обновляем прогресс, даже если значение не изменилось
        $goal->updateProgress($currentValue, $totalValue);
        return $goal;
    }

    /**
     * Рассчитать текущее значение для цели на основе сессий
     */
    private function calculateCurrentValue(Goal $goal, Carbon $fromDate, Carbon $toDate): int
    {
        $strategy = $this->getStrategy($goal);

        if (!$strategy) {
            return 0;
        }

        return $strategy->calculateCurrentValue($goal->user, $goal, $fromDate, $toDate);
    }

    /**
     * Рассчитать общее значение для цели
     */
    private function calculateTotalValue(Goal $goal, Carbon $fromDate, Carbon $toDate): int
    {
        $strategy = $this->getStrategy($goal);

        if (!$strategy) {
            return $goal->getTargetValue();
        }

        return $strategy->calculateTotalValue($goal, $fromDate, $toDate);
    }


    /**
     * Обновить прогресс целей после завершения сессии
     */
    public function updateProgressAfterSession(Session $session): array
    {
        $user = $session->user;
        $sessionDate = $session->created_at;

        // Обновляем прогресс за день сессии
        $dayStart = $sessionDate->copy()->startOfDay();
        $dayEnd = $sessionDate->copy()->endOfDay();

        return $this->updateProgressFromSessions($user, $dayStart, $dayEnd);
    }

    /**
     * Обновить прогресс целей после завершения блока сессии
     */
    public function updateProgressAfterSessionBlock(SessionBlock $sessionBlock): array
    {
        $session = $sessionBlock->session;
        $user = $session->user;
        $sessionDate = $session->created_at;

        // Обновляем прогресс за день сессии
        $dayStart = $sessionDate->copy()->startOfDay();
        $dayEnd = $sessionDate->copy()->endOfDay();

        return $this->updateProgressFromSessions($user, $dayStart, $dayEnd);
    }

    /**
     * Проверить и отметить завершенные цели
     */
    public function checkAndCompleteGoals(User $user): array
    {
        $completedGoals = [];
        $activeGoals = $user->goals()->active()->get();

        foreach ($activeGoals as $goal) {
            if ($goal->getProgressPercentage() >= 100 && !$goal->is_completed) {
                $goal->markAsCompleted();
                $completedGoals[] = $goal;
            }
        }

        return $completedGoals;
    }

    /**
     * Получить статистику прогресса целей пользователя
     */
    public function getGoalProgressStats(User $user, ?Carbon $fromDate = null, ?Carbon $toDate = null): array
    {
        $fromDate = $fromDate ?? Carbon::now()->subMonth();
        $toDate = $toDate ?? Carbon::now();

        $goals = $user->goals()->active()->get();
        $stats = [
            'total_goals'   => $goals->count(),
            'completed_goals' => $goals->where('is_completed', true)->count(),
            'active_goals'  => $goals->where('is_completed', false)->count(),
            'goals_by_type' => [],
            'average_progress' => 0,
        ];

        $totalProgress = 0;

        foreach ($goals as $goal) {
            $type = $goal->type;
            if (!isset($stats['goals_by_type'][$type])) {
                $stats['goals_by_type'][$type] = [
                    'count' => 0,
                    'completed' => 0,
                    'average_progress' => 0,
                ];
            }

            $stats['goals_by_type'][$type]['count']++;
            if ($goal->is_completed) {
                $stats['goals_by_type'][$type]['completed']++;
            }

            $progress = $goal->getProgressPercentage();
            $stats['goals_by_type'][$type]['average_progress'] += $progress;
            $totalProgress += $progress;
        }

        // Рассчитываем средний прогресс
        if ($goals->count() > 0) {
            $stats['average_progress'] = round($totalProgress / $goals->count(), 2);
        }

        // Рассчитываем средний прогресс по типам
        foreach ($stats['goals_by_type'] as $type => &$typeStats) {
            if ($typeStats['count'] > 0) {
                $typeStats['average_progress'] = round($typeStats['average_progress'] / $typeStats['count'], 2);
            }
        }

        return $stats;
    }
}
