<?php

declare(strict_types=1);

namespace App\Domains\Planning\Models;

use App\Domains\Shared\Models\BaseModel;
use App\Domains\User\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Модель отдельного упражнения - упражнение вне сессии
 *
 * @property int           $id
 * @property int           $user_id
 * @property string        $title                Название упражнения
 * @property string|null   $description          Описание упражнения
 * @property string        $type                 Тип упражнения
 * @property int           $planned_duration      Запланированная длительность в минутах
 * @property int|null      $actual_duration      Фактическая длительность в минутах
 * @property string        $status               Статус упражнения
 * @property Carbon|null   $scheduled_for        Запланированное время выполнения
 * @property Carbon|null   $started_at           Фактическое время начала
 * @property Carbon|null   $completed_at         Время завершения
 * @property array         $metadata             Дополнительные данные упражнения
 * @property Carbon        $created_at
 * @property Carbon        $updated_at
 * @property Carbon|null   $deleted_at
 *
 * @property User          $user
 * @property SessionBlock[] $sessionBlocks
 */
class Exercise extends BaseModel
{
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    /**
     * Название таблицы
     */
    protected $table = 'exercises';

    /**
     * Типы упражнений
     */
    public const TYPE_WARMUP        = 'warmup';            // Разминка
    public const TYPE_TECHNIQUE     = 'technique';         // Техника
    public const TYPE_REPERTOIRE    = 'repertoire';        // Репертуар
    public const TYPE_IMPROVISATION = 'improvisation';     // Импровизация
    public const TYPE_SIGHT_READING = 'sight_reading';     // Чтение с листа
    public const TYPE_THEORY        = 'theory';            // Теория
    public const TYPE_BREAK         = 'break';             // Перерыв
    public const TYPE_CUSTOM        = 'custom';            // Пользовательский

    /**
     * Статусы упражнения
     */
    public const STATUS_PLANNED   = 'planned';
    public const STATUS_ACTIVE    = 'active';
    public const STATUS_PAUSED    = 'paused';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Все возможные типы
     */
    public const TYPES = [
        self::TYPE_WARMUP,
        self::TYPE_TECHNIQUE,
        self::TYPE_REPERTOIRE,
        self::TYPE_IMPROVISATION,
        self::TYPE_SIGHT_READING,
        self::TYPE_THEORY,
        self::TYPE_BREAK,
        self::TYPE_CUSTOM,
    ];

    /**
     * Все возможные статусы
     */
    public const STATUSES = [
        self::STATUS_PLANNED,
        self::STATUS_ACTIVE,
        self::STATUS_PAUSED,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
    ];

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
        'planned_duration' => 'integer',
        'actual_duration'  => 'integer',
        'scheduled_for'    => 'datetime',
        'started_at'       => 'datetime',
        'completed_at'     => 'datetime',
        'metadata'         => 'array',
    ];

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
        $labels = [
            self::TYPE_WARMUP => 'Разминка',
            self::TYPE_TECHNIQUE => 'Техника',
            self::TYPE_REPERTOIRE => 'Репертуар',
            self::TYPE_IMPROVISATION => 'Импровизация',
            self::TYPE_SIGHT_READING => 'Чтение с листа',
            self::TYPE_THEORY => 'Теория',
            self::TYPE_BREAK => 'Перерыв',
            self::TYPE_CUSTOM => 'Пользовательский',
        ];

        return $labels[$this->type] ?? $this->type;
    }

    /**
     * Получить читаемое название статуса
     */
    public function getStatusLabelAttribute(): string
    {
        $labels = [
            self::STATUS_PLANNED => 'Запланировано',
            self::STATUS_ACTIVE => 'Активно',
            self::STATUS_PAUSED => 'Приостановлено',
            self::STATUS_COMPLETED => 'Завершено',
            self::STATUS_CANCELLED => 'Отменено',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Получить иконку для типа упражнения
     */
    public function getTypeIconAttribute(): string
    {
        $icons = [
            self::TYPE_WARMUP => '🔥',
            self::TYPE_TECHNIQUE => '⚡',
            self::TYPE_REPERTOIRE => '🎵',
            self::TYPE_IMPROVISATION => '🎨',
            self::TYPE_SIGHT_READING => '👀',
            self::TYPE_THEORY => '📚',
            self::TYPE_BREAK => '☕',
            self::TYPE_CUSTOM => '⭐',
        ];

        return $icons[$this->type] ?? '⭐';
    }

    /**
     * Проверить, можно ли начать упражнение
     */
    public function canStart(): bool
    {
        return $this->status === self::STATUS_PLANNED;
    }

    /**
     * Проверить, можно ли приостановить упражнение
     */
    public function canPause(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Проверить, можно ли завершить упражнение
     */
    public function canComplete(): bool
    {
        return in_array($this->status, [self::STATUS_ACTIVE, self::STATUS_PAUSED]);
    }

    /**
     * Проверить, можно ли отменить упражнение
     */
    public function canCancel(): bool
    {
        return in_array($this->status, [self::STATUS_PLANNED, self::STATUS_ACTIVE, self::STATUS_PAUSED]);
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