<?php

declare(strict_types=1);

namespace App\Services\GoalProgress\Strategies;

use App\Domains\Goals\Models\Goal;
use App\Domains\Planning\Models\Session;
use App\Domains\User\Models\User;
use App\Enums\GoalType;
use App\Enums\SessionBlockStatus;
use App\Enums\SessionStatus;
use App\Services\GoalProgress\Contracts\GoalProgressStrategy;
use Carbon\Carbon;

class ExerciseTypeStrategy implements GoalProgressStrategy
{
    public function supports(Goal $goal): bool
    {
        return $goal->type === GoalType::EXERCISE_TYPE;
    }

    public function calculateCurrentValue(User $user, Goal $goal, Carbon $fromDate, Carbon $toDate): int
    {
        $exerciseType = $goal->target['exercise_type'] ?? null;
        if (!$exerciseType) {
            return 0;
        }

        $sessions = $user->sessions()
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->where('status', SessionStatus::COMPLETED->value)
            ->with(['blocks'])
            ->get();

        return $sessions->sum(function (Session $session) use ($exerciseType) {
            return $session->blocks()
                ->where('status', SessionBlockStatus::COMPLETED->value)
                ->where('type', $exerciseType)
                ->sum('actual_duration');
        });
    }

    public function calculateTotalValue(Goal $goal, Carbon $fromDate, Carbon $toDate): int
    {
        // Для типа упражнения общее значение - это целевое значение за день
        return $goal->getTargetValue();
    }
}
