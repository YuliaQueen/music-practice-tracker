<?php

declare(strict_types=1);

namespace App\Domains\Shared\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Базовая модель для всех доменных моделей
 *
 * Предоставляет общую функциональность и стандартизирует поведение
 */
abstract class BaseModel extends Model
{
    /**
     * Отключаем mass assignment protection по умолчанию
     * Каждая модель должна явно определить $fillable
     */
    protected $guarded = ['id'];

    /**
     * Формат дат по умолчанию
     */
    protected $dateFormat = 'Y-m-d H:i:s';

    /**
     * Дополнительные атрибуты для сериализации
     */
    protected $appends = [];

    /**
     * Получить полное имя класса без namespace
     */
    public function getModelName(): string
    {
        return class_basename(static::class);
    }

    /**
     * Получить значение атрибута с fallback значением
     */
    public function getAttributeOrDefault(string $attribute, mixed $default = null): mixed
    {
        return $this->getAttribute($attribute) ?? $default;
    }

    /**
     * Проверить, был ли атрибут изменен с конкретного значения
     */
    public function wasChangedFrom(string $attribute, mixed $value): bool
    {
        return $this->wasChanged($attribute) && $this->getOriginal($attribute) === $value;
    }

    /**
     * Проверить, был ли атрибут изменен на конкретное значение
     */
    public function wasChangedTo(string $attribute, mixed $value): bool
    {
        return $this->wasChanged($attribute) && $this->getAttribute($attribute) === $value;
    }
}
