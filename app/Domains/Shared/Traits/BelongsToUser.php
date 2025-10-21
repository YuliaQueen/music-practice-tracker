<?php

declare(strict_types=1);

namespace App\Domains\Shared\Traits;

use App\Domains\User\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToUser
{
    /**
     * Связь с пользователем
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: записи пользователя
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Проверить, принадлежит ли запись указанному пользователю
     */
    public function belongsToUser(User $user): bool
    {
        return $this->user_id === $user->id;
    }

    /**
     * Scope: только записи текущего пользователя
     */
    public function scopeOwned(Builder $query): Builder
    {
        return $query->where('user_id', auth()->id());
    }
}
