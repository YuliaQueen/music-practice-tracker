<?php

declare(strict_types=1);

namespace App\Services\GoalProgress\Strategies;

use App\Domains\Goals\Models\Goal;
use App\Domains\Planning\Models\Session;
use App\Domains\User\Models\User;
use App\Enums\GoalType;
use App\Enums\SessionStatus;
use App\Services\GoalProgress\Contracts\GoalProgressStrategy;
use Carbon\Carbon;

class StreakDaysStrategy implements GoalProgressStrategy
{
    public function supports(Goal $goal): bool
    {
        return $goal->type === GoalType::STREAK_DAYS;
    }

    public function calculateCurrentValue(User $user, Goal $goal, Carbon $fromDate, Carbon $toDate): int
    {
        // Для расчета серии дней нужно получить сессии за больший период
        $extendedFromDate = $fromDate->copy()->subDays(30); // Проверяем последние 30 дней

        $sessions = $user->sessions()
            ->whereBetween('created_at', [$extendedFromDate, $toDate])
            ->where('status', SessionStatus::COMPLETED->value)
            ->get();

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

    public function calculateTotalValue(Goal $goal, Carbon $fromDate, Carbon $toDate): int
    {
        return $goal->getTargetValue();
    }
}
