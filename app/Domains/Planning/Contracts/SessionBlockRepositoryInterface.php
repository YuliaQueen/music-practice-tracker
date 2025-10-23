<?php

declare(strict_types=1);

namespace App\Domains\Planning\Contracts;

use App\Domains\Planning\Models\SessionBlock;
use Illuminate\Database\Eloquent\Collection;

/**
 * Интерфейс репозитория блоков сессий
 */
interface SessionBlockRepositoryInterface
{
    /**
     * Найти блок по ID
     *
     * @param int $id
     * @return SessionBlock|null
     */
    public function findById(int $id): ?SessionBlock;

    /**
     * Получить блоки для сессии
     *
     * @param int $sessionId
     * @return Collection
     */
    public function getForSession(int $sessionId): Collection;

    /**
     * Получить активный блок сессии
     *
     * @param int $sessionId
     * @return SessionBlock|null
     */
    public function getActiveForSession(int $sessionId): ?SessionBlock;

    /**
     * Получить следующий запланированный блок
     *
     * @param int $sessionId
     * @return SessionBlock|null
     */
    public function getNextPlannedForSession(int $sessionId): ?SessionBlock;

    /**
     * Создать блок
     *
     * @param array $data
     * @return SessionBlock
     */
    public function create(array $data): SessionBlock;

    /**
     * Обновить блок
     *
     * @param SessionBlock $block
     * @param array        $data
     * @return SessionBlock
     */
    public function update(SessionBlock $block, array $data): SessionBlock;

    /**
     * Удалить блок
     *
     * @param SessionBlock $block
     * @return bool
     */
    public function delete(SessionBlock $block): bool;

    /**
     * Удалить все блоки сессии
     *
     * @param int $sessionId
     * @return int Количество удаленных блоков
     */
    public function deleteForSession(int $sessionId): int;
}
