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
    planned: 'bg-neutral-100 text-neutral-800 dark:bg-neutral-700 dark:text-neutral-300',
    active: 'bg-success-100 text-success-800 dark:bg-success-900/40 dark:text-success-300',
    paused: 'bg-warning-100 text-warning-800 dark:bg-warning-900/40 dark:text-warning-300',
    completed: 'bg-accent-100 text-accent-800 dark:bg-accent-900/40 dark:text-accent-300',
    cancelled: 'bg-danger-100 text-danger-800 dark:bg-danger-900/40 dark:text-danger-300',
    skipped: 'bg-neutral-100 text-neutral-600 dark:bg-neutral-700 dark:text-neutral-400',
};

/**
 * Маппинг статусов блоков на CSS классы
 */
const BLOCK_STATUS_CLASSES: Record<Status, string> = {
    planned: 'border-neutral-200 dark:border-neutral-600 bg-neutral-50/80 dark:bg-neutral-800 hover:border-neutral-300 dark:hover:border-neutral-500',
    active: 'border-success-200 dark:border-success-600 bg-gradient-to-r from-success-50/80 to-accent-50/80 dark:from-success-900 dark:to-accent-900 hover:border-success-300 dark:hover:border-success-500',
    paused: 'border-warning-200 dark:border-warning-600 bg-gradient-to-r from-warning-50/80 to-primary-50/80 dark:from-warning-900 dark:to-primary-900 hover:border-warning-300 dark:hover:border-warning-500',
    completed: 'border-accent-200 dark:border-accent-600 bg-gradient-to-r from-accent-50/80 to-primary-50/80 dark:from-accent-900 dark:to-success-900 hover:border-accent-300 dark:hover:border-accent-500',
    cancelled: 'border-danger-200 dark:border-danger-600 bg-danger-50/80 dark:bg-danger-900/50 hover:border-danger-300 dark:hover:border-danger-500',
    skipped: 'border-neutral-200 dark:border-neutral-600 bg-neutral-100/80 dark:bg-neutral-800 hover:border-neutral-300 dark:hover:border-neutral-500',
};

/**
 * Маппинг статусов блоков на CSS классы для индикатора прогресса
 */
const BLOCK_PROGRESS_CLASSES: Record<Status, string> = {
    planned: 'bg-neutral-300',
    active: 'bg-gradient-to-b from-success-400 to-accent-500',
    paused: 'bg-gradient-to-b from-warning-400 to-primary-500',
    completed: 'bg-gradient-to-b from-accent-400 to-success-500',
    cancelled: 'bg-danger-400',
    skipped: 'bg-neutral-400',
};

/**
 * Маппинг статусов блоков на CSS классы для фона иконки
 */
const BLOCK_ICON_BG_CLASSES: Record<Status, string> = {
    planned: 'bg-neutral-100 dark:bg-neutral-700 shadow-sm',
    active: 'bg-gradient-to-br from-success-100 to-accent-100 dark:from-success-800 dark:to-accent-800 shadow-sm',
    paused: 'bg-gradient-to-br from-warning-100 to-primary-100 dark:from-warning-800 dark:to-primary-800 shadow-sm',
    completed: 'bg-gradient-to-br from-accent-100 to-primary-100 dark:from-accent-800 dark:to-success-800 shadow-sm',
    cancelled: 'bg-danger-100 dark:bg-danger-800 shadow-sm',
    skipped: 'bg-neutral-100 dark:bg-neutral-700 shadow-sm',
};

/**
 * Маппинг статусов блоков на CSS классы для бейджа
 */
const BLOCK_BADGE_CLASSES: Record<Status, string> = {
    planned: 'bg-neutral-100 dark:bg-neutral-700 text-neutral-700 dark:text-neutral-200',
    active: 'bg-success-100 dark:bg-success-900 text-success-700 dark:text-success-200',
    paused: 'bg-warning-100 dark:bg-warning-900 text-warning-700 dark:text-warning-200',
    completed: 'bg-accent-100 dark:bg-accent-900 text-accent-700 dark:text-accent-200',
    cancelled: 'bg-danger-100 dark:bg-danger-900 text-danger-700 dark:text-danger-200',
    skipped: 'bg-neutral-100 dark:bg-neutral-700 text-neutral-500 dark:text-neutral-400',
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
    const statusClass = STATUS_BADGE_CLASSES[status as Status] || 'bg-neutral-100 text-neutral-800 dark:bg-neutral-600 dark:text-neutral-200';
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
