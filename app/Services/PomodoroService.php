<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Сервис для работы с Pomodoro-сессиями
 */
class PomodoroService
{
    /**
     * Минимальная длительность для любого слота (минуты)
     */
    private const MIN_SLOT_DURATION = 1;

    /**
     * Типы слотов Pomodoro
     */
    private const SLOT_TYPE_WORK  = 'custom';
    private const SLOT_TYPE_BREAK = 'break';

    /**
     * Префиксы для описаний
     */
    private const DESCRIPTION_PREFIX  = 'Pomodoro: ';
    private const WORK_TITLE_TEMPLATE = 'Работа (цикл %d)';
    private const SHORT_BREAK_TITLE   = 'Короткий перерыв';
    private const LONG_BREAK_TITLE    = 'Длинный перерыв';

    /**
     * Рассчитать слоты Pomodoro для заданной общей длительности
     *
     * @param int $totalMinutes          Общая длительность сессии в минутах
     * @param int $workDuration          Длительность рабочего слота в минутах
     * @param int $shortBreak            Длительность короткого перерыва в минутах
     * @param int $longBreak             Длительность длинного перерыва в минутах
     * @param int $cyclesBeforeLongBreak Количество циклов до длинного перерыва
     * @return array{success: bool, slots?: array, totalCycles?: int, message: string}
     */
    public function calculatePomodoroSlots(
        int $totalMinutes,
        int $workDuration = 25,
        int $shortBreak = 5,
        int $longBreak = 15,
        int $cyclesBeforeLongBreak = 4
    ): array {
        try {
            $validation = $this->validatePomodoroSettings(
                $totalMinutes,
                $workDuration,
                $shortBreak,
                $longBreak,
                $cyclesBeforeLongBreak
            );

            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => implode(', ', $validation['errors']),
                ];
            }

            $slots           = [];
            $remainingTime   = $totalMinutes;
            $cycleNumber     = 0;
            $completedCycles = 0;

            while ($remainingTime > 0) {
                $cycleNumber++;

                // Добавляем рабочий слот
                $workSlotDuration = min($workDuration, $remainingTime);
                $slots[]          = $this->createWorkSlot($cycleNumber, $workSlotDuration);
                $remainingTime    -= $workSlotDuration;
                $completedCycles++;

                // Если время закончилось, не добавляем перерыв
                if ($remainingTime <= 0) {
                    break;
                }

                // Добавляем перерыв
                $isLongBreak   = ($cycleNumber % $cyclesBeforeLongBreak === 0);
                $breakDuration = min($isLongBreak ? $longBreak : $shortBreak, $remainingTime);

                if ($breakDuration > 0) {
                    $slots[]       = $this->createBreakSlot($cycleNumber, $breakDuration, $isLongBreak);
                    $remainingTime -= $breakDuration;
                }
            }

            return [
                'success'     => true,
                'slots'       => $slots,
                'totalCycles' => $completedCycles,
                'message'     => "Создано {$completedCycles} рабочих циклов Pomodoro",
            ];
        } catch (\Throwable $e) {
            Log::error('Ошибка при расчете Pomodoro слотов', [
                'total_minutes' => $totalMinutes,
                'error'         => $e->getMessage(),
                'trace'         => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Ошибка при расчете слотов Pomodoro',
            ];
        }
    }

    /**
     * Получить превью расписания Pomodoro в виде строки
     *
     * @param array $slots Массив слотов
     * @return string
     */
    public function generateSchedulePreview(array $slots): string
    {
        $preview = [];

        foreach ($slots as $slot) {
            $icon = match ($slot['type']) {
                'work' => '🔥',
                'short_break' => '☕',
                'long_break' => '🌟',
                default => '⏱️',
            };

            $preview[] = "{$icon} {$slot['title']} ({$slot['duration']} мин)";
        }

        return implode(' → ', $preview);
    }

    /**
     * Валидировать параметры Pomodoro (только базовые проверки)
     *
     * @param int $totalMinutes
     * @param int $workDuration
     * @param int $shortBreak
     * @param int $longBreak
     * @param int $cyclesBeforeLongBreak
     * @return array{valid: bool, errors: array}
     */
    public function validatePomodoroSettings(
        int $totalMinutes,
        int $workDuration,
        int $shortBreak,
        int $longBreak,
        int $cyclesBeforeLongBreak
    ): array {
        $errors = [];

        if ($totalMinutes < self::MIN_SLOT_DURATION) {
            $errors[] = 'Общая длительность должна быть не менее ' . self::MIN_SLOT_DURATION . ' минуты';
        }

        if ($workDuration < self::MIN_SLOT_DURATION) {
            $errors[] = 'Длительность работы должна быть не менее ' . self::MIN_SLOT_DURATION . ' минуты';
        }

        if ($shortBreak < 0) {
            $errors[] = 'Длительность короткого перерыва не может быть отрицательной';
        }

        if ($longBreak < 0) {
            $errors[] = 'Длительность длинного перерыва не может быть отрицательной';
        }

        if ($cyclesBeforeLongBreak < self::MIN_SLOT_DURATION) {
            $errors[] = 'Количество циклов до длинного перерыва должно быть не менее ' . self::MIN_SLOT_DURATION;
        }

        if ($totalMinutes < $workDuration) {
            $errors[] = 'Общая длительность должна быть не менее длительности одного рабочего слота';
        }

        return [
            'valid'  => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Создать рабочий слот
     *
     * @param int $cycleNumber
     * @param int $duration
     * @return array
     */
    private function createWorkSlot(int $cycleNumber, int $duration): array
    {
        return [
            'title'        => sprintf(self::WORK_TITLE_TEMPLATE, $cycleNumber),
            'type'         => self::SLOT_TYPE_WORK,
            'duration'     => $duration,
            'cycle_number' => $cycleNumber,
            'description'  => self::DESCRIPTION_PREFIX . "фокусированная работа {$duration} минут",
        ];
    }

    /**
     * Создать слот перерыва
     *
     * @param int  $cycleNumber
     * @param int  $duration
     * @param bool $isLongBreak
     * @return array
     */
    private function createBreakSlot(int $cycleNumber, int $duration, bool $isLongBreak): array
    {
        $title = $isLongBreak ? self::LONG_BREAK_TITLE : self::SHORT_BREAK_TITLE;
        $type  = $isLongBreak ? 'длинный перерыв' : 'короткий перерыв';

        return [
            'title'        => $title,
            'type'         => self::SLOT_TYPE_BREAK,
            'duration'     => $duration,
            'cycle_number' => $cycleNumber,
            'description'  => self::DESCRIPTION_PREFIX . "{$type} {$duration} минут",
        ];
    }
}
