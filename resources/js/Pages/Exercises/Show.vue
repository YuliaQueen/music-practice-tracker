<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-neutral-800 dark:text-neutral-200 leading-tight">
                    {{ exercise.title }}
                </h2>
                <div class="flex space-x-2">
                    <PrimaryButton @click="router.visit(route('exercises.edit', exercise.id))">
                        Редактировать
                    </PrimaryButton>
                    <PrimaryButton @click="router.visit('/exercises')">
                        ← Назад к упражнениям
                    </PrimaryButton>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg dark:bg-neutral-800 dark:shadow-neutral-900/20">
                    <div class="p-6 text-neutral-900 dark:text-neutral-100">
                        <div class="space-y-6">
                            <!-- Название -->
                            <div>
                                <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100">Название</h3>
                                <p class="mt-1 text-neutral-600 dark:text-neutral-400">{{ exercise.title }}</p>
                            </div>

                            <!-- Описание -->
                            <div v-if="exercise.description">
                                <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100">Описание</h3>
                                <p class="mt-1 text-neutral-600 dark:text-neutral-400">{{ exercise.description }}</p>
                            </div>

                            <!-- Тип упражнения -->
                            <div>
                                <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100">Тип упражнения</h3>
                                <p class="mt-1 text-neutral-600 dark:text-neutral-400">{{ getTypeLabel(exercise.type) }}</p>
                            </div>

                            <!-- Длительность -->
                            <div>
                                <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100">Запланированная длительность</h3>
                                <p class="mt-1 text-neutral-600 dark:text-neutral-400">{{ exercise.planned_duration }} минут</p>
                            </div>

                            <!-- Запланированное время -->
                            <div v-if="exercise.scheduled_for">
                                <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100">Запланированное время</h3>
                                <p class="mt-1 text-neutral-600 dark:text-neutral-400">{{ formatDateTime(exercise.scheduled_for) }}</p>
                            </div>

                            <!-- Статус -->
                            <div>
                                <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100">Статус</h3>
                                <span class="mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="getStatusClass(exercise.status)">
                                    {{ getStatusLabel(exercise.status) }}
                                </span>
                            </div>
                        </div>

                        <!-- Кнопки действий -->
                        <div class="flex items-center justify-end mt-6 space-x-3">
                            <SecondaryButton @click="router.visit('/exercises')" type="button">
                                Назад к списку
                            </SecondaryButton>
                            <PrimaryButton @click="router.visit(route('exercises.edit', exercise.id))">
                                Редактировать
                            </PrimaryButton>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'

interface Props {
    exercise: {
        id: number
        title: string
        description: string | null
        type: string
        planned_duration: number
        scheduled_for: string | null
        status: string
    }
}

const props = defineProps<Props>()

const getTypeLabel = (type: string): string => {
    const types: Record<string, string> = {
        warmup: '🔥 Разминка',
        technique: '⚡ Техника',
        repertoire: '🎵 Репертуар',
        improvisation: '🎨 Импровизация',
        sight_reading: '👀 Чтение с листа',
        theory: '📚 Теория',
        break: '☕ Перерыв',
        custom: '⭐ Пользовательский',
    }
    return types[type] || type
}

const getStatusLabel = (status: string): string => {
    const statuses: Record<string, string> = {
        planned: 'Запланировано',
        active: 'Активно',
        completed: 'Завершено',
        cancelled: 'Отменено',
    }
    return statuses[status] || status
}

const getStatusClass = (status: string): string => {
    const classes: Record<string, string> = {
        planned: 'bg-accent-100 text-accent-800 dark:bg-accent-900 dark:text-accent-200',
        active: 'bg-success-100 text-success-800 dark:bg-success-900 dark:text-success-200',
        completed: 'bg-neutral-100 text-neutral-800 dark:bg-neutral-900 dark:text-neutral-200',
        cancelled: 'bg-danger-100 text-danger-800 dark:bg-danger-900 dark:text-danger-200',
    }
    return classes[status] || 'bg-neutral-100 text-neutral-800 dark:bg-neutral-900 dark:text-neutral-200'
}

const formatDateTime = (dateTime: string): string => {
    return new Date(dateTime).toLocaleString('ru-RU')
}
</script>