<?php

declare(strict_types=1);

namespace App\Domains\Recording\Services;

use Throwable;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Domains\Planning\Models\SessionBlock;
use App\Domains\Recording\Models\AudioRecording;
use App\Domains\Recording\Contracts\AudioRecordingRepositoryInterface;

/**
 * Сервис для работы с аудио записями
 */
readonly class AudioRecordingService
{
    public function __construct(
        private AudioRecordingRepositoryInterface $repository
    ) {}

    /**
     * Создать новую аудио запись с загрузкой файла
     *
     * @param int          $userId
     * @param UploadedFile $file
     * @param array        $data
     * @return array
     * @throws Throwable
     */
    public function createRecording(int $userId, UploadedFile $file, array $data): array
    {
        // Валидация связей
        $validationResult = $this->validateRecordingRelations($data);
        if (!$validationResult['success']) {
            return $validationResult;
        }

        // Проверка прав доступа
        $authorizationResult = $this->authorizeRecordingAccess($userId, $data);
        if (!$authorizationResult['success']) {
            return $authorizationResult;
        }

        try {
            DB::beginTransaction();

            // Загружаем файл в MinIO
            $fileData = $this->uploadFile($userId, $file);

            // Создаем запись в БД
            $recording = $this->repository->create([
                'user_id'                   => $userId,
                'exercise_id'               => $data['exercise_id'] ?? null,
                'practice_session_block_id' => $data['practice_session_block_id'] ?? null,
                'title'                     => $data['title'] ?? null,
                'notes'                     => $data['notes'] ?? null,
                'file_path'                 => $fileData['path'],
                'file_name'                 => $fileData['original_name'],
                'mime_type'                 => $fileData['mime_type'],
                'file_size'                 => $fileData['size'],
                'quality_rating'            => $data['quality_rating'] ?? null,
                'recorded_at'               => now(),
            ]);

            DB::commit();

            return [
                'success'   => true,
                'recording' => $recording,
                'message'   => 'Запись успешно сохранена',
            ];
        } catch (\Throwable $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            // Удаляем файл если он был загружен
            if (isset($fileData['path'])) {
                $this->deleteFile($fileData['path']);
            }

            Log::error('Ошибка при создании аудио записи', [
                'user_id' => $userId,
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Ошибка при сохранении записи',
            ];
        }
    }

    /**
     * Обновить аудио запись
     *
     * @param AudioRecording $recording
     * @param array          $data
     * @return array
     */
    public function updateRecording(AudioRecording $recording, array $data): array
    {
        try {
            $updated = $this->repository->update($recording, $data);

            return [
                'success'   => true,
                'recording' => $updated,
                'message'   => 'Запись обновлена',
            ];
        } catch (\Exception $e) {
            Log::error('Ошибка при обновлении аудио записи', [
                'recording_id' => $recording->id,
                'error'        => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Ошибка при обновлении записи',
            ];
        }
    }

    /**
     * Удалить аудио запись
     *
     * @param AudioRecording $recording
     * @return array
     */
    public function deleteRecording(AudioRecording $recording): array
    {
        try {
            $this->repository->delete($recording);

            return [
                'success' => true,
                'message' => 'Запись удалена',
            ];
        } catch (\Exception $e) {
            Log::error('Ошибка при удалении аудио записи', [
                'recording_id' => $recording->id,
                'error'        => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Ошибка при удалении записи',
            ];
        }
    }

    /**
     * Получить поток файла для воспроизведения
     *
     * @param AudioRecording $recording
     * @return resource|false
     */
    public function getFileStream(AudioRecording $recording)
    {
        if (!$recording->fileExists()) {
            return false;
        }

        return Storage::disk('minio')->readStream($recording->file_path);
    }

    /**
     * Загрузить файл в MinIO
     *
     * @param int          $userId
     * @param UploadedFile $file
     * @return array
     * @throws \Exception
     */
    private function uploadFile(int $userId, UploadedFile $file): array
    {
        $originalFileName = $file->getClientOriginalName();
        $extension        = $file->getClientOriginalExtension();
        $uniqueFilename   = Str::uuid() . '.' . $extension;
        $filePath         = 'audio-recordings/' . $userId . '/' . $uniqueFilename;

        try {
            Storage::disk('minio')->put($filePath, file_get_contents($file->getPathname()));

            return [
                'path'          => $filePath,
                'original_name' => $originalFileName,
                'mime_type'     => $file->getMimeType(),
                'size'          => $file->getSize(),
            ];
        } catch (\Exception $e) {
            Log::error('Ошибка при загрузке файла в MinIO', [
                'user_id' => $userId,
                'file'    => $originalFileName,
                'error'   => $e->getMessage(),
            ]);

            throw new \Exception('Ошибка при загрузке файла в хранилище');
        }
    }

    /**
     * Удалить файл из MinIO
     *
     * @param string $filePath
     * @return void
     */
    private function deleteFile(string $filePath): void
    {
        try {
            if (Storage::disk('minio')->exists($filePath)) {
                Storage::disk('minio')->delete($filePath);
            }
        } catch (\Exception $e) {
            Log::error('Ошибка при удалении файла из MinIO', [
                'file_path' => $filePath,
                'error'     => $e->getMessage(),
            ]);
        }
    }

    /**
     * Валидация связей записи
     *
     * @param array $data
     * @return array
     */
    private function validateRecordingRelations(array $data): array
    {
        $hasExercise     = !empty($data['exercise_id']);
        $hasSessionBlock = !empty($data['practice_session_block_id']);

        if (!$hasExercise && !$hasSessionBlock) {
            return [
                'success' => false,
                'message' => 'Запись должна быть связана с упражнением или блоком сессии',
            ];
        }

        if ($hasExercise && $hasSessionBlock) {
            return [
                'success' => false,
                'message' => 'Запись может быть связана только с упражнением ИЛИ блоком сессии',
            ];
        }

        return ['success' => true];
    }

    /**
     * Проверка прав доступа к связанным сущностям
     *
     * @param int   $userId
     * @param array $data
     * @return array
     */
    private function authorizeRecordingAccess(int $userId, array $data): array
    {
        if (!empty($data['exercise_id'])) {
            $exercise = \App\Domains\Planning\Models\Exercise::find($data['exercise_id']);
            if (!$exercise || $exercise->user_id !== $userId) {
                return [
                    'success' => false,
                    'message' => 'Нет доступа к указанному упражнению',
                ];
            }
        }

        if (!empty($data['practice_session_block_id'])) {
            $sessionBlock = SessionBlock::with('session')
                ->find($data['practice_session_block_id']);

            if (!$sessionBlock || $sessionBlock->session->user_id !== $userId) {
                return [
                    'success' => false,
                    'message' => 'Нет доступа к указанному блоку сессии',
                ];
            }
        }

        return ['success' => true];
    }
}
