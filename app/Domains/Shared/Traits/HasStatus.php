<?php

declare(strict_types=1);

namespace App\Domains\Shared\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasStatus
{
    /**
     * Scope: активные записи
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', $this->getActiveStatus());
    }

    /**
     * Scope: завершенные записи
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', $this->getCompletedStatus());
    }

    /**
     * Scope: запланированные записи
     */
    public function scopePlanned(Builder $query): Builder
    {
        return $query->where('status', $this->getPlannedStatus());
    }

    /**
     * Проверить, является ли запись активной
     */
    public function isActive(): bool
    {
        return $this->status->value === $this->getActiveStatus();
    }

    /**
     * Проверить, завершена ли запись
     */
    public function isCompleted(): bool
    {
        return $this->status->value === $this->getCompletedStatus();
    }

    /**
     * Проверить, запланирована ли запись
     */
    public function isPlanned(): bool
    {
        return $this->status->value === $this->getPlannedStatus();
    }

    /**
     * Получить значение активного статуса
     * Должен быть переопределен в модели
     */
    abstract protected function getActiveStatus(): string;

    /**
     * Получить значение завершенного статуса
     * Должен быть переопределен в модели
     */
    abstract protected function getCompletedStatus(): string;

    /**
     * Получить значение запланированного статуса
     * Должен быть переопределен в модели
     */
    abstract protected function getPlannedStatus(): string;
}
