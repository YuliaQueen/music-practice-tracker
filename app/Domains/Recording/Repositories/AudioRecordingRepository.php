<?php

declare(strict_types=1);

namespace App\Domains\Recording\Repositories;

use Carbon\Carbon;
use App\Domains\Recording\Models\AudioRecording;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Domains\Recording\Contracts\AudioRecordingRepositoryInterface;

/**
 * Репозиторий для работы с аудио записями
 */
class AudioRecordingRepository implements AudioRecordingRepositoryInterface
{
    /**
     * Найти запись по ID
     */
    public function findById(int $id): ?AudioRecording
    {
        return AudioRecording::find($id);
    }

    /**
     * Получить все записи пользователя с пагинацией и фильтрами
     */
    public function getForUser(int $userId, int $perPage = 20, array $filters = []): LengthAwarePaginator
    {
        $query = AudioRecording::forUser($userId)
            ->with(['exercise', 'sessionBlock'])
            ->orderBy('recorded_at', 'desc');

        // Применяем фильтры
        if (isset($filters['exercise_id'])) {
            $query->where('exercise_id', $filters['exercise_id']);
        }

        if (isset($filters['session_block_id'])) {
            $query->where('practice_session_block_id', $filters['session_block_id']);
        }

        if (isset($filters['quality_rating'])) {
            $query->where('quality_rating', $filters['quality_rating']);
        }

        if (isset($filters['from_date']) && isset($filters['to_date'])) {
            $query->recordedBetween(
                Carbon::parse($filters['from_date']),
                Carbon::parse($filters['to_date'])
            );
        }

        return $query->paginate($perPage);
    }

    /**
     * Получить записи для упражнения
     */
    public function getForExercise(int $exerciseId): Collection
    {
        return AudioRecording::forExercise($exerciseId)
            ->orderBy('recorded_at', 'desc')
            ->get();
    }

    /**
     * Получить записи для блока сессии
     */
    public function getForSessionBlock(int $sessionBlockId): Collection
    {
        return AudioRecording::forSessionBlock($sessionBlockId)
            ->orderBy('recorded_at', 'desc')
            ->get();
    }

    /**
     * Получить записи за период
     */
    public function getRecordedBetween(int $userId, Carbon $from, Carbon $to): Collection
    {
        return AudioRecording::forUser($userId)
            ->recordedBetween($from, $to)
            ->orderBy('recorded_at', 'desc')
            ->get();
    }

    /**
     * Создать новую запись
     */
    public function create(array $data): AudioRecording
    {
        return AudioRecording::create($data);
    }

    /**
     * Обновить запись
     */
    public function update(AudioRecording $recording, array $data): AudioRecording
    {
        $recording->update($data);

        return $recording->fresh();
    }

    /**
     * Удалить запись
     */
    public function delete(AudioRecording $recording): bool
    {
        return $recording->delete();
    }

    /**
     * Получить статистику записей пользователя
     */
    public function getUserStatistics(int $userId): array
    {
        $recordings = AudioRecording::forUser($userId);

        return [
            'total_recordings'   => $recordings->count(),
            'total_duration'     => $recordings->sum('duration'),
            'total_size'         => $recordings->sum('file_size'),
            'average_quality'    => $recordings->whereNotNull('quality_rating')->avg('quality_rating'),
            'recordings_by_type' => $this->getRecordingsByType($userId),
        ];
    }

    /**
     * Получить количество записей по типам (упражнение/блок сессии)
     *
     * @param int $userId
     * @return array
     */
    private function getRecordingsByType(int $userId): array
    {
        return [
            'with_exercise'      => AudioRecording::forUser($userId)->whereNotNull('exercise_id')->count(),
            'with_session_block' => AudioRecording::forUser($userId)->whereNotNull('practice_session_block_id')->count(),
        ];
    }
}
