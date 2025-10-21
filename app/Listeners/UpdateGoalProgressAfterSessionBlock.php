<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\SessionBlockCompleted;
use App\Services\GoalProgressService;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateGoalProgressAfterSessionBlock implements ShouldQueue
{
    public function __construct(
        private GoalProgressService $goalProgressService
    ) {
    }

    /**
     * Handle the event.
     */
    public function handle(SessionBlockCompleted $event): void
    {
        $sessionBlock = $event->sessionBlock;
        $session = $sessionBlock->session;

        // Обновляем прогресс целей после завершения блока
        $this->goalProgressService->updateProgressAfterSessionBlock($sessionBlock);

        // Проверяем и завершаем достигнутые цели
        $this->goalProgressService->checkAndCompleteGoals($session->user);
    }
}
