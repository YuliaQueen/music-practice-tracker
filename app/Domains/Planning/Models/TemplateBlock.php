<?php

declare(strict_types=1);

namespace App\Domains\Planning\Models;

use App\Domains\Shared\Models\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Модель блока шаблона - элемент структуры шаблона
 *
 * @property int            $id
 * @property int            $practice_template_id
 * @property string         $title             Название блока
 * @property string|null    $description       Описание блока
 * @property string         $type              Тип блока
 * @property int            $duration          Длительность в минутах
 * @property int            $sort_order        Порядок в шаблоне
 * @property bool           $is_mandatory      Обязательный ли блок
 * @property array          $settings          Настройки блока
 * @property array          $default_content   Содержимое по умолчанию
 * @property Carbon         $created_at
 * @property Carbon         $updated_at
 * @property Carbon|null    $deleted_at
 *
 * @property Template       $template
 * @property SessionBlock[] $sessionBlocks
 */
class TemplateBlock extends BaseModel
{
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    /**
     * Название таблицы
     */
    protected $table = 'practice_template_blocks';

    protected $fillable = [
        'practice_template_id',
        'title',
        'description',
        'type',
        'duration',
        'sort_order',
        'is_mandatory',
        'settings',
        'default_content',
    ];

    protected $casts = [
        'duration'        => 'integer',
        'sort_order'      => 'integer',
        'is_mandatory'    => 'boolean',
        'settings'        => 'array',
        'default_content' => 'array',
    ];

    /**
     * Связь с шаблоном
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class, 'practice_template_id');
    }

    /**
     * Связь с блоками сессий, созданными из этого блока шаблона
     */
    public function sessionBlocks(): HasMany
    {
        return $this->hasMany(SessionBlock::class, 'practice_template_block_id');
    }

    /**
     * Scope: обязательные блоки
     */
    public function scopeMandatory(Builder $query): Builder
    {
        return $query->where('is_mandatory', true);
    }

    /**
     * Scope: необязательные блоки
     */
    public function scopeOptional(Builder $query): Builder
    {
        return $query->where('is_mandatory', false);
    }

    /**
     * Scope: блоки по типу
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: блоки шаблона
     */
    public function scopeForTemplate(Builder $query, int $templateId): Builder
    {
        return $query->where('practice_template_id', $templateId);
    }

    /**
     * Получить человекочитаемое название типа
     */
    public function getTypeLabel(): string
    {
        return match ($this->type) {
            SessionBlock::TYPE_WARMUP => 'Разминка',
            SessionBlock::TYPE_TECHNIQUE => 'Техника',
            SessionBlock::TYPE_REPERTOIRE => 'Репертуар',
            SessionBlock::TYPE_IMPROVISATION => 'Импровизация',
            SessionBlock::TYPE_SIGHT_READING => 'Чтение с листа',
            SessionBlock::TYPE_THEORY => 'Теория',
            SessionBlock::TYPE_BREAK => 'Перерыв',
            SessionBlock::TYPE_CUSTOM => 'Пользовательский',
            default => 'Неизвестный тип',
        };
    }

    /**
     * Получить иконку для типа блока
     */
    public function getTypeIcon(): string
    {
        return match ($this->type) {
            SessionBlock::TYPE_WARMUP => '🔥',
            SessionBlock::TYPE_TECHNIQUE => '⚡',
            SessionBlock::TYPE_REPERTOIRE => '🎵',
            SessionBlock::TYPE_IMPROVISATION => '🎨',
            SessionBlock::TYPE_SIGHT_READING => '👀',
            SessionBlock::TYPE_THEORY => '📚',
            SessionBlock::TYPE_BREAK => '☕',
            SessionBlock::TYPE_CUSTOM => '⭐',
            default => '❓',
        };
    }

    /**
     * Получить цвет для типа блока
     */
    public function getTypeColor(): string
    {
        return match ($this->type) {
            SessionBlock::TYPE_WARMUP => 'orange',
            SessionBlock::TYPE_TECHNIQUE => 'yellow',
            SessionBlock::TYPE_REPERTOIRE => 'blue',
            SessionBlock::TYPE_IMPROVISATION => 'purple',
            SessionBlock::TYPE_SIGHT_READING => 'green',
            SessionBlock::TYPE_THEORY => 'gray',
            SessionBlock::TYPE_BREAK => 'slate',
            SessionBlock::TYPE_CUSTOM => 'pink',
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
     * Получить содержимое по умолчанию
     */
    public function getDefaultContent(string $key, mixed $default = null): mixed
    {
        return data_get($this->default_content, $key, $default);
    }

    /**
     * Установить содержимое по умолчанию
     */
    public function setDefaultContent(string $key, mixed $value): self
    {
        $defaultContent = $this->default_content ?? [];
        data_set($defaultContent, $key, $value);
        $this->default_content = $defaultContent;

        return $this;
    }

    /**
     * Создать блок сессии на основе этого блока шаблона
     */
    public function createSessionBlock(Session $session, array $overrides = []): SessionBlock
    {
        $blockData = array_merge([
            'practice_session_id'        => $session->id,
            'practice_template_block_id' => $this->id,
            'title'                      => $this->title,
            'description'                => $this->description,
            'type'                       => $this->type,
            'planned_duration'           => $this->duration,
            'status'                     => SessionBlock::STATUS_PLANNED,
            'sort_order'                 => $this->sort_order,
            'settings'                   => $this->settings,
        ], $overrides);

        return SessionBlock::create($blockData);
    }

    /**
     * Клонировать блок для другого шаблона
     */
    public function cloneForTemplate(Template $template, array $overrides = []): self
    {
        $blockData = array_merge([
            'practice_template_id' => $template->id,
            'title'                => $this->title,
            'description'          => $this->description,
            'type'                 => $this->type,
            'duration'             => $this->duration,
            'sort_order'           => $this->sort_order,
            'is_mandatory'         => $this->is_mandatory,
            'settings'             => $this->settings,
            'default_content'      => $this->default_content,
        ], $overrides);

        return static::create($blockData);
    }

    /**
     * Проверить, используется ли блок в сессиях
     */
    public function isUsedInSessions(): bool
    {
        return $this->sessionBlocks()->exists();
    }

    /**
     * Получить количество использований в сессиях
     */
    public function getUsageCount(): int
    {
        return $this->sessionBlocks()->count();
    }

    /**
     * Настройки логирования активности
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'type', 'duration', 'sort_order', 'is_mandatory'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
