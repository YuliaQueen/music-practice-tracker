<template>
    <div class="bg-neutral-50 dark:bg-neutral-800 rounded-md p-4 space-y-2">
        <h4 class="text-sm font-semibold text-neutral-700 dark:text-neutral-300 mb-3">
            Статистика изучения
        </h4>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="text-center">
                <div class="text-2xl font-bold text-accent-600 dark:text-accent-400">
                    {{ exercise.sessions_count ?? 0 }}
                </div>
                <div class="text-xs text-neutral-600 dark:text-neutral-400">
                    Сессий
                </div>
            </div>

            <div class="text-center">
                <div class="text-2xl font-bold text-accent-600 dark:text-accent-400">
                    {{ formatDuration(exercise.total_practice_time ?? 0) }}
                </div>
                <div class="text-xs text-neutral-600 dark:text-neutral-400">
                    Всего практики
                </div>
            </div>

            <div class="text-center">
                <div class="text-2xl font-bold text-accent-600 dark:text-accent-400">
                    {{ formatDuration(exercise.average_practice_time ?? 0) }}
                </div>
                <div class="text-xs text-neutral-600 dark:text-neutral-400">
                    Средняя сессия
                </div>
            </div>

            <div class="text-center">
                <div class="text-2xl font-bold text-accent-600 dark:text-accent-400">
                    {{ exercise.learning_days ?? '-' }}
                </div>
                <div class="text-xs text-neutral-600 dark:text-neutral-400">
                    Дней изучения
                </div>
            </div>
        </div>

        <div v-if="exercise.started_learning_at && exercise.completed_learning_at" class="mt-4 pt-3 border-t border-neutral-200 dark:border-neutral-700">
            <div class="flex justify-between text-xs text-neutral-600 dark:text-neutral-400">
                <span>
                    Начало: {{ formatDate(exercise.started_learning_at) }}
                </span>
                <span>
                    Завершено: {{ formatDate(exercise.completed_learning_at) }}
                </span>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import type { Exercise } from '@/types/models'

interface Props {
    exercise: Exercise
}

defineProps<Props>()

const formatDuration = (minutes: number): string => {
    if (!minutes) return '0 мин'

    const hours = Math.floor(minutes / 60)
    const mins = Math.round(minutes % 60)

    if (hours > 0) {
        return mins > 0 ? `${hours}ч ${mins}м` : `${hours}ч`
    }

    return `${mins}м`
}

const formatDate = (dateString: string): string => {
    const date = new Date(dateString)
    return date.toLocaleDateString('ru-RU', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    })
}
</script>
