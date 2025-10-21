<?php

declare(strict_types=1);

namespace App\Enums;

enum SessionBlockStatus: string
{
    case PLANNED = 'planned';
    case ACTIVE = 'active';
    case PAUSED = 'paused';
    case COMPLETED = 'completed';
    case SKIPPED = 'skipped';

    /**
     * Получить человекочитаемое название статуса
     */
    public function label(): string
    {
        return match ($this) {
            self::PLANNED => 'Запланирован',
            self::ACTIVE => 'Активен',
            self::PAUSED => 'Приостановлен',
            self::COMPLETED => 'Завершен',
            self::SKIPPED => 'Пропущен',
        };
    }

    /**
     * Получить все возможные значения в виде массива строк
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_map(fn(self $case) => $case->value, self::cases());
    }
}
