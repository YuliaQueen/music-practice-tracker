<?php

declare(strict_types=1);

namespace App\Services\GoalProgress\Strategies;

use App\Domains\Goals\Models\Goal;
use App\Domains\Planning\Models\Session;
use App\Domains\Planning\Models\SessionBlock;
use App\Domains\User\Models\User;
use App\Enums\GoalType;
use App\Enums\SessionBlockStatus;
use App\Enums\SessionStatus;
use App\Services\GoalProgress\Contracts\GoalProgressStrategy;
use Carbon\Carbon;

class DailyMinutesStrategy implements GoalProgressStrategy
{
    public function supports(Goal $goal): bool
    {
        return $goal->type === GoalType::DAILY_MINUTES;
    }

    public function calculateCurrentValue(User $user, Goal $goal, Carbon $fromDate, Carbon $toDate): int
    {
        $sessions = $this->getSessionsInPeriod($user, $fromDate, $toDate);

        return $sessions->sum(function (Session $session) {
            return $session->blocks()
                ->where('status', SessionBlockStatus::COMPLETED->value)
                ->sum('actual_duration');
        });
    }

    public function calculateTotalValue(Goal $goal, Carbon $fromDate, Carbon $toDate): int
    {
        // Для ежедневных целей общее значение - это целевое значение за день
        return $goal->getTargetValue();
    }

    private function getSessionsInPeriod(User $user, Carbon $fromDate, Carbon $toDate)
    {
        return $user->sessions()
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->where('status', SessionStatus::COMPLETED->value)
            ->with(['blocks'])
            ->get();
    }
}
