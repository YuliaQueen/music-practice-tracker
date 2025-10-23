<?php

declare(strict_types=1);

namespace App\Domains\Planning\Repositories;

use App\Domains\Planning\Models\Exercise;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Domains\Planning\Contracts\ExerciseRepositoryInterface;

/**
 * Репозиторий для работы с упражнениями
 */
class ExerciseRepository implements ExerciseRepositoryInterface
{
    /**
     * Найти упражнение по ID
     *
     * @param int $id
     * @return Exercise|null
     */
    public function findById(int $id): ?Exercise
    {
        return Exercise::find($id);
    }

    /**
     * Получить упражнения пользователя с пагинацией
     *
     * @param int   $userId
     * @param int   $perPage
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getForUser(int $userId, int $perPage = 10, array $filters = []): LengthAwarePaginator
    {
        $query = Exercise::forUser($userId);

        // Фильтр по названию
        if (!empty($filters['search'])) {
            $query->where('title', 'like', '%' . $filters['search'] . '%');
        }

        // Фильтр по типу
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Создать новое упражнение
     *
     * @param array $data
     * @return Exercise
     */
    public function create(array $data): Exercise
    {
        return Exercise::create($data);
    }

    /**
     * Обновить упражнение
     *
     * @param Exercise $exercise
     * @param array    $data
     * @return Exercise
     */
    public function update(Exercise $exercise, array $data): Exercise
    {
        $exercise->update($data);

        return $exercise->fresh();
    }

    /**
     * Удалить упражнение
     *
     * @param Exercise $exercise
     * @return bool
     */
    public function delete(Exercise $exercise): bool
    {
        return $exercise->delete();
    }
}
