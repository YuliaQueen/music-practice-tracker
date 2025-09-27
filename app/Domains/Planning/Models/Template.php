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
 * Модель шаблона сессии - переиспользуемая структура занятия
 *
 * @property int             $id
 * @property int             $user_id
 * @property string          $name              Название шаблона
 * @property string|null     $description       Описание шаблона
 * @property string          $category          Категория шаблона
 * @property string          $difficulty_level  Уровень сложности
 * @property int             $total_duration    Общая длительность в минутах
 * @property bool            $is_public         Доступен ли другим пользователям
 * @property bool            $is_featured       Рекомендуемый шаблон
 * @property int             $usage_count       Количество использований
 * @property array           $tags              Теги для поиска
 * @property array           $metadata          Дополнительные данные
 * @property Carbon          $created_at
 * @property Carbon          $updated_at
 * @property Carbon|null     $deleted_at
 *
 * @property User            $user
 * @property TemplateBlock[] $blocks
 * @property Session[]       $sessions
 */
class Template extends BaseModel
{
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    /**
     * Название таблицы
     */
    protected $table = 'practice_templates';

    /**
     * Категории шаблонов
     */
    public const CATEGORY_BEGINNER         = 'beginner';
    public const CATEGORY_INTERMEDIATE     = 'intermediate';
    public const CATEGORY_ADVANCED         = 'advanced';
    public const CATEGORY_TECHNIQUE_FOCUS  = 'technique_focus';
    public const CATEGORY_REPERTOIRE_FOCUS = 'repertoire_focus';
    public const CATEGORY_QUICK_PRACTICE   = 'quick_practice';
    public const CATEGORY_WARM_UP          = 'warm_up';
    public const CATEGORY_CUSTOM           = 'custom';

    /**
     * Уровни сложности
     */
    public const DIFFICULTY_BEGINNER     = 'beginner';
    public const DIFFICULTY_INTERMEDIATE = 'intermediate';
    public const DIFFICULTY_ADVANCED     = 'advanced';
    public const DIFFICULTY_EXPERT       = 'expert';

    /**
     * Все возможные категории
     */
    public const CATEGORIES = [
        self::CATEGORY_BEGINNER,
        self::CATEGORY_INTERMEDIATE,
        self::CATEGORY_ADVANCED,
        self::CATEGORY_TECHNIQUE_FOCUS,
        self::CATEGORY_REPERTOIRE_FOCUS,
        self::CATEGORY_QUICK_PRACTICE,
        self::CATEGORY_WARM_UP,
        self::CATEGORY_CUSTOM,
    ];

