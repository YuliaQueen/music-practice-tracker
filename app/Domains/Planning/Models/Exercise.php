<?php

declare(strict_types=1);

namespace App\Domains\Planning\Models;

use Carbon\Carbon;
use App\Models\Note;
use App\Enums\ExerciseType;
use App\Enums\ExerciseStatus;
use App\Domains\User\Models\User;
use Spatie\Activitylog\LogOptions;
use Database\Factories\ExerciseFactory;
use App\Domains\Shared\Models\BaseModel;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Модель отдельного упражнения - упражнение вне сессии
 *
 * @property int         $id
 * @property int         $user_id
 * @property string      $title                 Название упражнения
 * @property string|null $description           Описание упражнения
 * @property string      $type                  Тип упражнения
 * @property int         $planned_duration      Запланированная длительность в минутах
 * @property int|null    $actual_duration       Фактическая длительность в минутах
 * @property string      $status                Статус упражнения
 * @property Carbon|null $scheduled_for         Запланированное время выполнения
 * @property Carbon|null $started_at            Фактическое время начала
 * @property Carbon|null $completed_at          Время завершения
 * @property array       $metadata              Дополнительные данные упражнения
 * @property bool        $is_archived           Упражнение в архиве
 * @property Carbon|null $archived_at           Дата архивирования
 * @property Carbon|null $started_learning_at   Дата начала изучения
 * @property Carbon|null $completed_learning_at Дата завершения изучения
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property User        $user
 * @property SessionBlock[] $sessionBlocks
 */
