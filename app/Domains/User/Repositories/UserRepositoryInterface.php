<?php

declare(strict_types=1);

namespace App\Domains\User\Repositories;

use App\Domains\User\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Интерфейс репозитория для работы с пользователями
 *
 * Определяет контракт для доступа к данным пользователей
 */
interface UserRepositoryInterface
{
    /**
     * Найти пользователя по ID
     */
    public function findById(int $id): ?User;

    /**
     * Найти пользователя по email
     */
    public function findByEmail(string $email): ?User;

    /**
     * Создать нового пользователя
     */
    public function create(array $data): User;

    /**
     * Обновить данные пользователя
     */
    public function update(User $user, array $data): User;

    /**
     * Удалить пользователя
     */
    public function delete(User $user): bool;

    /**
     * Получить всех активных пользователей с пагинацией
     */
    public function getActive(int $perPage = 15): LengthAwarePaginator;

    /**
     * Получить пользователей, зарегистрировавшихся за период
     */
    public function getRegisteredBetween(string $startDate, string $endDate): Collection;

    /**
     * Найти пользователей по поисковому запросу
     */
    public function search(string $query, int $perPage = 15): LengthAwarePaginator;

    /**
     * Получить пользователей с наибольшим количеством сессий
     */
    public function getMostActive(int $limit = 10): Collection;

    /**
     * Получить неактивных пользователей
     */
    public function getInactive(int $days = 7): Collection;

    /**
     * Обновить статистику пользователя
     */
    public function updateStatistics(User $user, array $stats): User;

    /**
     * Очистить кеш
     */
    public function clearCache(): void;
}
