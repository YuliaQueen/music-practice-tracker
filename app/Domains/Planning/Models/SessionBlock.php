<?php

declare(strict_types=1);

namespace App\Domains\Planning\Models;

use App\Domains\Shared\Models\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Database\Factories\SessionBlockFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Модель блока сессии - конкретный элемент занятия
 *
 * @property int           $id
 * @property int           $practice_session_id
 * @property int|null      $practice_template_block_id Блок шаблона, на основе которого создан
 * @property string        $title                      Название блока
 * @property string|null   $description                Описание блока
 * @property string        $type                       Тип блока
 * @property int           $planned_duration           Запланированная длительность в минутах
 * @property int|null      $actual_duration            Фактическая длительность в минутах
 * @property string        $status                     Статус блока
 * @property int           $sort_order                 Порядок выполнения в сессии
 * @property Carbon|null   $started_at                 Время начала блока
 * @property Carbon|null   $completed_at               Время завершения блока
 * @property array         $settings                   Настройки блока
 * @property Carbon        $created_at
 * @property Carbon        $updated_at
 * @property Carbon|null   $deleted_at
 *
 * @property Session       $session
 * @property TemplateBlock $templateBlock
 */
class SessionBlock extends BaseModel
{
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    /**
     * Название таблицы
     */
    protected $table = 'practice_session_blocks';

    /**
     * Типы блоков
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
     * Статусы блока
     */
    public const STATUS_PLANNED   = 'planned';
    public const STATUS_ACTIVE    = 'active';
    public const STATUS_PAUSED    = 'paused';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_SKIPPED   = 'skipped';

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
        self::STATUS_SKIPPED,
    ];

    protected $fillable = [
        'practice_session_id',
        'practice_template_block_id',
        'title',
        'description',
        'type',
        'planned_duration',
        'actual_duration',
        'status',
        'sort_order',
        'started_at',
        'completed_at',
        'settings',
    ];

    protected $casts = [
        'planned_duration' => 'integer',
        'actual_duration'  => 'integer',
        'sort_order'       => 'integer',
        'started_at'       => 'datetime',
        'completed_at'     => 'datetime',
        'settings'         => 'array',
    ];

    /**
     * Связь с сессией
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(Session::class, 'practice_session_id');
    }

    /**
     * Связь с блоком шаблона
     */
    public function templateBlock(): BelongsTo
    {
        return $this->belongsTo(TemplateBlock::class, 'practice_template_block_id');
    }


    /**
     * Scope: активные блоки
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope: завершенные блоки
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope: блоки по типу
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: блоки сессии
     */
    public function scopeForSession(Builder $query, int $sessionId): Builder
    {
        return $query->where('practice_session_id', $sessionId);
    }

    /**
     * Проверить, является ли блок активным
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Проверить, завершен ли блок
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Проверить, запланирован ли блок
     */
    public function isPlanned(): bool
    {
        return $this->status === self::STATUS_PLANNED;
    }

    /**
     * Проверить, можно ли запустить блок
     */
    public function canBeStarted(): bool
    {
        return in_array($this->status, [self::STATUS_PLANNED, self::STATUS_PAUSED]);
    }

    /**
     * Проверить, можно ли завершить блок
     */
    public function canBeCompleted(): bool
    {
        return in_array($this->status, [self::STATUS_ACTIVE, self::STATUS_PAUSED]);
    }

    /**
     * Получить человекочитаемое название типа
     */
    public function getTypeLabel(): string
    {
        return match ($this->type) {
            self::TYPE_WARMUP => 'Разминка',
            self::TYPE_TECHNIQUE => 'Техника',
            self::TYPE_REPERTOIRE => 'Репертуар',
            self::TYPE_IMPROVISATION => 'Импровизация',
            self::TYPE_SIGHT_READING => 'Чтение с листа',
            self::TYPE_THEORY => 'Теория',
            self::TYPE_BREAK => 'Перерыв',
            self::TYPE_CUSTOM => 'Пользовательский',
            default => 'Неизвестный тип',
        };
    }

    /**
     * Получить иконку для типа блока
     */
    public function getTypeIcon(): string
    {
        return match ($this->type) {
            self::TYPE_WARMUP => '🔥',
            self::TYPE_TECHNIQUE => '⚡',
            self::TYPE_REPERTOIRE => '🎵',
            self::TYPE_IMPROVISATION => '🎨',
            self::TYPE_SIGHT_READING => '👀',
            self::TYPE_THEORY => '📚',
            self::TYPE_BREAK => '☕',
            self::TYPE_CUSTOM => '⭐',
            default => '❓',
        };
    }

    /**
     * Получить цвет для типа блока
     */
    public function getTypeColor(): string
    {
        return match ($this->type) {
            self::TYPE_WARMUP => 'orange',
            self::TYPE_TECHNIQUE => 'yellow',
            self::TYPE_REPERTOIRE => 'blue',
            self::TYPE_IMPROVISATION => 'purple',
            self::TYPE_SIGHT_READING => 'green',
            self::TYPE_THEORY => 'gray',
            self::TYPE_BREAK => 'slate',
            self::TYPE_CUSTOM => 'pink',
            default => 'gray',
        };
    }

    /**
     * Получить настройку блока
     */
    public function getSetting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings, $key, $default);
    }

    /**
     * Установить настройку блока
     */
    public function setSetting(string $key, mixed $value): self
    {
        $settings = $this->settings ?? [];
        data_set($settings, $key, $value);
        $this->settings = $settings;

        return $this;
    }

    /**
     * Получить продолжительность для отображения
     */
    public function getDisplayDuration(): int
    {
        return $this->actual_duration ?? $this->planned_duration;
    }

    /**
     * Получить разность между запланированной и фактической длительностью
     */
    public function getDurationDifference(): ?int
    {
        if ($this->actual_duration === null) {
            return null;
        }

        return $this->actual_duration - $this->planned_duration;
    }

    /**
     * Настройки логирования активности
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'status', 'type', 'planned_duration', 'actual_duration'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return SessionBlockFactory::new();
    }
}
