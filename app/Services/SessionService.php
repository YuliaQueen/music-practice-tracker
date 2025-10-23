<?php

declare(strict_types=1);

namespace App\Services;

use Throwable;
use App\Domains\Planning\Models\Exercise;
use App\Domains\Planning\Models\Session;
use App\Domains\Planning\Models\SessionBlock;
use App\Domains\Planning\Models\Template;
use App\Domains\User\Models\User;
use App\DTOs\Sessions\CreateSessionBlockDTO;
use App\DTOs\Sessions\CreateSessionDTO;
use App\DTOs\Sessions\UpdateSessionBlockDTO;
use App\Enums\ExerciseStatus;
use App\Enums\SessionBlockStatus;
use App\Enums\SessionStatus;
use App\Events\SessionBlockCompleted;
use App\Events\SessionCompleted;
use App\Domains\Planning\Contracts\SessionRepositoryInterface;
use App\Domains\Planning\Contracts\SessionBlockRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

/**
 * Сервис для работы с сессиями практики
 */
class SessionService
{
    public function __construct(
        private SessionRepositoryInterface $sessionRepository,
        private SessionBlockRepositoryInterface $blockRepository
    ) {
    }

    /**
     * Создать новую сессию со всеми связанными блоками и упражнениями
     *
     * @param User             $user
     * @param CreateSessionDTO $dto
     * @return array{success: bool, session?: Session, message: string}
     */
    public function createSession(User $user, CreateSessionDTO $dto): array
    {
        try {
            DB::beginTransaction();

            $session = $this->sessionRepository->create([
                'user_id'              => $user->id,
                'practice_template_id' => $dto->template_id,
                ...$dto->toArray(),
                'planned_duration'     => array_sum(array_column($dto->getBlocksArray(), 'planned_duration')),
                'status'               => SessionStatus::PLANNED,
            ]);

            $this->createSessionBlocks($session, $dto->blocks);
            $this->createExercisesFromBlocks($user, $dto->blocks);

            DB::commit();

            return [
                'success' => true,
                'session' => $session->fresh(['blocks', 'template']),
                'message' => 'Сессия создана успешно',
            ];
        } catch (\Throwable $e) {
            if (DB::transactionLevel() > 0) {
                try {
                    DB::rollBack();
                } catch (\Throwable $rollbackException) {
                    Log::error('Ошибка при откате транзакции', [
                        'user_id' => $user->id,
                        'error'   => $rollbackException->getMessage(),
                    ]);
                }
            }

            Log::error('Ошибка при создании сессии', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Ошибка при создании сессии',
            ];
        }
    }

    /**
     * Получить упражнения пользователя для автозаполнения
     *
     * @param int $userId
     * @return Collection
     */
    public function getPreviousExercises(int $userId): Collection
    {
        return Exercise::where('user_id', $userId)
            ->select('title', 'description', 'type', 'planned_duration')
            ->orderBy('title')
            ->get()
            ->map(function ($exercise) {
                return [
                    'title'       => $exercise->title,
                    'description' => $exercise->description,
                    'type'        => $exercise->type,
                    'duration'    => $exercise->planned_duration,
                ];
            });
    }

    /**
     * Получить доступные шаблоны для пользователя
     *
     * @param int $userId
     * @return Collection
     */
    public function getAvailableTemplates(int $userId): Collection
    {
        return Template::availableFor($userId)
            ->with('blocks')
            ->orderBy('name')
            ->get();
    }

