<?php

declare(strict_types=1);

namespace App\Services;

use App\Domains\Goals\Models\Goal;
use App\Domains\Planning\Models\Session;
use App\Domains\Planning\Models\SessionBlock;
use App\Domains\User\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class GoalProgressService
{
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
        return match ($goal->type) {
            Goal::TYPE_DAILY_MINUTES => $this->calculateDailyMinutes($goal->user, $fromDate, $toDate),
            Goal::TYPE_WEEKLY_SESSIONS => $this->calculateWeeklySessions($goal->user, $fromDate, $toDate),
            Goal::TYPE_STREAK_DAYS => $this->calculateStreakDays($goal->user, $fromDate, $toDate),
            Goal::TYPE_EXERCISE_TYPE => $this->calculateExerciseTypeProgress($goal, $fromDate, $toDate),
            Goal::TYPE_MONTHLY_MINUTES => $this->calculateMonthlyMinutes($goal->user, $fromDate, $toDate),
            Goal::TYPE_YEARLY_SESSIONS => $this->calculateYearlySessions($goal->user, $fromDate, $toDate),
            default => 0,
        };
    }

    /**
     * Рассчитать общее значение для цели
     */
    private function calculateTotalValue(Goal $goal, Carbon $fromDate, Carbon $toDate): int
    {
        return match ($goal->type) {
            Goal::TYPE_DAILY_MINUTES => $this->calculateDailyTotal($goal, $fromDate, $toDate),
            Goal::TYPE_WEEKLY_SESSIONS => $this->calculateWeeklyTotal($goal, $fromDate, $toDate),
            Goal::TYPE_STREAK_DAYS => $this->calculateStreakTotal($goal, $fromDate, $toDate),
            Goal::TYPE_EXERCISE_TYPE => $this->calculateExerciseTypeTotal($goal, $fromDate, $toDate),
            Goal::TYPE_MONTHLY_MINUTES => $this->calculateMonthlyTotal($goal, $fromDate, $toDate),
            Goal::TYPE_YEARLY_SESSIONS => $this->calculateYearlyTotal($goal, $fromDate, $toDate),
            default => $goal->getTargetValue(),
        };
    }

    /**
     * Рассчитать ежедневные минуты практики
     */
    private function calculateDailyMinutes(User $user, Carbon $fromDate, Carbon $toDate): int
    {
        $sessions = $this->getSessionsInPeriod($user, $fromDate, $toDate);
        
        return $sessions->sum(function (Session $session) {
            return $session->blocks()
                ->where('status', SessionBlock::STATUS_COMPLETED)
                ->sum('actual_duration');
        });
    }

    /**
     * Рассчитать еженедельные сессии
     */
    private function calculateWeeklySessions(User $user, Carbon $fromDate, Carbon $toDate): int
    {
        // Для еженедельных целей считаем сессии за неделю
        $weekStart = $fromDate->copy()->startOfWeek();
        $weekEnd = $toDate->copy()->endOfWeek();
        
        $sessions = $this->getSessionsInPeriod($user, $weekStart, $weekEnd);
        
        return $sessions->count();
    }

    /**
     * Рассчитать серию дней практики
     */
    private function calculateStreakDays(User $user, Carbon $fromDate, Carbon $toDate): int
    {
        // Для расчета серии дней нужно получить сессии за больший период
        $extendedFromDate = $fromDate->copy()->subDays(30); // Проверяем последние 30 дней
        
        $sessions = $this->getSessionsInPeriod($user, $extendedFromDate, $toDate);
        
        // Группируем сессии по дням
        $daysWithPractice = $sessions
            ->groupBy(function (Session $session) {
                return $session->created_at->format('Y-m-d');
            })
            ->keys()
            ->sort()
            ->values();
        
        if ($daysWithPractice->isEmpty()) {
            return 0;
        }
        
        // Рассчитываем текущую серию дней
        $currentStreak = 0;
        $currentDate = $toDate->copy()->startOfDay();
        
        while ($currentDate->gte($extendedFromDate)) {
            $dateString = $currentDate->format('Y-m-d');
            
            if ($daysWithPractice->contains($dateString)) {
                $currentStreak++;
                $currentDate->subDay();
            } else {
                break;
            }
        }
        
        return $currentStreak;
    }

    /**
     * Рассчитать прогресс по типу упражнения
     */
    private function calculateExerciseTypeProgress(Goal $goal, Carbon $fromDate, Carbon $toDate): int
    {
        $exerciseType = $goal->target['exercise_type'] ?? null;
        if (!$exerciseType) {
            return 0;
        }
        
        $sessions = $this->getSessionsInPeriod($goal->user, $fromDate, $toDate);
        
        return $sessions->sum(function (Session $session) use ($exerciseType) {
            return $session->blocks()
                ->where('status', SessionBlock::STATUS_COMPLETED)
                ->where('type', $exerciseType)
                ->sum('actual_duration');
        });
    }

    /**
     * Рассчитать месячные минуты практики
     */
    private function calculateMonthlyMinutes(User $user, Carbon $fromDate, Carbon $toDate): int
    {
        return $this->calculateDailyMinutes($user, $fromDate, $toDate);
    }

    /**
     * Рассчитать годовые сессии
     */
    private function calculateYearlySessions(User $user, Carbon $fromDate, Carbon $toDate): int
    {
        return $this->calculateWeeklySessions($user, $fromDate, $toDate);
    }

    /**
     * Рассчитать общее значение для ежедневных минут
     */
    private function calculateDailyTotal(Goal $goal, Carbon $fromDate, Carbon $toDate): int
    {
        // Для ежедневных целей общее значение - это целевое значение за день
        return $goal->getTargetValue();
    }

    /**
     * Рассчитать общее значение для еженедельных сессий
     */
    private function calculateWeeklyTotal(Goal $goal, Carbon $fromDate, Carbon $toDate): int
    {
        $targetValue = $goal->getTargetValue();
        $weeksInPeriod = (int) ceil($fromDate->diffInDays($toDate) / 7);
        
        return $targetValue * $weeksInPeriod;
    }

    /**
     * Рассчитать общее значение для серии дней
     */
    private function calculateStreakTotal(Goal $goal, Carbon $fromDate, Carbon $toDate): int
    {
        return $goal->getTargetValue();
    }

    /**
     * Рассчитать общее значение для типа упражнения
     */
    private function calculateExerciseTypeTotal(Goal $goal, Carbon $fromDate, Carbon $toDate): int
    {
        // Для типа упражнения общее значение - это целевое значение за день
        return $goal->getTargetValue();
    }

    /**
     * Рассчитать общее значение для месячных минут
     */
    private function calculateMonthlyTotal(Goal $goal, Carbon $fromDate, Carbon $toDate): int
    {
        return $this->calculateDailyTotal($goal, $fromDate, $toDate);
    }

    /**
     * Рассчитать общее значение для годовых сессий
     */
    private function calculateYearlyTotal(Goal $goal, Carbon $fromDate, Carbon $toDate): int
    {
        return $this->calculateWeeklyTotal($goal, $fromDate, $toDate);
    }

    /**
     * Получить сессии пользователя за период
     */
    private function getSessionsInPeriod(User $user, Carbon $fromDate, Carbon $toDate): Collection
    {
        return $user->sessions()
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->where('status', Session::STATUS_COMPLETED)
            ->with(['blocks'])
            ->get();
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
            'total_goals' => $goals->count(),
            'completed_goals' => $goals->where('is_completed', true)->count(),
            'active_goals' => $goals->where('is_completed', false)->count(),
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