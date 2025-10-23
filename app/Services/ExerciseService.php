<?php

declare(strict_types=1);

namespace App\Services;

use Throwable;
use App\Domains\User\Models\User;
use App\Domains\Planning\Models\Exercise;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\DTOs\Exercises\CreateExerciseDTO;
use App\DTOs\Exercises\UpdateExerciseDTO;
use App\Domains\Planning\Contracts\ExerciseRepositoryInterface;

/**
 * Сервис для работы с упражнениями
 */
class ExerciseService
{
    public function __construct(
        private readonly ExerciseRepositoryInterface $exerciseRepository
    ) {
    }

    /**
     * Создать новое упражнение
     *
     * @param User               $user
     * @param CreateExerciseDTO $dto
     * @return array{success: bool, exercise?: Exercise, message: string}
     */
    public function createExercise(User $user, CreateExerciseDTO $dto): array
    {
        try {
            DB::beginTransaction();

            $exercise = $this->exerciseRepository->create([
                'user_id' => $user->id,
                ...$dto->toArray(),
                'status' => Exercise::STATUS_PLANNED,
            ]);

            DB::commit();

            return [
                'success'  => true,
                'exercise' => $exercise,
                'message'  => 'Упражнение успешно создано',
            ];
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Ошибка при создании упражнения', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Не удалось создать упражнение',
            ];
        }
    }

    /**
     * Обновить упражнение
     *
     * @param Exercise          $exercise
     * @param UpdateExerciseDTO $dto
     * @return array{success: bool, exercise?: Exercise, message: string}
     */
    public function updateExercise(Exercise $exercise, UpdateExerciseDTO $dto): array
    {
        try {
            DB::beginTransaction();

            $updatedExercise = $this->exerciseRepository->update($exercise, $dto->toArray());

            DB::commit();

            return [
                'success'  => true,
                'exercise' => $updatedExercise,
                'message'  => 'Упражнение успешно обновлено',
            ];
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Ошибка при обновлении упражнения', [
                'exercise_id' => $exercise->id,
                'error'       => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Не удалось обновить упражнение',
            ];
        }
    }

    /**
     * Удалить упражнение
     *
     * @param Exercise $exercise
     * @return array{success: bool, message: string}
     */
    public function deleteExercise(Exercise $exercise): array
    {
        try {
            DB::beginTransaction();

            $this->exerciseRepository->delete($exercise);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Упражнение успешно удалено',
            ];
        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Ошибка при удалении упражнения', [
                'exercise_id' => $exercise->id,
                'error'       => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Не удалось удалить упражнение',
            ];
        }
    }
}