    /**
     * Начать сессию
     *
     * @param Session $session
     * @return bool
     */
    public function startSession(Session $session): bool
    {
        if (!$session->canBeStarted()) {
            return false;
        }

        try {
            $this->sessionRepository->update($session, [
                'status'     => SessionStatus::ACTIVE,
                'started_at' => now(),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Ошибка при запуске сессии', [
                'session_id' => $session->id,
                'error'      => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Приостановить сессию
     *
     * @param Session $session
     * @return bool
     */
    public function pauseSession(Session $session): bool
    {
        if ($session->status !== SessionStatus::ACTIVE) {
            return false;
        }

        try {
            $this->sessionRepository->update($session, [
                'status' => SessionStatus::PAUSED,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Ошибка при приостановке сессии', [
                'session_id' => $session->id,
                'error'      => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Завершить сессию и обновить прогресс целей
     *
     * @param Session $session
     * @return array
     */
    public function completeSession(Session $session): array
    {
        if (!$session->canBeCompleted()) {
            return [
                'success' => false,
                'message' => 'Сессия не может быть завершена в текущем статусе',
            ];
        }

        try {
            DB::beginTransaction();

            $actualDuration = $session->getTotalBlocksActualDuration();

            $this->sessionRepository->update($session, [
                'status'          => SessionStatus::COMPLETED,
                'completed_at'    => now(),
                'actual_duration' => $actualDuration,
            ]);

            // Отправляем событие для асинхронного обновления прогресса целей
            SessionCompleted::dispatch($session);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Сессия завершена!',
            ];
        } catch (\Throwable $e) {
            if (DB::transactionLevel() > 0) {
                try {
                    DB::rollBack();
                } catch (\Throwable $rollbackException) {
                    Log::error('Ошибка при откате транзакции', [
                        'session_id' => $session->id,
                        'error'      => $rollbackException->getMessage(),
                    ]);
                }
            }

            Log::error('Ошибка при завершении сессии', [
                'session_id' => $session->id,
                'error'      => $e->getMessage(),
                'trace'      => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Ошибка при завершении сессии',
            ];
        }
    }

    /**
     * Начать выполнение конкретного блока сессии
     *
     * @param Session      $session
     * @param SessionBlock $block
     * @return array{success: bool, message: string}
     */
    public function startSessionBlock(Session $session, SessionBlock $block): array
    {
        // Проверяем что блок принадлежит этой сессии
        if ($block->practice_session_id !== $session->id) {
            return [
                'success' => false,
                'message' => 'Блок не принадлежит этой сессии',
            ];
        }

        // Проверяем что блок можно начать
        if (!$block->canBeStarted()) {
            return [
                'success' => false,
                'message' => 'Блок не может быть запущен в текущем статусе',
            ];
        }

        try {
            // Приостанавливаем текущий активный блок, если есть
            $currentBlock = $session->getCurrentBlock();
            if ($currentBlock && $currentBlock->id !== $block->id) {
                $this->blockRepository->update($currentBlock, [
                    'status' => SessionBlockStatus::PAUSED,
                ]);
            }

            // Запускаем выбранный блок
            $this->blockRepository->update($block, [
                'status'     => SessionBlockStatus::ACTIVE,
                'started_at' => now(),
            ]);

            return [
                'success' => true,
                'message' => 'Упражнение начато',
            ];
        } catch (\Throwable $e) {
            Log::error('Ошибка при запуске блока сессии', [
                'session_id' => $session->id,
                'block_id'   => $block->id,
                'error'      => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Ошибка при запуске упражнения',
            ];
        }
    }

    /**
     * Обновить блок сессии и обновить прогресс целей если блок завершен
     *
     * @param Session                $session
     * @param SessionBlock           $block
     * @param UpdateSessionBlockDTO  $dto
     * @return array
     */
    public function updateSessionBlock(Session $session, SessionBlock $block, UpdateSessionBlockDTO $dto): array
    {
        try {
            DB::beginTransaction();

            $updateData = $dto->toArray();

            // Если блок завершается, устанавливаем время завершения если не передано
            if ($dto->status === SessionBlockStatus::COMPLETED && $dto->completed_at === null) {
                $updateData['completed_at'] = now();
            }

            $this->blockRepository->update($block, $updateData);

            // Отправляем событие для асинхронного обновления прогресса целей
            if ($dto->status === SessionBlockStatus::COMPLETED) {
                SessionBlockCompleted::dispatch($block);
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Блок обновлен',
            ];
        } catch (\Throwable $e) {
            if (DB::transactionLevel() > 0) {
                try {
                    DB::rollBack();
                } catch (\Throwable $rollbackException) {
                    Log::error('Ошибка при откате транзакции', [
                        'session_id' => $session->id,
                        'block_id'   => $block->id,
                        'error'      => $rollbackException->getMessage(),
                    ]);
                }
            }

            Log::error('Ошибка при обновлении блока сессии', [
                'session_id' => $session->id,
                'block_id'   => $block->id,
                'error'      => $e->getMessage(),
                'trace'      => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Ошибка при обновлении блока',
            ];
        }
    }

    /**
     * Удалить сессию со всеми блоками
     *
     * @param Session $session
     * @return array{success: bool, message: string}
     */
    public function deleteSession(Session $session): array
    {
        try {
            DB::beginTransaction();

            // Удаляем все блоки сессии
            $this->blockRepository->deleteForSession($session->id);

            // Удаляем саму сессию
            $this->sessionRepository->delete($session);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Сессия успешно удалена',
            ];
        } catch (\Throwable $e) {
            if (DB::transactionLevel() > 0) {
                try {
                    DB::rollBack();
                } catch (\Throwable $rollbackException) {
                    Log::error('Ошибка при откате транзакции', [
                        'session_id' => $session->id,
                        'error'      => $rollbackException->getMessage(),
                    ]);
                }
            }

            Log::error('Ошибка при удалении сессии', [
                'session_id' => $session->id,
                'error'      => $e->getMessage(),
                'trace'      => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Ошибка при удалении сессии',
            ];
        }
    }

    /**
     * Создать блоки для сессии
     *
     * @param Session                        $session
     * @param array<CreateSessionBlockDTO>   $blocks
     * @return void
     */
    private function createSessionBlocks(Session $session, array $blocks): void
    {
        foreach ($blocks as $index => $blockDTO) {
            $blockData = $blockDTO->toArray();

            $this->blockRepository->create([
                'practice_session_id' => $session->id,
                ...$blockData,
                'status'              => SessionBlockStatus::PLANNED,
                'sort_order'          => $index + 1,
            ]);
        }
    }

    /**
     * Создать упражнения из блоков сессии (если они еще не существуют)
     *
     * @param User                           $user
     * @param array<CreateSessionBlockDTO>   $blocks
     * @return void
     */
    private function createExercisesFromBlocks(User $user, array $blocks): void
    {
        foreach ($blocks as $blockDTO) {
            $existingExercise = Exercise::where('user_id', $user->id)
                ->where('title', $blockDTO->title)
                ->where('type', $blockDTO->type)
                ->first();

            if (!$existingExercise) {
                Exercise::create([
                    'user_id'          => $user->id,
                    'title'            => $blockDTO->title,
                    'description'      => $blockDTO->description,
                    'type'             => $blockDTO->type,
                    'planned_duration' => $blockDTO->duration,
                    'status'           => ExerciseStatus::PLANNED,
                ]);
            }
        }
    }
}
