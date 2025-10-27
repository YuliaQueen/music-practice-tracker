<?php

declare(strict_types=1);

namespace App\Domains\Planning\Contracts;

use App\Domains\Planning\Models\Exercise;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Интерфейс репозитория упражнений
 */
interface ExerciseRepositoryInterface
{
    /**
     * Найти упражнение по ID
     *
     * @param int $id
     * @return Exercise|null
     */
    public function findById(int $id): ?Exercise;

    /**
     * Получить активные упражнения пользователя с пагинацией
     *
     * @param int   $userId
     * @param int   $perPage
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getActiveForUser(int $userId, int $perPage = 10, array $filters = []): LengthAwarePaginator;

    /**
     * Получить архивные упражнения пользователя с пагинацией
     *
     * @param int $userId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getArchivedForUser(int $userId, int $perPage = 10): LengthAwarePaginator;

    /**
     * Создать новое упражнение
     *
     * @param array $data
     * @return Exercise
     */
    public function create(array $data): Exercise;

    /**
     * Обновить упражнение
     *
     * @param Exercise $exercise
     * @param array    $data
     * @return Exercise
     */
    public function update(Exercise $exercise, array $data): Exercise;

    /**
     * Удалить упражнение
     *
     * @param Exercise $exercise
     * @return bool
     */
    public function delete(Exercise $exercise): bool;
}
