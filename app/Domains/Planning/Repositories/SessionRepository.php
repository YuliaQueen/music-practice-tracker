<?php

declare(strict_types=1);

namespace App\Domains\Planning\Repositories;

use Carbon\Carbon;
use App\Domains\Planning\Models\Session;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Domains\Planning\Contracts\SessionRepositoryInterface;

/**
 * Репозиторий для работы с сессиями
 */
class SessionRepository implements SessionRepositoryInterface
{
    /**
     * Найти сессию по ID
     */
    public function findById(int $id): ?Session
    {
        return Session::with(['template', 'blocks'])->find($id);
    }

    /**
     * Получить сессии пользователя с пагинацией
     */
    public function getForUser(int $userId, int $perPage = 10): LengthAwarePaginator
    {
        return Session::forUser($userId)
            ->with(['template', 'blocks'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Получить активные сессии пользователя
     */
    public function getActiveForUser(int $userId): Collection
    {
        return Session::forUser($userId)
            ->active()
            ->with(['blocks'])
            ->orderBy('started_at', 'desc')
            ->get();
    }

    /**
     * Получить завершенные сессии за период
     */
    public function getCompletedInPeriod(int $userId, Carbon $from, Carbon $to): Collection
    {
        return Session::forUser($userId)
            ->completed()
            ->inPeriod($from, $to)
            ->with(['blocks'])
            ->orderBy('completed_at', 'desc')
            ->get();
    }

    /**
     * Создать новую сессию
     */
    public function create(array $data): Session
    {
        return Session::create($data);
    }

    /**
     * Обновить сессию
     */
    public function update(Session $session, array $data): Session
    {
        $session->update($data);

        return $session->fresh(['template', 'blocks']);
    }

    /**
     * Удалить сессию
     */
    public function delete(Session $session): bool
    {
        return $session->delete();
    }

    /**
     * Получить статистику сессий пользователя
     */
    public function getUserStatistics(int $userId): array
    {
        $sessions = Session::forUser($userId);

        return [
            'total_sessions'       => $sessions->count(),
            'completed_sessions'   => $sessions->clone()->completed()->count(),
            'active_sessions'      => $sessions->clone()->active()->count(),
            'total_practice_time'  => $sessions->clone()->completed()->sum('actual_duration'),
            'average_session_time' => $sessions->clone()->completed()->avg('actual_duration'),
        ];
    }
}
