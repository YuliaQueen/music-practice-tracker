<?php

declare(strict_types=1);

namespace App\Domains\Recording\Models;

use Carbon\Carbon;
use App\Domains\User\Models\User;
use Spatie\Activitylog\LogOptions;
use App\Domains\Shared\Models\BaseModel;
use Illuminate\Database\Eloquent\Builder;
use App\Domains\Planning\Models\Exercise;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Domains\Planning\Models\SessionBlock;

/**
 * Модель аудио записи практики
 *
 * @property int         $id
 * @property int         $user_id
 * @property int|null    $exercise_id
 * @property int|null    $practice_session_block_id
 * @property string|null $title                      Название записи
 * @property string|null $notes                      Заметки к записи
 * @property string      $file_path                  Путь к файлу в MinIO
 * @property string      $file_name                  Оригинальное имя файла
 * @property string      $mime_type                  MIME тип файла
 * @property int         $file_size                  Размер файла в байтах
 * @property int|null    $duration                   Длительность в секундах
 * @property int|null    $quality_rating             Оценка качества (1-5)
 * @property array       $metadata                   Дополнительные метаданные
 * @property Carbon      $recorded_at                Дата и время записи
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property User              $user
 * @property Exercise|null     $exercise
 * @property SessionBlock|null $sessionBlock
 */
class AudioRecording extends BaseModel
{
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    /**
     * Название таблицы
     */
    protected $table = 'audio_recordings';

    /**
     * Максимальный размер файла в байтах (50MB)
     */
    public const MAX_FILE_SIZE = 52428800;

    /**
     * Разрешенные MIME типы
     */
    public const ALLOWED_MIME_TYPES = [
        'audio/webm',
        'audio/mp4',
        'audio/x-m4a',
        'audio/wav',
        'audio/mpeg',
        'audio/mp3',
    ];

    /**
     * Разрешенные расширения файлов
     */
    public const ALLOWED_EXTENSIONS = ['webm', 'mp4', 'm4a', 'wav', 'mp3'];

    protected $fillable = [
        'user_id',
        'exercise_id',
        'practice_session_block_id',
        'title',
        'notes',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'duration',
        'quality_rating',
        'metadata',
        'recorded_at',
    ];

    protected $casts = [
        'metadata'       => 'array',
        'recorded_at'    => 'datetime',
        'quality_rating' => 'integer',
        'file_size'      => 'integer',
        'duration'       => 'integer',
    ];

    protected $appends = [
        'audio_url',
        'formatted_file_size',
        'formatted_duration',
    ];

    /**
     * Связь с пользователем
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Связь с упражнением
     */
    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }

    /**
     * Связь с блоком сессии
     */
    public function sessionBlock(): BelongsTo
    {
        return $this->belongsTo(SessionBlock::class, 'practice_session_block_id');
    }

    /**
     * Scope: записи пользователя
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: записи упражнения
     */
    public function scopeForExercise(Builder $query, int $exerciseId): Builder
    {
        return $query->where('exercise_id', $exerciseId);
    }

    /**
     * Scope: записи блока сессии
     */
    public function scopeForSessionBlock(Builder $query, int $sessionBlockId): Builder
    {
        return $query->where('practice_session_block_id', $sessionBlockId);
    }

    /**
     * Scope: записи с определенной оценкой качества
     */
    public function scopeWithQualityRating(Builder $query, int $rating): Builder
    {
        return $query->where('quality_rating', $rating);
    }

    /**
     * Scope: записи за период
     */
    public function scopeRecordedBetween(Builder $query, Carbon $from, Carbon $to): Builder
    {
        return $query->whereBetween('recorded_at', [$from, $to]);
    }

    /**
     * Получить URL для прослушивания аудиофайла
     */
    public function getAudioUrlAttribute(): ?string
    {
        if (!$this->file_path) {
            return null;
        }

        return route('audio-recordings.stream', $this->id);
    }

    /**
     * Получить размер файла в удобочитаемом формате
     */
    public function getFormattedFileSizeAttribute(): string
    {
        return $this->formatBytes($this->file_size);
    }

    /**
     * Получить длительность в удобочитаемом формате (мм:сс)
     */
    public function getFormattedDurationAttribute(): ?string
    {
        if (!$this->duration) {
            return null;
        }

        $minutes = floor($this->duration / 60);
        $seconds = $this->duration % 60;

        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    /**
     * Проверить, принадлежит ли запись пользователю
     */
    public function belongsToUser(int $userId): bool
    {
        return $this->user_id === $userId;
    }

    /**
     * Проверить, связана ли запись с упражнением
     */
    public function hasExercise(): bool
    {
        return $this->exercise_id !== null;
    }

    /**
     * Проверить, связана ли запись с блоком сессии
     */
    public function hasSessionBlock(): bool
    {
        return $this->practice_session_block_id !== null;
    }

    /**
     * Проверить, существует ли файл в хранилище
     */
    public function fileExists(): bool
    {
        return $this->file_path && Storage::disk('minio')->exists($this->file_path);
    }

    /**
     * Получить метаданные со значением по умолчанию
     *
     * @param string $key
     * @param mixed  $default
     * @return mixed
     */
    public function getMetadata(string $key, mixed $default = null): mixed
    {
        return data_get($this->metadata, $key, $default);
    }

    /**
     * Установить метаданные
     *
     * @param string $key
     * @param mixed  $value
     * @return self
     */
    public function setMetadata(string $key, mixed $value): self
    {
        $metadata = $this->metadata ?? [];
        data_set($metadata, $key, $value);
        $this->metadata = $metadata;

        return $this;
    }

    /**
     * Форматировать байты в читаемый формат
     *
     * @param int $bytes
     * @return string
     */
    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        }

        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }

        if ($bytes > 1) {
            return $bytes . ' bytes';
        }

        if ($bytes === 1) {
            return '1 byte';
        }

        return '0 bytes';
    }

    /**
     * Настройки логирования активности
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'notes', 'quality_rating', 'file_name'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Boot метод модели
     */
    protected static function booted(): void
    {
        // Удалить файл из MinIO при удалении записи
        static::deleted(function (AudioRecording $recording) {
            if ($recording->file_path && Storage::disk('minio')->exists($recording->file_path)) {
                try {
                    Storage::disk('minio')->delete($recording->file_path);
                } catch (\Exception $e) {
                    \Log::error('Ошибка при удалении аудио файла из MinIO', [
                        'recording_id' => $recording->id,
                        'file_path'    => $recording->file_path,
                        'error'        => $e->getMessage(),
                    ]);
                }
            }
        });
    }
}
