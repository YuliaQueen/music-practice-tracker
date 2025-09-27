<?php

declare(strict_types=1);

namespace App\Policies;

use App\Domains\Planning\Models\Session;
use App\Domains\User\Models\User;

class SessionPolicy
{
    /**
     * Определить, может ли пользователь просматривать сессию
     */
    public function view(User $user, Session $session): bool
    {
        return $user->id === $session->user_id;
    }

    /**
     * Определить, может ли пользователь обновлять сессию
     */
    public function update(User $user, Session $session): bool
    {
        return $user->id === $session->user_id;
    }

    /**
     * Определить, может ли пользователь удалять сессию
     */
    public function delete(User $user, Session $session): bool
    {
        return $user->id === $session->user_id;
    }
}