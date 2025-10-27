import { ref, computed } from 'vue'

/**
 * Интерфейс для настроек Pomodoro
 */
export interface PomodoroSettings {
    totalCycles: number // Количество рабочих циклов
    workDuration: number
    shortBreak: number
    longBreak: number
    cyclesBeforeLongBreak: number
}

/**
 * Интерфейс для слота Pomodoro
 */
export interface PomodoroSlot {
    title: string
    type: 'custom' | 'break'
    duration: number
    cycleNumber: number
    description: string
}

/**
 * Composable для работы с Pomodoro-режимом
 */
export function usePomodoro() {
    const settings = ref<PomodoroSettings>({
        totalCycles: 4,
        workDuration: 25,
        shortBreak: 5,
        longBreak: 15,
        cyclesBeforeLongBreak: 4,
    })

    /**
     * Рассчитать слоты Pomodoro на основе количества циклов
     */
    const calculateSlots = (customSettings?: Partial<PomodoroSettings>): PomodoroSlot[] => {
        const config = { ...settings.value, ...customSettings }
        const slots: PomodoroSlot[] = []

        for (let cycleNumber = 1; cycleNumber <= config.totalCycles; cycleNumber++) {
            // Рабочий слот
            slots.push({
                title: `Работа (цикл ${cycleNumber})`,
                type: 'custom',
                duration: config.workDuration,
                cycleNumber,
                description: `Pomodoro: фокусированная работа ${config.workDuration} минут`,
            })

            // Перерыв после цикла (кроме последнего цикла)
            if (cycleNumber < config.totalCycles) {
                if (cycleNumber % config.cyclesBeforeLongBreak === 0) {
                    // Длинный перерыв
                    slots.push({
                        title: 'Длинный перерыв',
                        type: 'break',
                        duration: config.longBreak,
                        cycleNumber,
                        description: `Pomodoro: длинный перерыв ${config.longBreak} минут`,
                    })
                } else {
                    // Короткий перерыв
                    slots.push({
                        title: 'Короткий перерыв',
                        type: 'break',
                        duration: config.shortBreak,
                        cycleNumber,
                        description: `Pomodoro: короткий перерыв ${config.shortBreak} минут`,
                    })
                }
            }
        }

        return slots
    }

    /**
     * Рассчитать общее время сессии в минутах
     */
    const calculateTotalMinutes = (customSettings?: Partial<PomodoroSettings>): number => {
        const config = { ...settings.value, ...customSettings }
        let totalMinutes = 0

        // Время работы
        totalMinutes += config.totalCycles * config.workDuration

        // Время перерывов
        const totalBreaks = config.totalCycles - 1 // Перерывов на 1 меньше чем циклов
        if (totalBreaks > 0) {
            const longBreaksCount = Math.floor(config.totalCycles / config.cyclesBeforeLongBreak)
            const shortBreaksCount = totalBreaks - longBreaksCount

            totalMinutes += longBreaksCount * config.longBreak
            totalMinutes += shortBreaksCount * config.shortBreak
        }

        return totalMinutes
    }

    /**
     * Получить превью расписания
     */
    const getSchedulePreview = (slots: PomodoroSlot[]): string => {
        return slots
            .map(slot => {
                const icon = slot.type === 'custom' ? '🔥' : '☕'
                return `${icon} ${slot.title} (${slot.duration} мин)`
            })
            .join(' → ')
    }

    /**
     * Получить короткое превью (первые 3 слота)
     */
    const getShortPreview = (slots: PomodoroSlot[]): string => {
        const preview = slots.slice(0, 3).map(slot => {
            const icon = slot.type === 'custom' ? '🔥' : '☕'
            return `${icon} ${slot.duration}м`
        })

        if (slots.length > 3) {
            preview.push('...')
        }

        return preview.join(' → ')
    }

    /**
     * Подсчитать количество рабочих циклов
     */
    const calculateTotalCycles = (slots: PomodoroSlot[]): number => {
        return slots.filter(slot => slot.type === 'custom').length
    }

    /**
     * Валидировать настройки
     */
    const validateSettings = (config: PomodoroSettings): { valid: boolean; errors: string[] } => {
        const errors: string[] = []

        if (config.totalCycles < 1) {
            errors.push('Количество циклов должно быть не менее 1')
        }

        if (config.workDuration < 1) {
            errors.push('Длительность работы должна быть не менее 1 минуты')
        }

        if (config.shortBreak < 0) {
            errors.push('Длительность короткого перерыва не может быть отрицательной')
        }

        if (config.longBreak < 0) {
            errors.push('Длительность длинного перерыва не может быть отрицательной')
        }

        if (config.cyclesBeforeLongBreak < 1) {
            errors.push('Количество циклов до длинного перерыва должно быть не менее 1')
        }

        return {
            valid: errors.length === 0,
            errors,
        }
    }

    /**
     * Получить иконку для типа слота
     */
    const getSlotIcon = (type: 'custom' | 'break'): string => {
        switch (type) {
            case 'custom':
                return '🔥'
            case 'break':
                return '☕'
            default:
                return '⏱️'
        }
    }

    /**
     * Получить цвет для типа слота (Tailwind классы)
     */
    const getSlotColorClass = (type: 'custom' | 'break'): string => {
        switch (type) {
            case 'custom':
                return 'bg-danger-100 text-danger-600 border-danger-200 dark:bg-danger-900/20 dark:text-danger-300 dark:border-danger-800'
            case 'break':
                return 'bg-primary-100 text-primary-600 border-primary-200 dark:bg-primary-900/20 dark:text-primary-300 dark:border-primary-800'
            default:
                return 'bg-neutral-100 text-neutral-600 border-neutral-200 dark:bg-neutral-700 dark:text-neutral-300 dark:border-neutral-600'
        }
    }

    /**
     * Вычисляемое свойство для слотов
     */
    const slots = computed(() => calculateSlots())

    /**
     * Вычисляемое свойство для общего количества циклов
     */
    const totalCycles = computed(() => calculateTotalCycles(slots.value))

    /**
     * Вычисляемое свойство для превью
     */
    const schedulePreview = computed(() => getSchedulePreview(slots.value))

    /**
     * Вычисляемое свойство для короткого превью
     */
    const shortPreview = computed(() => getShortPreview(slots.value))

    return {
        settings,
        slots,
        totalCycles,
        schedulePreview,
        shortPreview,
        calculateSlots,
        calculateTotalMinutes,
        getSchedulePreview,
        getShortPreview,
        calculateTotalCycles,
        validateSettings,
        getSlotIcon,
        getSlotColorClass,
    }
}