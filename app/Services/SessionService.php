<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\SessionStatus;
use App\Enums\ExerciseStatus;
use App\Events\SessionCompleted;
use App\Domains\User\Models\User;
use App\Enums\SessionBlockStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use App\Traits\DatabaseTransactions;
use App\Events\SessionBlockCompleted;
use App\DTOs\Sessions\CreateSessionDTO;
use App\Domains\Planning\Models\Session;
use App\Domains\Planning\Models\Exercise;
use App\Domains\Planning\Models\Template;
use App\DTOs\Sessions\CreateSessionBlockDTO;
use App\DTOs\Sessions\UpdateSessionBlockDTO;
use App\Domains\Planning\Models\SessionBlock;
use App\Domains\Planning\Contracts\SessionRepositoryInterface;
use App\Domains\Planning\Contracts\SessionBlockRepositoryInterface;

/**
 * Сервис для работы с сессиями практики
 */
class SessionService
{
    use DatabaseTransactions;

    public function __construct(
        private SessionRepositoryInterface      $sessionRepository,
        private SessionBlockRepositoryInterface $blockRepository,
        private PomodoroService                 $pomodoroService
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
        $result = $this->executeInTransaction(
            callback    : function () use ($user, $dto) {
                $session = $this->sessionRepository->create([
                    'user_id'              => $user->id,
                    'practice_template_id' => $dto->template_id,
                    ...$dto->toArray(),
                    'planned_duration'     => array_sum(array_column($dto->getBlocksArray(), 'planned_duration')),
                    'status'               => SessionStatus::PLANNED,
                ]);

                $this->createSessionBlocks($session, $dto->blocks);
                $this->createExercisesFromBlocks($user, $dto->blocks);

                return $session->fresh(['blocks', 'template']);
            },
            errorContext: 'Ошибка при создании сессии',
            logContext  : ['user_id' => $user->id]
        );

        if ($result['success']) {
            return [
                'success' => true,
                'session' => $result['result'],
                'message' => 'Сессия создана успешно',
            ];
        }

        return $result;
    }

    /**
     * Создать Pomodoro-сессию с автоматическим расчетом слотов
     *
     * @param User        $user
     * @param string      $title
     * @param string|null $description
     * @param int         $totalMinutes
     * @param int         $workDuration
     * @param int         $shortBreak
     * @param int         $longBreak
     * @param int         $cyclesBeforeLongBreak
     * @return array{success: bool, session?: Session, message: string}
     */
    public function createPomodoroSession(
        User    $user,
        string  $title,
        ?string $description,
        int     $totalMinutes,
        int     $workDuration = 25,
        int     $shortBreak = 5,
        int     $longBreak = 15,
        int     $cyclesBeforeLongBreak = 4
    ): array {
        // Рассчитываем слоты Pomodoro
        $pomodoroResult = $this->pomodoroService->calculatePomodoroSlots(
            $totalMinutes,
            $workDuration,
            $shortBreak,
            $longBreak,
            $cyclesBeforeLongBreak
        );

        if (!$pomodoroResult['success']) {
            return [
                'success' => false,
                'message' => $pomodoroResult['message'],
            ];
        }

        $result = $this->executeInTransaction(
            callback    : function () use (
                $user,
                $title,
                $description,
                $totalMinutes,
                $workDuration,
                $shortBreak,
                $longBreak,
                $cyclesBeforeLongBreak,
                $pomodoroResult
            ) {
                // Создаем сессию с Pomodoro настройками
                $session = $this->sessionRepository->create([
                    'user_id'                           => $user->id,
                    'title'                             => $title,
                    'description'                       => $description,
                    'session_mode'                      => 'pomodoro',
                    'pomodoro_enabled'                  => true,
                    'pomodoro_work_duration'            => $workDuration,
                    'pomodoro_short_break'              => $shortBreak,
                    'pomodoro_long_break'               => $longBreak,
                    'pomodoro_cycles_before_long_break' => $cyclesBeforeLongBreak,
                    'pomodoro_total_cycles'             => $pomodoroResult['totalCycles'],
                    'planned_duration'                  => $totalMinutes,
                    'status'                            => SessionStatus::PLANNED,
                    'auto_advance'                      => true, // Pomodoro всегда с автопереходом
                ]);

                // Создаем блоки из рассчитанных слотов
                foreach ($pomodoroResult['slots'] as $index => $slot) {
                    $this->blockRepository->create([
                        'practice_session_id' => $session->id,
                        'title'               => $slot['title'],
                        'description'         => $slot['description'],
                        'type'                => $slot['type'],
                        'planned_duration'    => $slot['duration'],
                        'status'              => SessionBlockStatus::PLANNED->value,
                        'sort_order'          => $index + 1,
                    ]);
                }

                return $session->fresh(['blocks']);
            },
            errorContext: 'Ошибка при создании Pomodoro-сессии',
            logContext  : ['user_id' => $user->id]
        );

        if ($result['success']) {
            return [
                'success' => true,
                'session' => $result['result'],
                'message' => 'Pomodoro-сессия создана успешно',
            ];
        }

        return $result;
    }