class Exercise extends BaseModel
{
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    /**
     * Типы упражнений (backward compatibility)
     */
    public const TYPE_WARMUP        = 'warmup';
    public const TYPE_TECHNIQUE     = 'technique';
    public const TYPE_REPERTOIRE    = 'repertoire';
    public const TYPE_IMPROVISATION = 'improvisation';
    public const TYPE_SIGHT_READING = 'sight_reading';
    public const TYPE_THEORY        = 'theory';
    public const TYPE_BREAK         = 'break';
    public const TYPE_CUSTOM        = 'custom';
    /**
     * Статусы упражнения (backward compatibility)
     */
    public const STATUS_PLANNED   = 'planned';
    public const STATUS_ACTIVE    = 'active';
    public const STATUS_PAUSED    = 'paused';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';
    /**
     * Все возможные типы
     */
    public const TYPES = ExerciseType::class;
    /**
     * Все возможные статусы
     */
    public const STATUSES = ExerciseStatus::class;
    /**
     * Название таблицы
     */
    protected $table = 'exercises';
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'type',
        'planned_duration',
        'actual_duration',
        'status',
        'scheduled_for',
        'started_at',
        'completed_at',
        'metadata',
        'is_archived',
        'archived_at',
        'started_learning_at',
        'completed_learning_at',
    ];

    protected $casts = [
        'type'                  => ExerciseType::class,
        'status'                => ExerciseStatus::class,
        'planned_duration'      => 'integer',
        'actual_duration'       => 'integer',
        'scheduled_for'         => 'datetime',
        'started_at'            => 'datetime',
        'completed_at'          => 'datetime',
        'metadata'              => 'array',
        'is_archived'           => 'boolean',
        'archived_at'           => 'datetime',
        'started_learning_at'   => 'datetime',
        'completed_learning_at' => 'datetime',
    ];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return ExerciseFactory::new();
    }

    /**
     * Связь с пользователем
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Получить блоки сессий с таким же названием упражнения
     * Примечание: это не стандартная связь Eloquent, так как нет прямой FK
     */
    public function getRelatedSessionBlocks()
    {
        return SessionBlock::where('title', $this->title)
            ->whereHas('session', function ($query) {
                $query->where('user_id', $this->user_id);
            })
            ->get();
    }

    /**
     * Связь с нотами упражнения
     */
    public function notes(): HasMany
    {
        return $this->hasMany(Note::class, 'exercise_id');
    }

    /**
     * Связь с аудио записями упражнения
     */
    public function audioRecordings(): HasMany
    {
        return $this->hasMany(\App\Domains\Recording\Models\AudioRecording::class, 'exercise_id');
    }

    /**
     * Scope для получения упражнений пользователя
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope для получения упражнений по статусу
     */
    public function scopeWithStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope для получения упражнений по типу
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Scope для получения запланированных упражнений
     */
    public function scopePlanned(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PLANNED);
    }

    /**
     * Scope для получения активных упражнений
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope для получения завершенных упражнений
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Получить читаемое название типа упражнения
     */
    public function getTypeLabelAttribute(): string
    {
        return $this->type instanceof ExerciseType ? $this->type->label() : $this->type;
    }

    /**
     * Получить читаемое название статуса
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->status instanceof ExerciseStatus ? $this->status->label() : $this->status;
    }

    /**
     * Получить иконку для типа упражнения
     */
    public function getTypeIconAttribute(): string
    {
        return $this->type instanceof ExerciseType ? $this->type->icon() : '⭐';
    }

    /**
     * Проверить, можно ли начать упражнение
     */
    public function canStart(): bool
    {
        return $this->status === ExerciseStatus::PLANNED;
    }

    /**
     * Проверить, можно ли приостановить упражнение
     */
    public function canPause(): bool
    {
        return $this->status === ExerciseStatus::ACTIVE;
    }

    /**
     * Проверить, можно ли завершить упражнение
     */
    public function canComplete(): bool
    {
        return in_array($this->status, [ExerciseStatus::ACTIVE, ExerciseStatus::PAUSED]);
    }

    /**
     * Проверить, можно ли отменить упражнение
     */
    public function canCancel(): bool
    {
        return in_array($this->status, [ExerciseStatus::PLANNED, ExerciseStatus::ACTIVE, ExerciseStatus::PAUSED]);
    }

    /**
     * Scope для получения только активных (неархивных) упражнений
     */
    public function scopeNotArchived(Builder $query): Builder
    {
        return $query->where('is_archived', false);
    }

    /**
     * Scope для получения архивных упражнений
     */
    public function scopeArchived(Builder $query): Builder
    {
        return $query->where('is_archived', true);
    }

    /**
     * Проверить, находится ли упражнение в архиве
     */
    public function isArchived(): bool
    {
        return $this->is_archived;
    }

    /**
     * Получить количество сессий с этим упражнением
     */
    public function getSessionsCountAttribute(): int
    {
        return SessionBlock::where('title', $this->title)
            ->whereHas('session', function ($query) {
                $query->where('user_id', $this->user_id)
                      ->where('status', 'completed');
            })
            ->distinct('practice_session_id')
            ->count('practice_session_id');
    }

    /**
     * Получить общее время практики (в минутах)
     */
    public function getTotalPracticeTimeAttribute(): int
    {
        return (int) SessionBlock::where('title', $this->title)
            ->whereHas('session', function ($query) {
                $query->where('user_id', $this->user_id);
            })
            ->whereNotNull('actual_duration')
            ->sum('actual_duration');
    }

    /**
     * Получить среднюю длительность практики за сессию (в минутах)
     */
    public function getAveragePracticeTimeAttribute(): float
    {
        $count = SessionBlock::where('title', $this->title)
            ->whereHas('session', function ($query) {
                $query->where('user_id', $this->user_id);
            })
            ->whereNotNull('actual_duration')
            ->count();

        if ($count === 0) {
            return 0.0;
        }

        return round($this->total_practice_time / $count, 1);
    }

    /**
     * Получить количество дней изучения
     */
    public function getLearningDaysAttribute(): ?int
    {
        if (!$this->started_learning_at || !$this->completed_learning_at) {
            return null;
        }

        return (int) $this->started_learning_at->diffInDays($this->completed_learning_at);
    }

    /**
     * Получить статистику изучения
     *
     * @return array{sessions_count: int, total_practice_time: int, average_practice_time: float, learning_days: int|null, started_at: string|null, completed_at: string|null}
     */
    public function getLearningStatistics(): array
    {
        return [
            'sessions_count'        => $this->sessions_count,
            'total_practice_time'   => $this->total_practice_time,
            'average_practice_time' => $this->average_practice_time,
            'learning_days'         => $this->learning_days,
            'started_at'            => $this->started_learning_at?->format('Y-m-d'),
            'completed_at'          => $this->completed_learning_at?->format('Y-m-d'),
        ];
    }

    /**
     * Настроить логирование активности
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'description', 'type', 'planned_duration', 'status', 'scheduled_for'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
