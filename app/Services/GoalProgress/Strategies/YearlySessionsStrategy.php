<?php

declare(strict_types=1);

namespace App\Services\GoalProgress\Strategies;

use App\Domains\Goals\Models\Goal;
use App\Domains\User\Models\User;
use App\Enums\GoalType;
use App\Enums\SessionStatus;
use App\Services\GoalProgress\Contracts\GoalProgressStrategy;
use Carbon\Carbon;

class YearlySessionsStrategy implements GoalProgressStrategy
{
    public function supports(Goal $goal): bool
    {
        return $goal->type === GoalType::YEARLY_SESSIONS;
    }

    public function calculateCurrentValue(User $user, Goal $goal, Carbon $fromDate, Carbon $toDate): int
    {
        return $user->sessions()
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->where('status', SessionStatus::COMPLETED->value)
            ->count();
    }

    public function calculateTotalValue(Goal $goal, Carbon $fromDate, Carbon $toDate): int
    {
        $targetValue = $goal->getTargetValue();
        $weeksInPeriod = (int) ceil($fromDate->diffInDays($toDate) / 7);

        return $targetValue * $weeksInPeriod;
    }
}
