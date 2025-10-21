<?php

declare(strict_types=1);

namespace App\Domains\User\Repositories;

use App\Domains\User\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Репозиторий для работы с пользователями
 *
 * Обеспечивает абстракцию доступа к данным пользователей с кешированием
 */
class UserRepository implements UserRepositoryInterface
{
    private const CACHE_TTL    = 3600; // 1 час
    private const CACHE_PREFIX = 'user:';

    /**
     * Найти пользователя по ID с кешированием
     */
    public function findById(int $id): ?User
    {
        return Cache::remember(
            self::CACHE_PREFIX . $id,
            self::CACHE_TTL,
            fn() => User::find($id)
        );
    }

    /**
     * Найти пользователя по email
     */
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * Создать нового пользователя
     */
    public function create(array $data): User
    {
        $user = User::create($data);

        // Очистить кеш при создании
        $this->clearUserCache($user->id);

        return $user;
    }

    /**
     * Очистить кеш пользователя
     */
    private function clearUserCache(int $userId): void
    {
        Cache::forget(self::CACHE_PREFIX . $userId);
    }

    /**
     * Удалить пользователя (soft delete)
     */
    public function delete(User $user): bool
    {
        $result = $user->delete();

        if ($result) {
            $this->clearUserCache($user->id);
        }

        return $result;
    }

    /**
     * Получить всех активных пользователей с пагинацией
     */
    public function getActive(int $perPage = 15): LengthAwarePaginator
    {
        return User::whereNotNull('email_verified_at')
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Получить пользователей, зарегистрировавшихся за период
     */
    public function getRegisteredBetween(string $startDate, string $endDate): Collection
    {
        return User::betweenDates($startDate, $endDate)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Найти пользователей по поисковому запросу
     */
    public function search(string $query, int $perPage = 15): LengthAwarePaginator
    {
        return User::where(function ($q) use ($query) {
            $q->where('name', 'ILIKE', "%{$query}%")
                ->orWhere('email', 'ILIKE', "%{$query}%");
        })
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Получить пользователей с наибольшим количеством сессий
     */
    public function getMostActive(int $limit = 10): Collection
    {
        return User::where('total_sessions', '>', 0)
            ->orderBy('total_sessions', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Получить пользователей, которые не занимались более N дней
     */
    public function getInactive(int $days = 7): Collection
    {
        return User::where('last_practice_date', '<', now()->subDays($days))
            ->orWhereNull('last_practice_date')
            ->whereNotNull('email_verified_at')
            ->whereNull('deleted_at')
            ->get();
    }

    /**
     * Обновить статистику пользователя
     */
    public function updateStatistics(User $user, array $stats): User
    {
        $user->update([
            'total_sessions'         => $stats['total_sessions'] ?? $user->total_sessions,
            'total_practice_minutes' => $stats['total_practice_minutes'] ?? $user->total_practice_minutes,
            'last_practice_date'     => $stats['last_practice_date'] ?? $user->last_practice_date,
        ]);

        $this->clearUserCache($user->id);

        return $user->fresh();
    }

    /**
     * Обновить данные пользователя
     */
    public function update(User $user, array $data): User
    {
        $user->update($data);

        // Очистить кеш при обновлении
        $this->clearUserCache($user->id);

        return $user->fresh();
    }

    /**
     * Очистить весь кеш пользователей
     */
    public function clearCache(): void
    {
        Cache::tags(['users'])->flush();
    }
}
