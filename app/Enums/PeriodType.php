<?php

declare(strict_types=1);

namespace App\Enums;

use Carbon\Carbon;

enum PeriodType: string
{
    case DAY   = 'day';
    case WEEK  = 'week';
    case MONTH = 'month';
    case YEAR  = 'year';

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
     * Получить человекочитаемое название периода
     */
    public function label(): string
    {
        return match ($this) {
            self::DAY   => 'День',
            self::WEEK  => 'Неделя',
            self::MONTH => 'Месяц',
            self::YEAR  => 'Год',
        };
    }

    /**
     * Получить начало и конец периода
     *
     * @return array{start: Carbon, end: Carbon}
     */
    public function getDateRange(): array
    {
        return match ($this) {
            self::DAY => [
                'start' => Carbon::today(),
                'end'   => Carbon::today()->endOfDay(),
            ],
            self::WEEK => [
                'start' => Carbon::now()->startOfWeek(),
                'end'   => Carbon::now()->endOfWeek(),
            ],
            self::MONTH => [
                'start' => Carbon::now()->startOfMonth(),
                'end'   => Carbon::now()->endOfMonth(),
            ],
            self::YEAR => [
                'start' => Carbon::now()->startOfYear(),
                'end'   => Carbon::now()->endOfYear(),
            ],
        };
    }

    /**
     * Получить период из строки или вернуть по умолчанию
     */
    public static function fromString(string $value, self $default = self::WEEK): self
    {
        return self::tryFrom($value) ?? $default;
    }
}
