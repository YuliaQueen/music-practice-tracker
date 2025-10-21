<?php

declare(strict_types=1);

namespace App\Enums;

enum ExerciseType: string
{
    case WARMUP    = 'warmup';
    case TECHNIQUE = 'technique';
    case REPERTOIRE = 'repertoire';
    case IMPROVISATION = 'improvisation';
    case SIGHT_READING = 'sight_reading';
    case THEORY    = 'theory';
    case BREAK     = 'break';
    case CUSTOM    = 'custom';

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
     * Получить человекочитаемое название типа
     */
    public function label(): string
    {
        return match ($this) {
            self::WARMUP => 'Разминка',
            self::TECHNIQUE => 'Техника',
            self::REPERTOIRE => 'Репертуар',
            self::IMPROVISATION => 'Импровизация',
            self::SIGHT_READING => 'Чтение с листа',
            self::THEORY => 'Теория',
            self::BREAK => 'Перерыв',
            self::CUSTOM => 'Пользовательский',
        };
    }

    /**
     * Получить иконку для типа
     */
    public function icon(): string
    {
        return match ($this) {
            self::WARMUP => '🔥',
            self::TECHNIQUE => '⚡',
            self::REPERTOIRE => '🎵',
            self::IMPROVISATION => '🎨',
            self::SIGHT_READING => '👀',
            self::THEORY => '📚',
            self::BREAK => '☕',
            self::CUSTOM => '⭐',
        };
    }

    /**
     * Получить цвет для типа
     */
    public function color(): string
    {
        return match ($this) {
            self::WARMUP => 'orange',
            self::TECHNIQUE => 'yellow',
            self::REPERTOIRE => 'blue',
            self::IMPROVISATION => 'purple',
            self::SIGHT_READING => 'green',
            self::THEORY => 'gray',
            self::BREAK => 'slate',
            self::CUSTOM => 'pink',
        };
    }
}
