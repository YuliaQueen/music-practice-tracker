<?php

declare(strict_types=1);

namespace App\Services\GoalProgress\Contracts;

use App\Domains\Goals\Models\Goal;
use App\Domains\User\Models\User;
use Carbon\Carbon;

interface GoalProgressStrategy
{
    /**
     * Рассчитать текущее значение прогресса
     */
    public function calculateCurrentValue(User $user, Goal $goal, Carbon $fromDate, Carbon $toDate): int;

    /**
     * Рассчитать общее целевое значение
     */
    public function calculateTotalValue(Goal $goal, Carbon $fromDate, Carbon $toDate): int;

    /**
     * Проверить, поддерживается ли данный тип цели
     */
    public function supports(Goal $goal): bool;
}
