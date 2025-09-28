<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
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
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg dark:bg-gray-800 dark:shadow-gray-900/20">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="space-y-6">
                            <!-- Название -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Название</h3>
                                <p class="mt-1 text-gray-600 dark:text-gray-400">{{ exercise.title }}</p>
                            </div>

                            <!-- Описание -->
                            <div v-if="exercise.description">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Описание</h3>
                                <p class="mt-1 text-gray-600 dark:text-gray-400">{{ exercise.description }}</p>
                            </div>

                            <!-- Тип упражнения -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Тип упражнения</h3>
                                <p class="mt-1 text-gray-600 dark:text-gray-400">{{ getTypeLabel(exercise.type) }}</p>
                            </div>

                            <!-- Длительность -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Запланированная длительность</h3>
                                <p class="mt-1 text-gray-600 dark:text-gray-400">{{ exercise.planned_duration }} минут</p>
                            </div>

                            <!-- Запланированное время -->
                            <div v-if="exercise.scheduled_for">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Запланированное время</h3>
                                <p class="mt-1 text-gray-600 dark:text-gray-400">{{ formatDateTime(exercise.scheduled_for) }}</p>
                            </div>

                            <!-- Статус -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Статус</h3>
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
        planned: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        active: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        completed: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
        cancelled: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
    }
    return classes[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200'
}

const formatDateTime = (dateTime: string): string => {
    return new Date(dateTime).toLocaleString('ru-RU')
}
</script>