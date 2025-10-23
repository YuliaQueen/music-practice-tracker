<?php

declare(strict_types=1);

namespace App\Domains\Planning\Contracts;

use Carbon\Carbon;
use App\Domains\Planning\Models\Session;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Интерфейс репозитория сессий
 */
interface SessionRepositoryInterface
{
    /**
     * Найти сессию по ID
     *
     * @param int $id
     * @return Session|null
     */
    public function findById(int $id): ?Session;

    /**
     * Получить сессии пользователя с пагинацией
     *
     * @param int $userId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getForUser(int $userId, int $perPage = 10): LengthAwarePaginator;

    /**
     * Получить активные сессии пользователя
     *
     * @param int $userId
     * @return Collection
     */
    public function getActiveForUser(int $userId): Collection;

    /**
     * Получить завершенные сессии пользователя
     *
     * @param int    $userId
     * @param Carbon $from
     * @param Carbon $to
     * @return Collection
     */
    public function getCompletedInPeriod(int $userId, Carbon $from, Carbon $to): Collection;

    /**
     * Создать новую сессию
     *
     * @param array $data
     * @return Session
     */
    public function create(array $data): Session;

    /**
     * Обновить сессию
     *
     * @param Session $session
     * @param array   $data
     * @return Session
     */
    public function update(Session $session, array $data): Session;

    /**
     * Удалить сессию
     *
     * @param Session $session
     * @return bool
     */
    public function delete(Session $session): bool;

    /**
     * Получить статистику сессий пользователя
     *
     * @param int $userId
     * @return array
     */
    public function getUserStatistics(int $userId): array;
}
