<?php

declare(strict_types=1);

namespace App\Services;

use App\Domains\Planning\Models\Exercise;
use App\Domains\Planning\Models\Session;
use App\Domains\Planning\Models\SessionBlock;
use App\Domains\User\Models\User;
use App\DTOs\Sessions\CreateSessionBlockDTO;
use App\DTOs\Sessions\CreateSessionDTO;
use App\DTOs\Sessions\UpdateSessionBlockDTO;
use App\Enums\ExerciseStatus;
use App\Enums\SessionBlockStatus;
use App\Enums\SessionStatus;
use App\Events\SessionBlockCompleted;
use App\Events\SessionCompleted;

class SessionService
{

    /**
     * Создать новую сессию со всеми связанными блоками и упражнениями
     */
    public function createSession(User $user, CreateSessionDTO $dto): Session
    {
        $session = Session::create([
            'user_id' => $user->id,
            'practice_template_id' => $dto->template_id,
            ...$dto->toArray(),
            'planned_duration' => array_sum(array_column($dto->getBlocksArray(), 'planned_duration')),
            'status' => SessionStatus::PLANNED,
        ]);

        $this->createSessionBlocks($session, $dto->blocks);
        $this->createExercisesFromBlocks($user, $dto->blocks);

        return $session->fresh(['blocks', 'template']);
    }

    /**
     * Создать блоки для сессии
     *
     * @param array<CreateSessionBlockDTO> $blocks
     */
    private function createSessionBlocks(Session $session, array $blocks): void
    {
        foreach ($blocks as $index => $blockDTO) {
            $blockData = $blockDTO->toArray();

            $session->blocks()->create([
                ...$blockData,
                'status' => SessionBlockStatus::PLANNED,
                'sort_order' => $index + 1,
            ]);
        }
    }

    /**
     * Создать упражнения из блоков сессии (если они еще не существуют)
     *
     * @param array<CreateSessionBlockDTO> $blocks
     */
    private function createExercisesFromBlocks(User $user, array $blocks): void
    {
        foreach ($blocks as $blockDTO) {
            $existingExercise = Exercise::where('user_id', $user->id)
                ->where('title', $blockDTO->title)
                ->where('type', $blockDTO->type->value)
                ->first();

            if (!$existingExercise) {
                Exercise::create([
                    'user_id' => $user->id,
                    'title' => $blockDTO->title,
                    'description' => $blockDTO->description,
                    'type' => $blockDTO->type,
                    'planned_duration' => $blockDTO->duration,
                    'status' => ExerciseStatus::PLANNED,
                ]);
            }
        }
    }

    /**
     * Начать сессию
     */
    public function startSession(Session $session): bool
    {
        if (!$session->canBeStarted()) {
            return false;
        }

        $session->update([
            'status' => SessionStatus::ACTIVE,
            'started_at' => now(),
        ]);

        return true;
    }

    /**
     * Приостановить сессию
     */
    public function pauseSession(Session $session): bool
    {
        if ($session->status !== SessionStatus::ACTIVE) {
            return false;
        }

        $session->update([
            'status' => SessionStatus::PAUSED,
        ]);

        return true;
    }

    /**
     * Завершить сессию и обновить прогресс целей
     */
    public function completeSession(Session $session): array
    {
        if (!$session->canBeCompleted()) {
            return [
                'success' => false,
                'message' => 'Сессия не может быть завершена в текущем статусе',
            ];
        }

        $actualDuration = $session->getTotalBlocksActualDuration();

        $session->update([
            'status' => SessionStatus::COMPLETED,
            'completed_at' => now(),
            'actual_duration' => $actualDuration,
        ]);

        // Отправляем событие для асинхронного обновления прогресса целей
        SessionCompleted::dispatch($session);

        return [
            'success' => true,
            'message' => 'Сессия завершена!',
        ];
    }

    /**
     * Обновить блок сессии и обновить прогресс целей если блок завершен
     */
    public function updateSessionBlock(Session $session, SessionBlock $block, UpdateSessionBlockDTO $dto): array
    {
        $updateData = $dto->toArray();

        // Если блок завершается, устанавливаем время завершения если не передано
        if ($dto->status === SessionBlockStatus::COMPLETED && $dto->completed_at === null) {
            $updateData['completed_at'] = now();
        }

        $block->update($updateData);

        // Отправляем событие для асинхронного обновления прогресса целей
        if ($dto->status === SessionBlockStatus::COMPLETED) {
            SessionBlockCompleted::dispatch($block);
        }

        return [
            'success' => true,
            'message' => 'Блок обновлен',
        ];
    }

    /**
     * Удалить сессию со всеми блоками
     */
    public function deleteSession(Session $session): void
    {
        // Удаляем все блоки сессии
        $session->blocks()->delete();

        // Удаляем саму сессию
        $session->delete();
    }
}
