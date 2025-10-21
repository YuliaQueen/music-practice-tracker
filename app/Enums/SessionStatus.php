<?php

declare(strict_types=1);

namespace App\Enums;

enum SessionStatus: string
{
    case PLANNED   = 'planned';
    case ACTIVE    = 'active';
    case PAUSED    = 'paused';
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
            self::PLANNED => 'Запланирована',
            self::ACTIVE => 'Активна',
            self::PAUSED => 'Приостановлена',
            self::COMPLETED => 'Завершена',
            self::CANCELLED => 'Отменена',
        };
    }
}