    /**
     * Получить упражнения пользователя для автозаполнения
     *
     * @param int $userId
     * @return Collection
     */
    public function getPreviousExercises(int $userId): Collection
    {
        // Получаем все упражнения пользователя
        $exercises = Exercise::where('user_id', $userId)
            ->select('title', 'description', 'type', 'planned_duration')
            ->orderBy('title')
            ->get();

        // Подсчитываем количество использований каждого упражнения в сессиях
        $usageCounts = SessionBlock::whereHas('session', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->select('title', DB::raw('count(*) as usage_count'))
            ->groupBy('title')
            ->pluck('usage_count', 'title');

        return $exercises->map(function ($exercise) use ($usageCounts) {
            return [
                'title'       => $exercise->title,
                'description' => $exercise->description,
                'type'        => $exercise->type,
                'duration'    => $exercise->planned_duration,
                'usage_count' => $usageCounts->get($exercise->title, 0),
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
     * Получить список уникальных упражнений пользователя
     *
     * @param int $userId
     * @return array
     */
    public function getUserExercises(int $userId): array
    {
        return SessionBlock::whereHas('session', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->distinct()
            ->pluck('title')
            ->filter()
            ->sort()
            ->values()
            ->toArray();
    }

    /**
     * Начать сессию
     * Автоматически запускает первое упражнение
     *
     * @param Session $session
     * @return bool
     */
    public function startSession(Session $session): bool
    {
        if (!$session->canBeStarted()) {
            return false;
        }

        $result = $this->executeInTransaction(
            callback    : function () use ($session) {
                // Обновляем статус сессии
                $updatedSession = $this->sessionRepository->update($session, [
                    'status'     => SessionStatus::ACTIVE,
                    'started_at' => now(),
                ]);

                // Автоматически стартуем первое упражнение
                // Используем прямой запрос к БД вместо отношений модели
                $firstBlock = SessionBlock::where('practice_session_id', $session->id)
                    ->where('status', SessionBlockStatus::PLANNED->value)
                    ->orderBy('sort_order', 'asc')
                    ->first();

                if ($firstBlock) {
                    $this->blockRepository->update($firstBlock, [
                        'status'     => SessionBlockStatus::ACTIVE->value,
                        'started_at' => now(),
                    ]);

                    Log::info('Автоматически запущено первое упражнение', [
                        'session_id'  => $session->id,
                        'block_id'    => $firstBlock->id,
                        'block_title' => $firstBlock->title,
                    ]);
                }

                return true;
            },
            errorContext: 'Ошибка при запуске сессии',
            logContext  : ['session_id' => $session->id]
        );

        return $result['success'];
    }

    /**
     * Приостановить сессию
     *
     * @param Session $session
     * @return bool
     */
    public function pauseSession(Session $session): bool
    {
        if ($session->status != SessionStatus::ACTIVE) {
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

        $result = $this->executeInTransaction(
            callback    : function () use ($session) {
                $actualDuration = $session->getTotalBlocksActualDuration();

                $this->sessionRepository->update($session, [
                    'status'          => SessionStatus::COMPLETED,
                    'completed_at'    => now(),
                    'actual_duration' => $actualDuration,
                ]);

                // Отправляем событие для асинхронного обновления прогресса целей
                SessionCompleted::dispatch($session);

                return true;
            },
            errorContext: 'Ошибка при завершении сессии',
            logContext  : ['session_id' => $session->id]
        );

        if ($result['success']) {
            return [
                'success' => true,
                'message' => 'Сессия завершена!',
            ];
        }

        return $result;
    }

    /**
     * Изменить порядок блоков в сессии
     *
     * @param Session $session
     * @param array   $blockIds Массив ID блоков в новом порядке
     * @return array{success: bool, message: string}
     */
    public function reorderSessionBlocks(Session $session, array $blockIds): array
    {
        $result = $this->executeInTransaction(
            callback    : function () use ($session, $blockIds) {
                // Проверяем, что все блоки принадлежат этой сессии
                $sessionBlocks   = $this->blockRepository->getForSession($session->id);
                $sessionBlockIds = $sessionBlocks->pluck('id')->toArray();

                foreach ($blockIds as $blockId) {
                    if (!in_array($blockId, $sessionBlockIds)) {
                        throw new \InvalidArgumentException('Некоторые блоки не принадлежат этой сессии');
                    }
                }

                // Обновляем sort_order для каждого блока
                foreach ($blockIds as $index => $blockId) {
                    $block = $sessionBlocks->firstWhere('id', $blockId);
                    if ($block) {
                        $this->blockRepository->update($block, [
                            'sort_order' => $index + 1,
                        ]);
                    }
                }

                return true;
            },
            errorContext: 'Ошибка при изменении порядка блоков',
            logContext  : ['session_id' => $session->id]
        );

        if ($result['success']) {
            return [
                'success' => true,
                'message' => 'Порядок упражнений обновлен',
            ];
        }

        return $result;
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
        // Проверяем, что блок принадлежит этой сессии
        if ($block->practice_session_id !== $session->id) {
            return [
                'success' => false,
                'message' => 'Блок не принадлежит этой сессии',
            ];
        }

        // Проверяем, что блок можно начать
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
                    'status' => SessionBlockStatus::PAUSED->value,
                ]);
            }

            // Запускаем выбранный блок
            $this->blockRepository->update($block, [
                'status'     => SessionBlockStatus::ACTIVE->value,
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
        $result = $this->executeInTransaction(
            callback    : function () use ($session, $block, $dto) {
                $updateData = $dto->toArray();

                // Если блок завершается, устанавливаем время завершения если не передано
                if ($dto->status === SessionBlockStatus::COMPLETED->value && $dto->completed_at === null) {
                    $updateData['completed_at'] = now();
                }

                $updatedBlock = $this->blockRepository->update($block, $updateData);

                Log::info('Блок обновлен', [
                    'block_id' => $updatedBlock->id,
                    'block_title' => $updatedBlock->title,
                    'old_status' => $block->status,
                    'new_status' => $updatedBlock->status,
                ]);

                // Отправляем событие для асинхронного обновления прогресса целей
                if ($dto->status === SessionBlockStatus::COMPLETED->value) {
                    SessionBlockCompleted::dispatch($updatedBlock);

                    // Автопереход к следующему упражнению, если включена опция
                    Log::info('Проверка auto_advance для сессии', [
                        'session_id'   => $session->id,
                        'auto_advance' => $session->auto_advance,
                        'block_id'     => $block->id,
                        'block_title'  => $block->title,
                    ]);

                    if ($session->auto_advance) {
                        Log::info('Вызов autoAdvanceToNextBlock', [
                            'session_id' => $session->id,
                            'block_id'   => $updatedBlock->id,
                        ]);
                        $this->autoAdvanceToNextBlock($session, $updatedBlock);
                    } else {
                        Log::info('auto_advance отключен для сессии', [
                            'session_id' => $session->id,
                        ]);
                    }
                }

                return true;
            },
            errorContext: 'Ошибка при обновлении блока сессии',
            logContext  : [
                'session_id' => $session->id,
                'block_id'   => $block->id,
            ]
        );

        if ($result['success']) {
            return [
                'success' => true,
                'message' => 'Блок обновлен',
            ];
        }

        return $result;
    }

    /**
     * Удалить сессию со всеми блоками
     *
     * @param Session $session
     * @return array{success: bool, message: string}
     */
    public function deleteSession(Session $session): array
    {
        $result = $this->executeInTransaction(
            callback    : function () use ($session) {
                // Удаляем все блоки сессии
                $this->blockRepository->deleteForSession($session->id);

                // Удаляем саму сессию
                $this->sessionRepository->delete($session);

                return true;
            },
            errorContext: 'Ошибка при удалении сессии',
            logContext  : ['session_id' => $session->id]
        );

        if ($result['success']) {
            return [
                'success' => true,
                'message' => 'Сессия успешно удалена',
            ];
        }

        return $result;
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
                'status'              => SessionBlockStatus::PLANNED->value,
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

    /**
     * Автоматически перейти к следующему упражнению
     *
     * @param Session      $session
     * @param SessionBlock $completedBlock
     * @return void
     */
    protected function autoAdvanceToNextBlock(Session $session, SessionBlock $completedBlock): void
    {
        try {
            // Находим следующее незавершенное упражнение
            // Используем прямой запрос к БД для получения актуальных данных
            $nextBlock = SessionBlock::where('practice_session_id', $session->id)
                ->where('status', SessionBlockStatus::PLANNED->value)
                ->where('sort_order', '>', $completedBlock->sort_order)
                ->orderBy('sort_order', 'asc')
                ->first();

            if ($nextBlock) {
                $this->blockRepository->update($nextBlock, [
                    'status' => SessionBlockStatus::ACTIVE->value,
                    'started_at' => now(),
                ]);

                Log::info('Автопереход к следующему упражнению', [
                    'session_id'       => $session->id,
                    'completed_block'  => $completedBlock->title,
                    'next_block'       => $nextBlock->title,
                    'next_block_id'    => $nextBlock->id,
                ]);
            } else {
                Log::info('Автопереход: нет следующих упражнений', [
                    'session_id'      => $session->id,
                    'completed_block' => $completedBlock->title,
                ]);
            }
        } catch (\Throwable $e) {
            // Ошибка автоперехода не должна прерывать основной процесс
            Log::error('Ошибка при автопереходе к следующему упражнению', [
                'session_id'      => $session->id,
                'completed_block' => $completedBlock->title,
                'error'           => $e->getMessage(),
                'trace'           => $e->getTraceAsString(),
            ]);
        }
    }
}
