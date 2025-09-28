<?php

declare(strict_types=1);

namespace App\Policies;

use App\Domains\Goals\Models\Goal;
use App\Domains\User\Models\User;

class GoalPolicy
{
    /**
     * Определить, может ли пользователь просматривать любые цели.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Определить, может ли пользователь просматривать цель.
     */
    public function view(User $user, Goal $goal): bool
    {
        return $user->id === $goal->user_id;
    }

    /**
     * Определить, может ли пользователь создавать цели.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Определить, может ли пользователь обновлять цель.
     */
    public function update(User $user, Goal $goal): bool
    {
        return $user->id === $goal->user_id;
    }

    /**
     * Определить, может ли пользователь удалять цель.
     */
    public function delete(User $user, Goal $goal): bool
    {
        return $user->id === $goal->user_id;
    }

    /**
     * Определить, может ли пользователь восстанавливать цель.
     */
    public function restore(User $user, Goal $goal): bool
    {
        return $user->id === $goal->user_id;
    }

    /**
     * Определить, может ли пользователь окончательно удалять цель.
     */
    public function forceDelete(User $user, Goal $goal): bool
    {
        return $user->id === $goal->user_id;
    }
}