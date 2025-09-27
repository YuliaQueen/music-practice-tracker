<?php

declare(strict_types=1);

namespace App\Policies;

use App\Domains\Planning\Models\Exercise;
use App\Domains\User\Models\User;

class ExercisePolicy
{
    /**
     * Определить, может ли пользователь просматривать упражнение
     */
    public function view(User $user, Exercise $exercise): bool
    {
        return $user->id === $exercise->user_id;
    }

    /**
     * Определить, может ли пользователь обновлять упражнение
     */
    public function update(User $user, Exercise $exercise): bool
    {
        return $user->id === $exercise->user_id;
    }

    /**
     * Определить, может ли пользователь удалять упражнение
     */
    public function delete(User $user, Exercise $exercise): bool
    {
        return $user->id === $exercise->user_id;
    }
}