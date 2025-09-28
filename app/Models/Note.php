<?php

declare(strict_types=1);

namespace App\Models;

use App\Domains\Planning\Models\Exercise;
use App\Domains\User\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Модель нот - файлы нот, прикрепленные к упражнениям
 *
 * @property int           $id
 * @property int           $user_id
 * @property int|null      $exercise_id
 * @property string        $title                Название файла нот
 * @property string|null   $description          Описание/заметки к нотам
 * @property string        $filename             Оригинальное имя файла
 * @property string        $file_path            Путь к файлу в хранилище
 * @property string        $mime_type            MIME тип файла
 * @property int           $file_size            Размер файла в байтах
 * @property string        $file_hash            Хеш файла для дедупликации
 * @property array         $metadata             Дополнительные метаданные
 * @property bool          $is_public            Публичный доступ
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 *
 * @property User          $user
 * @property Exercise|null $exercise
 */
class Note extends Model
{
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    /**
     * Название таблицы
     */
    protected $table = 'notes';

    /**
     * Разрешенные MIME типы для нот
     */
    public const ALLOWED_MIME_TYPES = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'application/vnd.recordare.musicxml+xml',
        'application/vnd.recordare.musicxml',
        'audio/mpeg',
        'audio/wav',
        'audio/ogg',
    ];

    /**
     * Максимальный размер файла в байтах (50MB)
     */
    public const MAX_FILE_SIZE = 50 * 1024 * 1024;

    protected $fillable = [
        'user_id',
        'exercise_id',
        'title',
        'description',
        'filename',
        'file_path',
        'mime_type',
        'file_size',
        'file_hash',
        'metadata',
        'is_public',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'metadata' => 'array',
        'is_public' => 'boolean',
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
     * Scope для получения нот пользователя
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope для получения нот упражнения
     */
    public function scopeForExercise(Builder $query, int $exerciseId): Builder
    {
        return $query->where('exercise_id', $exerciseId);
    }

    /**
     * Scope для получения публичных нот
     */
    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope для получения нот по типу файла
     */
    public function scopeByMimeType(Builder $query, string $mimeType): Builder
    {
        return $query->where('mime_type', $mimeType);
    }

    /**
     * Получить URL файла
     */
    public function getUrlAttribute(): string
    {
        $baseUrl = config('filesystems.disks.minio.url');
        $bucket = config('filesystems.disks.minio.bucket');
        return $baseUrl . '/' . $bucket . '/' . $this->file_path;
    }

    /**
     * Получить временный URL для файла (для приватных файлов)
     */
    public function getTemporaryUrl(int $minutes = 60): string
    {
        return Storage::disk('minio')->temporaryUrl($this->file_path, now()->addMinutes($minutes));
    }

    /**
     * Проверить, является ли файл изображением
     */
    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Проверить, является ли файл PDF
     */
    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    /**
     * Проверить, является ли файл аудио
     */
    public function isAudio(): bool
    {
        return str_starts_with($this->mime_type, 'audio/');
    }

    /**
     * Проверить, является ли файл MusicXML
     */
    public function isMusicXml(): bool
    {
        return in_array($this->mime_type, [
            'application/vnd.recordare.musicxml+xml',
            'application/vnd.recordare.musicxml',
        ]);
    }

    /**
     * Получить размер файла в человекочитаемом формате
     */
    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Настройки для логирования активности
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'description', 'exercise_id', 'is_public'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
