<?php

declare(strict_types=1);

namespace App\Enums;

enum ExerciseStatus: string
{
    case PLANNED = 'planned';
    case ACTIVE = 'active';
    case PAUSED = 'paused';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    /**
     * Получить все возможные значения в виде массива строк
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_map(fn(self $case) => $case->value, self::cases());
    }

    /**
     * Получить человекочитаемое название статуса
     */
    public function label(): string
    {
        return match ($this) {
            self::PLANNED => 'Запланировано',
            self::ACTIVE => 'Активно',
            self::PAUSED => 'Приостановлено',
            self::COMPLETED => 'Завершено',
            self::CANCELLED => 'Отменено',
        };
    }
}
