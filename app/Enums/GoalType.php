<?php

declare(strict_types=1);

namespace App\Enums;

enum GoalType: string
{
    case DAILY_MINUTES = 'daily_minutes';
    case WEEKLY_SESSIONS = 'weekly_sessions';
    case STREAK_DAYS = 'streak_days';
    case EXERCISE_TYPE = 'exercise_type';
    case MONTHLY_MINUTES = 'monthly_minutes';
    case YEARLY_SESSIONS = 'yearly_sessions';

    /**
     * Получить человекочитаемое название типа
     */
    public function label(): string
    {
        return match ($this) {
            self::DAILY_MINUTES => 'Ежедневные минуты',
            self::WEEKLY_SESSIONS => 'Еженедельные сессии',
            self::STREAK_DAYS => 'Серия дней',
            self::EXERCISE_TYPE => 'Тип упражнений',
            self::MONTHLY_MINUTES => 'Ежемесячные минуты',
            self::YEARLY_SESSIONS => 'Годовые сессии',
        };
    }

    /**
     * Получить иконку для типа цели
     */
    public function icon(): string
    {
        return match ($this) {
            self::DAILY_MINUTES => '📅',
            self::WEEKLY_SESSIONS => '📊',
            self::STREAK_DAYS => '🔥',
            self::EXERCISE_TYPE => '🎵',
            self::MONTHLY_MINUTES => '📈',
            self::YEARLY_SESSIONS => '🎯',
        };
    }

    /**
     * Получить цвет для типа цели
     */
    public function color(): string
    {
        return match ($this) {
            self::DAILY_MINUTES => 'blue',
            self::WEEKLY_SESSIONS => 'green',
            self::STREAK_DAYS => 'orange',
            self::EXERCISE_TYPE => 'purple',
            self::MONTHLY_MINUTES => 'indigo',
            self::YEARLY_SESSIONS => 'red',
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
