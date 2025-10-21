/**
 * Утилиты для работы со статусами сессий и упражнений
 */

export type Status = 'planned' | 'active' | 'paused' | 'completed' | 'cancelled' | 'skipped';

/**
 * Маппинг статусов на текстовые метки
 */
const STATUS_LABELS: Record<Status, string> = {
    planned: 'Запланировано',
    active: 'Активно',
    paused: 'Приостановлено',
    completed: 'Завершено',
    cancelled: 'Отменено',
    skipped: 'Пропущено',
};

/**
 * Маппинг статусов на CSS классы для бейджей
 */
const STATUS_BADGE_CLASSES: Record<Status, string> = {
    planned: 'bg-amber-100 text-amber-800 dark:bg-gray-600 dark:text-gray-200',
    active: 'bg-orange-100 text-orange-800 dark:bg-green-900 dark:text-green-200',
    paused: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
    completed: 'bg-red-100 text-red-800 dark:bg-blue-900 dark:text-blue-200',
    cancelled: 'bg-red-200 text-red-800 dark:bg-red-900 dark:text-red-200',
    skipped: 'bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-200',
};

/**
 * Маппинг статусов блоков на CSS классы
 */
const BLOCK_STATUS_CLASSES: Record<Status, string> = {
    planned: 'border-amber-200 dark:border-gray-600 bg-amber-50/80 dark:bg-gray-800 hover:border-amber-300 dark:hover:border-gray-500',
    active: 'border-orange-200 dark:border-indigo-600 bg-gradient-to-r from-orange-50/80 to-red-50/80 dark:from-indigo-900 dark:to-purple-900 hover:border-orange-300 dark:hover:border-indigo-500',
    paused: 'border-yellow-200 dark:border-yellow-600 bg-gradient-to-r from-yellow-50/80 to-orange-50/80 dark:from-yellow-900 dark:to-orange-900 hover:border-yellow-300 dark:hover:border-yellow-500',
    completed: 'border-red-200 dark:border-green-600 bg-gradient-to-r from-red-50/80 to-orange-50/80 dark:from-green-900 dark:to-emerald-900 hover:border-red-300 dark:hover:border-green-500',
    cancelled: 'border-red-200 dark:border-red-600 bg-red-50/80 dark:bg-red-900/50 hover:border-red-300 dark:hover:border-red-500',
    skipped: 'border-amber-200 dark:border-gray-600 bg-amber-100/80 dark:bg-gray-800 hover:border-amber-300 dark:hover:border-gray-500',
};

/**
 * Маппинг статусов блоков на CSS классы для индикатора прогресса
 */
const BLOCK_PROGRESS_CLASSES: Record<Status, string> = {
    planned: 'bg-gray-300',
    active: 'bg-gradient-to-b from-indigo-400 to-purple-500',
    paused: 'bg-gradient-to-b from-yellow-400 to-orange-500',
    completed: 'bg-gradient-to-b from-green-400 to-emerald-500',
    cancelled: 'bg-red-400',
    skipped: 'bg-gray-400',
};

/**
 * Маппинг статусов блоков на CSS классы для фона иконки
 */
const BLOCK_ICON_BG_CLASSES: Record<Status, string> = {
    planned: 'bg-amber-100 dark:bg-gray-700 shadow-sm',
    active: 'bg-gradient-to-br from-orange-100 to-red-100 dark:from-indigo-800 dark:to-purple-800 shadow-sm',
    paused: 'bg-gradient-to-br from-yellow-100 to-orange-100 dark:from-yellow-800 dark:to-orange-800 shadow-sm',
    completed: 'bg-gradient-to-br from-red-100 to-orange-100 dark:from-green-800 dark:to-emerald-800 shadow-sm',
    cancelled: 'bg-red-100 dark:bg-red-800 shadow-sm',
    skipped: 'bg-amber-100 dark:bg-gray-700 shadow-sm',
};

/**
 * Маппинг статусов блоков на CSS классы для бейджа
 */
const BLOCK_BADGE_CLASSES: Record<Status, string> = {
    planned: 'bg-amber-100 dark:bg-gray-700 text-amber-700 dark:text-gray-200',
    active: 'bg-orange-100 dark:bg-indigo-900 text-orange-700 dark:text-indigo-200',
    paused: 'bg-yellow-100 dark:bg-yellow-900 text-yellow-700 dark:text-yellow-200',
    completed: 'bg-red-100 dark:bg-green-900 text-red-700 dark:text-green-200',
    cancelled: 'bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-200',
    skipped: 'bg-amber-100 dark:bg-gray-700 text-amber-500 dark:text-gray-400',
};

/**
 * Получить текстовую метку для статуса
 */
export function getStatusLabel(status: string): string {
    return STATUS_LABELS[status as Status] || status;
}

/**
 * Получить CSS классы для бейджа статуса
 */
export function getStatusBadgeClass(status: string): string {
    const baseClass = 'px-2 py-1 text-xs font-medium rounded-full';
    const statusClass = STATUS_BADGE_CLASSES[status as Status] || 'bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-200';
    return `${baseClass} ${statusClass}`;
}

/**
 * Получить CSS классы для блока в зависимости от статуса
 */
export function getBlockStatusClass(status: string): string {
    return BLOCK_STATUS_CLASSES[status as Status] || BLOCK_STATUS_CLASSES.planned;
}

/**
 * Получить CSS классы для индикатора прогресса блока
 */
export function getBlockProgressClass(status: string): string {
    return BLOCK_PROGRESS_CLASSES[status as Status] || BLOCK_PROGRESS_CLASSES.planned;
}

/**
 * Получить CSS классы для фона иконки блока
 */
export function getBlockIconBgClass(status: string): string {
    return BLOCK_ICON_BG_CLASSES[status as Status] || BLOCK_ICON_BG_CLASSES.planned;
}

/**
 * Получить CSS классы для бейджа блока
 */
export function getBlockBadgeClass(status: string): string {
    const baseClass = 'px-2 py-1 rounded-full text-xs font-medium shadow-sm';
    const statusClass = BLOCK_BADGE_CLASSES[status as Status] || BLOCK_BADGE_CLASSES.planned;
    return `${baseClass} ${statusClass}`;
}

/**
 * Получить все доступные статусы
 */
export function getAvailableStatuses(): Array<{ value: Status; label: string }> {
    return Object.keys(STATUS_LABELS).map((status) => ({
        value: status as Status,
        label: STATUS_LABELS[status as Status],
    }));
}
