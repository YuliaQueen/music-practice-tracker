/**
 * Утилиты для работы со временем
 */

/**
 * Форматировать секунды в формат MM:SS
 */
export function formatTime(seconds: number): string {
    const minutes = Math.floor(seconds / 60);
    const remainingSeconds = seconds % 60;
    return `${minutes.toString().padStart(2, '0')}:${remainingSeconds.toString().padStart(2, '0')}`;
}

/**
 * Форматировать минуты в человекочитаемый формат
 */
export function formatMinutes(minutes: number): string {
    if (minutes < 60) {
        return `${minutes} мин`;
    }

    const hours = Math.floor(minutes / 60);
    const remainingMinutes = minutes % 60;

    if (remainingMinutes === 0) {
        return `${hours} ч`;
    }

    return `${hours} ч ${remainingMinutes} мин`;
}

/**
 * Конвертировать минуты в секунды
 */
export function minutesToSeconds(minutes: number): number {
    return minutes * 60;
}

/**
 * Конвертировать секунды в минуты
 */
export function secondsToMinutes(seconds: number): number {
    return Math.floor(seconds / 60);
}

/**
 * Получить процент выполнения
 */
export function getProgressPercentage(current: number, total: number): number {
    if (total <= 0) {
        return 0;
    }
    return Math.min(100, Math.round((current / total) * 100));
}
