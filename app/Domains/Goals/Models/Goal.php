<?php

declare(strict_types=1);

namespace App\Domains\Goals\Models;

use App\Domains\Shared\Models\BaseModel;
use App\Domains\User\Models\User;
use App\Enums\GoalType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Database\Factories\GoalFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Модель цели пользователя
 *
 * @property int           $id
 * @property int           $user_id
 * @property string        $title
 * @property string|null   $description
 * @property string        $type
 * @property array         $target
 * @property array|null    $progress
 * @property Carbon        $start_date
 * @property Carbon|null   $end_date
 * @property bool          $is_active
 * @property bool          $is_completed
 * @property Carbon|null   $completed_at
 * @property Carbon        $created_at
 * @property Carbon        $updated_at
 * @property Carbon|null   $deleted_at
 *
 * @property User          $user
 */
class Goal extends BaseModel
{
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    /**
     * Типы целей (backward compatibility)
     */
    public const TYPE_DAILY_MINUTES = 'daily_minutes';
    public const TYPE_WEEKLY_SESSIONS = 'weekly_sessions';
    public const TYPE_STREAK_DAYS = 'streak_days';
    public const TYPE_EXERCISE_TYPE = 'exercise_type';
    public const TYPE_MONTHLY_MINUTES = 'monthly_minutes';
    public const TYPE_YEARLY_SESSIONS = 'yearly_sessions';

    /**
     * Все возможные типы
     */
    public const TYPES = GoalType::class;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'type',
        'target',
        'progress',
        'start_date',
        'end_date',
        'is_active',
        'is_completed',
        'completed_at',
    ];

    protected $casts = [
        'type' => GoalType::class,
        'target' => 'array',
        'progress' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    /**
     * Связь с пользователем
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: активные цели
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: завершенные цели
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('is_completed', true);
    }

    /**
     * Scope: цели пользователя
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: цели по типу
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: текущие цели (не завершенные и не истекшие)
     */
    public function scopeCurrent(Builder $query): Builder
    {
        return $query->where('is_active', true)
                    ->where('is_completed', false)
                    ->where(function ($q) {
                        $q->whereNull('end_date')
                          ->orWhere('end_date', '>=', now()->toDateString());
                    });
    }

    /**
     * Проверить, активна ли цель
     */
    public function isActive(): bool
    {
        return $this->is_active && !$this->is_completed;
    }

    /**
     * Проверить, завершена ли цель
     */
    public function isCompleted(): bool
    {
        return $this->is_completed;
    }

    /**
     * Проверить, истекла ли цель
     */
    public function isExpired(): bool
    {
        return $this->end_date && $this->end_date->isPast();
    }

    /**
     * Получить прогресс в процентах
     */
    public function getProgressPercentage(): int
    {
        if (!$this->progress || !isset($this->progress['current']) || !isset($this->progress['total'])) {
            return 0;
        }

        $current = (int) $this->progress['current'];
        $total = (int) $this->progress['total'];

        if ($total <= 0) {
            return 0;
        }

        return min(100, (int) round(($current / $total) * 100));
    }

    /**
     * Получить оставшееся количество для достижения цели
     */
    public function getRemaining(): int
    {
        if (!$this->progress || !isset($this->progress['current']) || !isset($this->progress['total'])) {
            return $this->getTargetValue();
        }

        $current = (int) $this->progress['current'];
        $total = (int) $this->progress['total'];

        return max(0, $total - $current);
    }

    /**
     * Получить целевое значение
     */
    public function getTargetValue(): int
    {
        if (!$this->target || !isset($this->target['value'])) {
            return 0;
        }

        return (int) $this->target['value'];
    }

    /**
     * Получить текущее значение прогресса
     */
    public function getCurrentValue(): int
    {
        if (!$this->progress || !isset($this->progress['current'])) {
            return 0;
        }

        return (int) $this->progress['current'];
    }

    /**
     * Обновить прогресс
     */
    public function updateProgress(int $current, int $total = null): self
    {
        $total = $total ?? $this->getTargetValue();
        
        $this->progress = [
            'current' => $current,
            'total' => $total,
        ];

        // Проверить, достигнута ли цель
        if ($current >= $total && !$this->is_completed) {
            $this->markAsCompleted();
        }

        // Сохраняем изменения в базу данных
        $this->save();

        return $this;
    }

    /**
     * Отметить цель как завершенную
     */
    public function markAsCompleted(): self
    {
        $this->is_completed = true;
        $this->completed_at = now();

        return $this;
    }

    /**
     * Получить человекочитаемое название типа
     */
    public function getTypeLabel(): string
    {
        return $this->type instanceof GoalType ? $this->type->label() : 'Неизвестный тип';
    }

    /**
     * Получить иконку для типа цели
     */
    public function getTypeIcon(): string
    {
        return $this->type instanceof GoalType ? $this->type->icon() : '❓';
    }

    /**
     * Получить цвет для типа цели
     */
    public function getTypeColor(): string
    {
        return $this->type instanceof GoalType ? $this->type->color() : 'gray';
    }

    /**
     * Получить описание цели
     */
    public function getDescription(): string
    {
        $target = $this->getTargetValue();

        return match ($this->type) {
            GoalType::DAILY_MINUTES => "Практиковать {$target} минут в день",
            GoalType::WEEKLY_SESSIONS => "Провести {$target} сессий в неделю",
            GoalType::STREAK_DAYS => "Практиковать {$target} дней подряд",
            GoalType::EXERCISE_TYPE => "Практиковать {$target} минут типа '{$this->target['exercise_type']}'",
            GoalType::MONTHLY_MINUTES => "Практиковать {$target} минут в месяц",
            GoalType::YEARLY_SESSIONS => "Провести {$target} сессий в год",
            default => 'Неизвестная цель',
        };
    }

    /**
     * Настройки логирования активности
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'type', 'target', 'progress', 'is_active', 'is_completed'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return GoalFactory::new();
    }
}