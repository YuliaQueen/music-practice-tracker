<?php

declare(strict_types=1);

namespace App\Domains\Planning\Models;

use App\Domains\Journal\Models\Note;
use App\Domains\Shared\Models\BaseModel;
use App\Domains\User\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Модель сессии занятий - основная единица планирования
 *
 * @property int            $id
 * @property int            $user_id
 * @property int|null       $practice_template_id Шаблон, на основе которого создана сессия
 * @property string         $title                Название сессии
 * @property string|null    $description          Описание целей сессии
 * @property int            $planned_duration     Запланированная длительность в минутах
 * @property int|null       $actual_duration      Фактическая длительность в минутах
 * @property string         $status               Статус сессии
 * @property Carbon|null    $scheduled_for        Запланированное время начала
 * @property Carbon|null    $started_at           Фактическое время начала
 * @property Carbon|null    $completed_at         Время завершения
 * @property array          $metadata             Дополнительные данные сессии
 * @property Carbon         $created_at
 * @property Carbon         $updated_at
 * @property Carbon|null    $deleted_at
 *
 * @property User           $user
 * @property Template|null  $template
 * @property SessionBlock[] $blocks
 * @property Note[]         $notes
 */
class Session extends BaseModel
{
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    /**
     * Название таблицы
     */
    protected $table = 'practice_sessions';

    /**
     * Статусы сессии
     */
    public const STATUS_PLANNED   = 'planned';
    public const STATUS_ACTIVE    = 'active';
    public const STATUS_PAUSED    = 'paused';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

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
        'practice_template_id',
        'title',
        'description',
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
     * Связь с шаблоном
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class, 'practice_template_id');
    }

    /**
     * Связь с блоками сессии
     */
    public function blocks(): HasMany
    {
        return $this->hasMany(SessionBlock::class, 'practice_session_id')->orderBy('sort_order');
    }

    /**
     * Связь с заметками
     */
    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'noteable');
    }

    /**
     * Scope: активные сессии
     */
    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope: завершенные сессии
     */
    #[Scope]
    protected function completed(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope: запланированные сессии
     */
    #[Scope]
    protected function planned(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PLANNED);
    }

    /**
     * Scope: сессии пользователя
     */
    #[Scope]
    protected function forUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: сессии за период
     */
    #[Scope]
    protected function inPeriod(Builder $query, Carbon $from, Carbon $to): Builder
    {
        return $query->whereBetween('started_at', [$from, $to]);
    }

    /**
     * Проверить, является ли сессия активной
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Проверить, завершена ли сессия
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Проверить, запланирована ли сессия
     */
    public function isPlanned(): bool
    {
        return $this->status === self::STATUS_PLANNED;
    }

    /**
     * Проверить, можно ли запустить сессию
     */
    public function canBeStarted(): bool
    {
        return in_array($this->status, [self::STATUS_PLANNED, self::STATUS_PAUSED]);
    }

    /**
     * Проверить, можно ли завершить сессию
     */
    public function canBeCompleted(): bool
    {
        return in_array($this->status, [self::STATUS_ACTIVE, self::STATUS_PAUSED]);
    }

    /**
     * Получить общую продолжительность блоков
     */
    public function getTotalBlocksDuration(): int
    {
        return $this->blocks->sum('planned_duration');
    }

    /**
     * Получить фактическую продолжительность блоков
     */
    public function getTotalBlocksActualDuration(): int
    {
        return $this->blocks->sum('actual_duration');
    }

    /**
     * Получить прогресс сессии в процентах
     */
    public function getProgressPercentage(): int
    {
        $totalBlocks = $this->blocks->count();

        // Ранний возврат для избежания деления на ноль
        if ($totalBlocks <= 0) {
            return 0;
        }

        $completedBlocks = $this->blocks->where('status', SessionBlock::STATUS_COMPLETED)->count();

        return 0; //TODO
    }

    /**
     * Получить текущий активный блок
     */
    public function getCurrentBlock(): ?SessionBlock
    {
        return $this->blocks->where('status', SessionBlock::STATUS_ACTIVE)->first();
    }

    /**
     * Получить следующий блок для выполнения
     */
    public function getNextBlock(): ?SessionBlock
    {
        return $this->blocks
            ->where('status', SessionBlock::STATUS_PLANNED)
            ->sortBy('sort_order')
            ->first();
    }

    /**
     * Получить метаданные со значением по умолчанию
     */
    public function getMetadata(string $key, mixed $default = null): mixed
    {
        return data_get($this->metadata, $key, $default);
    }

    /**
     * Установить метаданные
     */
    public function setMetadata(string $key, mixed $value): self
    {
        $metadata = $this->metadata ?? [];
        data_set($metadata, $key, $value);
        $this->metadata = $metadata;

        return $this;
    }

    /**
     * Настройки логирования активности
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'status', 'planned_duration', 'actual_duration'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
