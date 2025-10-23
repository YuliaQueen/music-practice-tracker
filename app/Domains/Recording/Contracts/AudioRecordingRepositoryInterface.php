<?php

declare(strict_types=1);

namespace App\Domains\Recording\Contracts;

use Carbon\Carbon;
use App\Domains\Recording\Models\AudioRecording;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Интерфейс репозитория аудио записей
 */
interface AudioRecordingRepositoryInterface
{
    /**
     * Найти запись по ID
     *
     * @param int $id
     * @return AudioRecording|null
     */
    public function findById(int $id): ?AudioRecording;

    /**
     * Получить все записи пользователя с пагинацией
     *
     * @param int   $userId
     * @param int   $perPage
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getForUser(int $userId, int $perPage = 20, array $filters = []): LengthAwarePaginator;

    /**
     * Получить записи для упражнения
     *
     * @param int $exerciseId
     * @return Collection
     */
    public function getForExercise(int $exerciseId): Collection;

    /**
     * Получить записи для блока сессии
     *
     * @param int $sessionBlockId
     * @return Collection
     */
    public function getForSessionBlock(int $sessionBlockId): Collection;

    /**
     * Получить записи за период
     *
     * @param int    $userId
     * @param Carbon $from
     * @param Carbon $to
     * @return Collection
     */
    public function getRecordedBetween(int $userId, Carbon $from, Carbon $to): Collection;

    /**
     * Создать новую запись
     *
     * @param array $data
     * @return AudioRecording
     */
    public function create(array $data): AudioRecording;

    /**
     * Обновить запись
     *
     * @param AudioRecording $recording
     * @param array          $data
     * @return AudioRecording
     */
    public function update(AudioRecording $recording, array $data): AudioRecording;

    /**
     * Удалить запись
     *
     * @param AudioRecording $recording
     * @return bool
     */
    public function delete(AudioRecording $recording): bool;

    /**
     * Получить статистику записей пользователя
     *
     * @param int $userId
     * @return array
     */
    public function getUserStatistics(int $userId): array;
}
