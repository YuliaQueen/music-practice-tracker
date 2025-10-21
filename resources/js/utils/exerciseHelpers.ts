/**
 * Утилиты для работы с типами упражнений
 */

export type ExerciseType =
    | 'warmup'
    | 'technique'
    | 'repertoire'
    | 'improvisation'
    | 'sight_reading'
    | 'theory'
    | 'break'
    | 'custom';

/**
 * Маппинг типов упражнений на иконки
 */
const TYPE_ICONS: Record<ExerciseType, string> = {
    warmup: '🔥',
    technique: '⚡',
    repertoire: '🎵',
    improvisation: '🎨',
    sight_reading: '👀',
    theory: '📚',
    break: '☕',
    custom: '⭐',
};

/**
 * Маппинг типов упражнений на текстовые метки
 */
const TYPE_LABELS: Record<ExerciseType, string> = {
    warmup: 'Разминка',
    technique: 'Техника',
    repertoire: 'Репертуар',
    improvisation: 'Импровизация',
    sight_reading: 'Чтение с листа',
    theory: 'Теория',
    break: 'Перерыв',
    custom: 'Пользовательский',
};

/**
 * Получить иконку для типа упражнения
 */
export function getTypeIcon(type: string): string {
    return TYPE_ICONS[type as ExerciseType] || '❓';
}

/**
 * Получить текстовую метку для типа упражнения
 */
export function getTypeLabel(type: string): string {
    return TYPE_LABELS[type as ExerciseType] || type;
}

/**
 * Получить все доступные типы упражнений
 */
export function getExerciseTypes(): Array<{ value: ExerciseType; label: string; icon: string }> {
    return Object.keys(TYPE_ICONS).map((type) => ({
        value: type as ExerciseType,
        label: TYPE_LABELS[type as ExerciseType],
        icon: TYPE_ICONS[type as ExerciseType],
    }));
}
