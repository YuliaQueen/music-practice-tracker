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
     */
    public function findById(int $id): ?Exercise
    {
        return Exercise::find($id);
    }

    /**
     * Получить активные упражнения пользователя с пагинацией
     *
     * @param int   $userId
     * @param int   $perPage
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getActiveForUser(int $userId, int $perPage = 10, array $filters = []): LengthAwarePaginator
    {
        $query = Exercise::forUser($userId)->notArchived();

        if (!empty($filters['search'])) {
            $query->where('title', 'like', '%' . $filters['search'] . '%');
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Получить архивные упражнения пользователя с пагинацией
     *
     * @param int $userId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getArchivedForUser(int $userId, int $perPage = 10): LengthAwarePaginator
    {
        $paginator = Exercise::forUser($userId)
            ->archived()
            ->orderBy('archived_at', 'desc')
            ->paginate($perPage);

        $paginator->getCollection()->each(function ($exercise) {
            $exercise->append(['sessions_count', 'total_practice_time', 'average_practice_time', 'learning_days']);
        });

        return $paginator;
    }

    /**
     * Создать новое упражнение
     */
    public function create(array $data): Exercise
    {
        return Exercise::create($data);
    }

    /**
     * Обновить упражнение
     */
    public function update(Exercise $exercise, array $data): Exercise
    {
        $exercise->update($data);

        return $exercise->fresh();
    }

    /**
     * Удалить упражнение
     */
    public function delete(Exercise $exercise): bool
    {
        return $exercise->delete();
    }
}
