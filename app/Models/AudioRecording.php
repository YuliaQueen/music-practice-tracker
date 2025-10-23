<?php

namespace App\Models;

use App\Domains\Planning\Models\Exercise;
use App\Domains\Planning\Models\SessionBlock as PracticeSessionBlock;
use App\Domains\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class AudioRecording extends Model
{
    use HasFactory, SoftDeletes;

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
        'metadata' => 'array',
        'recorded_at' => 'datetime',
        'quality_rating' => 'integer',
        'file_size' => 'integer',
        'duration' => 'integer',
    ];

    protected $appends = [
        'audio_url',
        'formatted_file_size',
        'formatted_duration',
    ];

    /**
     * Получить пользователя, которому принадлежит запись
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Получить упражнение, к которому относится запись
     */
    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }

    /**
     * Получить блок сессии, к которому относится запись
     */
    public function sessionBlock(): BelongsTo
    {
        return $this->belongsTo(PracticeSessionBlock::class, 'practice_session_block_id');
    }

    /**
     * Получить URL для прослушивания аудиофайла через Laravel маршрут
     */
    public function getAudioUrlAttribute(): ?string
    {
        if (!$this->file_path) {
            return null;
        }

        // Используем маршрут Laravel для стриминга файла
        return route('audio-recordings.stream', $this->id);
    }

    /**
     * Получить размер файла в удобочитаемом формате
     */
    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            return $bytes . ' bytes';
        } elseif ($bytes == 1) {
            return $bytes . ' byte';
        } else {
            return '0 bytes';
        }
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
     * Удалить аудиофайл из MinIO при удалении записи
     */
    protected static function booted(): void
    {
        static::deleted(function (AudioRecording $recording) {
            if ($recording->file_path && Storage::disk('minio')->exists($recording->file_path)) {
                try {
                    Storage::disk('minio')->delete($recording->file_path);
                } catch (\Exception $e) {
                    \Log::error('Ошибка при удалении аудио файла из MinIO: ' . $e->getMessage());
                }
            }
        });
    }
}
