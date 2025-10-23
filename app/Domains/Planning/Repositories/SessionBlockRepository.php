<?php

declare(strict_types=1);

namespace App\Domains\Planning\Repositories;

use App\Domains\Planning\Models\SessionBlock;
use Illuminate\Database\Eloquent\Collection;
use App\Domains\Planning\Contracts\SessionBlockRepositoryInterface;

/**
 * Репозиторий для работы с блоками сессий
 */
class SessionBlockRepository implements SessionBlockRepositoryInterface
{
    /**
     * Найти блок по ID
     */
    public function findById(int $id): ?SessionBlock
    {
        return SessionBlock::with(['session', 'templateBlock'])->find($id);
    }

    /**
     * Получить блоки для сессии
     */
    public function getForSession(int $sessionId): Collection
    {
        return SessionBlock::forSession($sessionId)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Получить активный блок сессии
     */
    public function getActiveForSession(int $sessionId): ?SessionBlock
    {
        return SessionBlock::forSession($sessionId)
            ->active()
            ->first();
    }

    /**
     * Получить следующий запланированный блок
     */
    public function getNextPlannedForSession(int $sessionId): ?SessionBlock
    {
        return SessionBlock::forSession($sessionId)
            ->where('status', SessionBlock::STATUS_PLANNED)
            ->orderBy('sort_order')
            ->first();
    }

    /**
     * Получить уникальные упражнения пользователя из всех сессий
     */
    public function getPreviousExercisesForUser(int $userId): Collection
    {
        return SessionBlock::whereHas('session', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
            ->select('title', 'description', 'type', 'planned_duration')
            ->distinct()
            ->orderBy('title')
            ->get()
            ->groupBy('title')
            ->map(function ($group) {
                $first = $group->first();
                return [
                    'title'       => $first->title,
                    'description' => $first->description,
                    'type'        => $first->type,
                    'duration'    => $first->planned_duration,
                    'usage_count' => $group->count(),
                ];
            })
            ->values();
    }

    /**
     * Создать блок
     */
    public function create(array $data): SessionBlock
    {
        return SessionBlock::create($data);
    }

    /**
     * Обновить блок
     */
    public function update(SessionBlock $block, array $data): SessionBlock
    {
        $block->update($data);

        return $block->fresh(['session', 'templateBlock']);
    }

    /**
     * Удалить блок
     */
    public function delete(SessionBlock $block): bool
    {
        return $block->delete();
    }

    /**
     * Удалить все блоки сессии
     */
    public function deleteForSession(int $sessionId): int
    {
        return SessionBlock::forSession($sessionId)->delete();
    }
}
