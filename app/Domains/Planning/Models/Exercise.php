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
    ];

    protected $casts = [
        'type'             => ExerciseType::class,
        'status'           => ExerciseStatus::class,
        'planned_duration' => 'integer',
        'actual_duration'  => 'integer',
        'scheduled_for'    => 'datetime',
        'started_at'       => 'datetime',
        'completed_at'     => 'datetime',
        'metadata'         => 'array',
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
     * Связь с блоками сессий (если упражнение использовалось в сессиях)
     */
    public function sessionBlocks(): HasMany
    {
        return $this->hasMany(SessionBlock::class, 'exercise_id');
    }

    /**
     * Связь с нотами упражнения
     */
    public function notes(): HasMany
    {
        return $this->hasMany(Note::class, 'exercise_id');
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