    /**
     * Все возможные уровни сложности
     */
    public const DIFFICULTY_LEVELS = [
        self::DIFFICULTY_BEGINNER,
        self::DIFFICULTY_INTERMEDIATE,
        self::DIFFICULTY_ADVANCED,
        self::DIFFICULTY_EXPERT,
    ];

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'category',
        'difficulty_level',
        'total_duration',
        'is_public',
        'is_featured',
        'usage_count',
        'tags',
        'metadata',
    ];

    protected $casts = [
        'total_duration' => 'integer',
        'is_public'      => 'boolean',
        'is_featured'    => 'boolean',
        'usage_count'    => 'integer',
        'tags'           => 'array',
        'metadata'       => 'array',
    ];

    /**
     * Связь с пользователем
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Связь с блоками шаблона
     */
    public function blocks(): HasMany
    {
        return $this->hasMany(TemplateBlock::class, 'practice_template_id')->orderBy('sort_order');
    }

    /**
     * Связь с сессиями, созданными из этого шаблона
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class, 'practice_template_id');
    }

    /**
     * Scope: публичные шаблоны
     */
    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope: рекомендуемые шаблоны
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope: шаблоны пользователя
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: доступные для пользователя (свои + публичные)
     */
    public function scopeAvailableFor(Builder $query, int $userId): Builder
    {
        return $query->where(function (Builder $q) use ($userId) {
            $q->where('user_id', $userId)->orWhere('is_public', true);
        });
    }

    /**
     * Scope: шаблоны по категории
     */
    public function scopeInCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    /**
     * Scope: шаблоны по уровню сложности
     */
    public function scopeByDifficulty(Builder $query, string $difficulty): Builder
    {
        return $query->where('difficulty_level', $difficulty);
    }

    /**
     * Scope: поиск по тегам
     */
    public function scopeWithTag(Builder $query, string $tag): Builder
    {
        return $query->whereJsonContains('tags', $tag);
    }

    /**
     * Scope: поиск по длительности
     */
    public function scopeByDuration(Builder $query, ?int $minDuration = null, ?int $maxDuration = null): Builder
    {
        if ($minDuration !== null) {
            $query->where('total_duration', '>=', $minDuration);
        }

        if ($maxDuration !== null) {
            $query->where('total_duration', '<=', $maxDuration);
        }

        return $query;
    }

    /**
     * Получить человекочитаемое название категории
     */
    public function getCategoryLabel(): string
    {
        return match ($this->category) {
            self::CATEGORY_BEGINNER => 'Для начинающих',
            self::CATEGORY_INTERMEDIATE => 'Средний уровень',
            self::CATEGORY_ADVANCED => 'Продвинутый уровень',
            self::CATEGORY_TECHNIQUE_FOCUS => 'Техника',
            self::CATEGORY_REPERTOIRE_FOCUS => 'Репертуар',
            self::CATEGORY_QUICK_PRACTICE => 'Быстрая практика',
            self::CATEGORY_WARM_UP => 'Разминка',
            self::CATEGORY_CUSTOM => 'Пользовательский',
            default => 'Неизвестная категория',
        };
    }

    /**
     * Получить человекочитаемое название уровня сложности
     */
    public function getDifficultyLabel(): string
    {
        return match ($this->difficulty_level) {
            self::DIFFICULTY_BEGINNER => 'Начинающий',
            self::DIFFICULTY_INTERMEDIATE => 'Средний',
            self::DIFFICULTY_ADVANCED => 'Продвинутый',
            self::DIFFICULTY_EXPERT => 'Эксперт',
            default => 'Неизвестный уровень',
        };
    }

    /**
     * Получить цвет для категории
     */
    public function getCategoryColor(): string
    {
        return match ($this->category) {
            self::CATEGORY_BEGINNER => 'green',
            self::CATEGORY_INTERMEDIATE => 'blue',
            self::CATEGORY_ADVANCED => 'purple',
            self::CATEGORY_TECHNIQUE_FOCUS => 'yellow',
            self::CATEGORY_REPERTOIRE_FOCUS => 'blue',
            self::CATEGORY_QUICK_PRACTICE => 'orange',
            self::CATEGORY_WARM_UP => 'red',
            self::CATEGORY_CUSTOM => 'gray',
            default => 'gray',
        };
    }

    /**
     * Увеличить счетчик использований
     */
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }

    /**
     * Проверить, принадлежит ли шаблон пользователю
     */
    public function belongsToUser(int $userId): bool
    {
        return $this->user_id === $userId;
    }

    /**
     * Проверить, доступен ли шаблон для пользователя
     */
    public function isAvailableFor(int $userId): bool
    {
        return $this->belongsToUser($userId) || $this->is_public;
    }

    /**
     * Получить общую длительность блоков
     */
    public function calculateTotalDuration(): int
    {
        return $this->blocks->sum('duration');
    }

    /**
     * Синхронизировать общую длительность с блоками
     */
    public function syncTotalDuration(): void
    {
        $this->update(['total_duration' => $this->calculateTotalDuration()]);
    }

    /**
     * Добавить тег к шаблону
     */
    public function addTag(string $tag): self
    {
        $tags = $this->tags ?? [];

        if (!in_array($tag, $tags)) {
            $tags[]     = $tag;
            $this->tags = $tags;
        }

        return $this;
    }

    /**
     * Удалить тег из шаблона
     */
    public function removeTag(string $tag): self
    {
        $tags       = $this->tags ?? [];
        $this->tags = array_values(array_filter($tags, fn($t) => $t !== $tag));

        return $this;
    }

    /**
     * Проверить наличие тега
     */
    public function hasTag(string $tag): bool
    {
        return in_array($tag, $this->tags ?? []);
    }

    /**
     * Получить метаданные с значением по умолчанию
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
     * Создать сессию на основе шаблона
     */
    public function createSession(array $overrides = []): Session
    {
        $sessionData = array_merge([
            'user_id'              => $this->user_id,
            'practice_template_id' => $this->id,
            'title'                => $this->name,
            'description'          => $this->description,
            'planned_duration'     => $this->total_duration,
            'status'               => Session::STATUS_PLANNED,
        ], $overrides);

        $session = Session::create($sessionData);

        // Создаем блоки сессии на основе блоков шаблона
        foreach ($this->blocks as $templateBlock) {
            $session->blocks()->create([
                'practice_template_block_id' => $templateBlock->id,
                'title'                      => $templateBlock->title,
                'description'                => $templateBlock->description,
                'type'                       => $templateBlock->type,
                'planned_duration'           => $templateBlock->duration,
                'status'                     => SessionBlock::STATUS_PLANNED,
                'sort_order'                 => $templateBlock->sort_order,
                'settings'                   => $templateBlock->settings,
            ]);
        }

        $this->incrementUsage();

        return $session;
    }

    /**
     * Настройки логирования активности
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'category', 'difficulty_level', 'is_public', 'usage_count'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
