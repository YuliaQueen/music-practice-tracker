<?php

declare(strict_types=1);

namespace App\Services;

use App\Domains\User\Models\User;
use App\Traits\DatabaseTransactions;
use App\Domains\Planning\Models\Exercise;
use App\Domains\Planning\Models\SessionBlock;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Domains\Planning\Contracts\ExerciseRepositoryInterface;

/**
 * Сервис для работы с упражнениями
 */
class ExerciseService
{
    use DatabaseTransactions;

    public function __construct(
        private ExerciseRepositoryInterface $exerciseRepository
    ) {}

    /**
     * Архивировать упражнение
     *
     * @param Exercise $exercise
     * @return array{success: bool, exercise?: Exercise, message: string}
     */
    public function archiveExercise(Exercise $exercise): array
    {
        $result = $this->executeInTransaction(
            callback    : function () use ($exercise) {
                if ($exercise->is_archived) {
                    throw new \RuntimeException('Упражнение уже находится в архиве');
                }

                $firstSessionBlock = SessionBlock::where('title', $exercise->title)
                    ->whereHas('session', function ($query) use ($exercise) {
                        $query->where('user_id', $exercise->user_id)
                              ->where('status', 'completed');
                    })
                    ->orderBy('created_at', 'asc')
                    ->first();

                $exercise->update([
                    'is_archived'           => true,
                    'archived_at'           => now(),
                    'started_learning_at'   => $firstSessionBlock?->created_at ?? $exercise->created_at,
                    'completed_learning_at' => now(),
                ]);

                return $exercise->fresh();
            },
            errorContext: 'Ошибка при архивировании упражнения',
            logContext  : ['exercise_id' => $exercise->id]
        );

        if ($result['success']) {
            return [
                'success'  => true,
                'exercise' => $result['result'],
                'message'  => 'Упражнение успешно перемещено в архив',
            ];
        }

        return $result;
    }

    /**
     * Восстановить упражнение из архива
     *
     * @param Exercise $exercise
     * @return array{success: bool, exercise?: Exercise, message: string}
     */
    public function restoreExercise(Exercise $exercise): array
    {
        $result = $this->executeInTransaction(
            callback    : function () use ($exercise) {
                if (!$exercise->is_archived) {
                    throw new \RuntimeException('Упражнение не находится в архиве');
                }

                $exercise->update([
                    'is_archived'           => false,
                    'archived_at'           => null,
                    'completed_learning_at' => null,
                ]);

                return $exercise->fresh();
            },
            errorContext: 'Ошибка при восстановлении упражнения',
            logContext  : ['exercise_id' => $exercise->id]
        );

        if ($result['success']) {
            return [
                'success'  => true,
                'exercise' => $result['result'],
                'message'  => 'Упражнение успешно восстановлено из архива',
            ];
        }

        return $result;
    }

    /**
     * Получить статистику изучения упражнения
     *
     * @param Exercise $exercise
     * @return array{sessions_count: int, total_practice_time: int, average_practice_time: float, learning_days: int|null, started_at: string|null, completed_at: string|null}
     */
    public function getExerciseStatistics(Exercise $exercise): array
    {
        return $exercise->getLearningStatistics();
    }

    /**
     * Получить список архивных упражнений пользователя
     *
     * @param User $user
     * @param int  $perPage
     * @return LengthAwarePaginator
     */
    public function getArchivedExercises(User $user, int $perPage = 10): LengthAwarePaginator
    {
        return $this->exerciseRepository->getArchivedForUser($user->id, $perPage);
    }

    /**
     * Получить список активных упражнений пользователя
     *
     * @param User  $user
     * @param int   $perPage
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getActiveExercises(User $user, int $perPage = 10, array $filters = []): LengthAwarePaginator
    {
        return $this->exerciseRepository->getActiveForUser($user->id, $perPage, $filters);
    }
}
