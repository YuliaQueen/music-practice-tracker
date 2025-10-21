<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\SessionCompleted;
use App\Services\GoalProgressService;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateGoalProgressAfterSession implements ShouldQueue
{
    public function __construct(
        private GoalProgressService $goalProgressService
    ) {
    }

    /**
     * Handle the event.
     */
    public function handle(SessionCompleted $event): void
    {
        $session = $event->session;

        // Обновляем прогресс целей после завершения сессии
        $this->goalProgressService->updateProgressAfterSession($session);

        // Проверяем и завершаем достигнутые цели
        $this->goalProgressService->checkAndCompleteGoals($session->user);
    }
}
