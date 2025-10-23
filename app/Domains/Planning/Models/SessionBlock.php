<?php

declare(strict_types=1);

namespace App\Domains\Planning\Models;

use Carbon\Carbon;
use App\Enums\ExerciseType;
use App\Enums\SessionBlockStatus;
use Spatie\Activitylog\LogOptions;
use App\Domains\Shared\Models\BaseModel;
use Illuminate\Database\Eloquent\Builder;
use Database\Factories\SessionBlockFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
     * Типы блоков (backward compatibility)
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
     * Статусы блока (backward compatibility)
     */
    public const STATUS_PLANNED   = 'planned';
    public const STATUS_ACTIVE    = 'active';
    public const STATUS_PAUSED    = 'paused';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_SKIPPED   = 'skipped';
    /**
     * Все возможные типы
     */
    public const TYPES = ExerciseType::class;
    /**
     * Все возможные статусы
     */
    public const STATUSES = SessionBlockStatus::class;
    /**
     * Название таблицы
     */
    protected $table = 'practice_session_blocks';
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
        'type'             => ExerciseType::class,
        'status'           => SessionBlockStatus::class,
        'planned_duration' => 'integer',
        'actual_duration'  => 'integer',
        'sort_order'       => 'integer',
        'started_at'       => 'datetime',
        'completed_at'     => 'datetime',
        'settings'         => 'array',
    ];

    /**
     * Relationships to append to model's array/JSON form
     */
    protected $appends = ['audioRecordings'];

    /**
     * Accessor для автоматической сериализации audioRecordings
     */
    public function getAudioRecordingsAttribute()
    {
        // Если relationship уже загружен, возвращаем его
        if ($this->relationLoaded('audioRecordings')) {
            return $this->getRelation('audioRecordings');
        }
        
        // Иначе возвращаем пустую коллекцию
        return collect();
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return SessionBlockFactory::new();
    }

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
     * Связь с аудио записями блока
     */
    public function audioRecordings(): HasMany
    {
        return $this->hasMany(\App\Domains\Recording\Models\AudioRecording::class, 'practice_session_block_id');
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
        return $this->status === SessionBlockStatus::ACTIVE;
    }

    /**
     * Проверить, завершен ли блок
     */
    public function isCompleted(): bool
    {
        return $this->status === SessionBlockStatus::COMPLETED;
    }

    /**
     * Проверить, запланирован ли блок
     */
    public function isPlanned(): bool
    {
        return $this->status === SessionBlockStatus::PLANNED;
    }

    /**
     * Проверить, можно ли запустить блок
     */
    public function canBeStarted(): bool
    {
        return in_array($this->status, [SessionBlockStatus::PLANNED, SessionBlockStatus::PAUSED]);
    }

    /**
     * Проверить, можно ли завершить блок
     */
    public function canBeCompleted(): bool
    {
        return in_array($this->status, [SessionBlockStatus::ACTIVE, SessionBlockStatus::PAUSED]);
    }

    /**
     * Получить человекочитаемое название типа
     */
    public function getTypeLabel(): string
    {
        return $this->type instanceof ExerciseType ? $this->type->label() : 'Неизвестный тип';
    }

    /**
     * Получить иконку для типа блока
     */
    public function getTypeIcon(): string
    {
        return $this->type instanceof ExerciseType ? $this->type->icon() : '❓';
    }

    /**
     * Получить цвет для типа блока
     */
    public function getTypeColor(): string
    {
        return $this->type instanceof ExerciseType ? $this->type->color() : 'gray';
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
}
