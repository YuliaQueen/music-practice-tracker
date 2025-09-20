<?php

declare(strict_types=1);

namespace App\Domains\Shared\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * Базовая модель с общим функционалом для всех доменных сущностей
 */
abstract class BaseModel extends Model
{
    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = true;

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot the model
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model) {
            if (empty($model->{$model->getKeyName()}) && $model->getKeyType() === 'string') {
                $model->{$model->getKeyName()} = Str::uuid()->toString();
            }
        });
    }

    /**
     * Scope a query to only include records created today
     */
    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope a query to only include records from current week
     */
    public function scopeThisWeek(Builder $query): Builder
    {
        return $query->whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek(),
        ]);
    }

    /**
     * Scope a query to only include records from current month
     */
    public function scopeThisMonth(Builder $query): Builder
    {
        return $query->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
    }

    /**
     * Scope a query to only include records between dates
     */
    public function scopeBetweenDates(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('created_at', [
            $startDate,
            $endDate,
        ]);
    }

    /**
     * Get the model's display name for UI
     */
    public function getDisplayName(): string
    {
        if (method_exists($this, 'name')) {
            return $this->name;
        }

        if (method_exists($this, 'title')) {
            return $this->title;
        }

        return class_basename($this) . ' #' . $this->getKey();
    }

    /**
     * Get formatted created_at date for display
     */
    public function getFormattedCreatedAtAttribute(): string
    {
        return $this->created_at->format('d.m.Y H:i');
    }

    /**
     * Get formatted updated_at date for display
     */
    public function getFormattedUpdatedAtAttribute(): string
    {
        return $this->updated_at->format('d.m.Y H:i');
    }

    /**
     * Check if model was created today
     */
    public function isCreatedToday(): bool
    {
        return $this->created_at->isToday();
    }

    /**
     * Check if model was recently updated (within last hour)
     */
    public function isRecentlyUpdated(): bool
    {
        return $this->updated_at->diffInMinutes(now()) < 60;
    }

    /**
     * Get the model's searchable fields for full-text search
     */
    public function getSearchableFields(): array
    {
        return [];
    }

    /**
     * Scope for full-text search across searchable fields
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        if (empty($search)) {
            return $query;
        }

        $searchableFields = $this->getSearchableFields();

        if (empty($searchableFields)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($search, $searchableFields) {
            foreach ($searchableFields as $field) {
                $q->orWhere($field, 'ILIKE', "%{$search}%");
            }
        });
    }
}
