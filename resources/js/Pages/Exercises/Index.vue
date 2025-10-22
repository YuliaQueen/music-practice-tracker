<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-primary-800 dark:text-neutral-200 leading-tight">
                    Мои упражнения
                </h2>
                <PrimaryButton @click="router.visit('/exercises/create')">
                    + Создать упражнение
                </PrimaryButton>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-primary-50 overflow-hidden shadow-sm sm:rounded-lg dark:bg-neutral-800 dark:shadow-neutral-900/20">
                    <div class="p-6 text-primary-900 dark:text-neutral-100">
                        <div v-if="exercises.data.length === 0" class="text-center py-12">
                            <div class="text-primary-500 mb-4">
                                <svg class="mx-auto h-12 w-12 text-primary-400 dark:text-neutral-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <h4 class="text-lg font-medium text-primary-900 dark:text-neutral-100 mb-2">Нет упражнений</h4>
                            <p class="text-primary-600 dark:text-neutral-400 mb-4">Создайте свое первое упражнение для быстрой практики</p>
                            <PrimaryButton @click="router.visit('/exercises/create')">
                                Создать упражнение
                            </PrimaryButton>
                        </div>

                        <div v-else class="space-y-4">
                            <div v-for="exercise in exercises.data" :key="exercise.id" class="border border-primary-200 dark:border-neutral-700 rounded-lg p-4 hover:bg-primary-50 dark:hover:bg-neutral-700 transition-colors">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <span class="text-3xl">{{ getTypeIcon(exercise.type) }}</span>
                                        <div>
                                            <h3 class="text-lg font-medium text-primary-900 dark:text-neutral-100">{{ exercise.title }}</h3>
                                            <p v-if="exercise.description" class="text-sm text-primary-700 dark:text-neutral-300 mt-1">{{ exercise.description }}</p>
                                            <div class="flex items-center space-x-3 mt-2">
                                                <span class="text-sm text-primary-600 dark:text-neutral-400">{{ exercise.type_label }}</span>
                                                <span class="text-sm text-primary-600 dark:text-neutral-400">•</span>
                                                <span class="text-sm text-primary-600 dark:text-neutral-400">{{ exercise.planned_duration }} мин</span>
                                                <span :class="getStatusBadgeClass(exercise.status)">{{ exercise.status_label }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="flex space-x-2">
                                        <PrimaryButton
                                            @click="router.visit('/exercises/' + exercise.id)"
                                            size="sm"
                                        >
                                            Открыть
                                        </PrimaryButton>
                                        
                                        <SecondaryButton
                                            @click="addToSession(exercise)"
                                            size="sm"
                                        >
                                            + В занятие
                                        </SecondaryButton>
                                        
                                        <DangerButton
                                            @click="deleteExercise(exercise)"
                                            size="sm"
                                        >
                                            🗑️
                                        </DangerButton>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Пагинация -->
                        <div v-if="exercises.data.length > 0 && exercises.links" class="mt-6">
                            <nav class="flex items-center justify-between">
                                <div class="flex-1 flex justify-between sm:hidden">
                                    <Link
                                        v-if="exercises.prev_page_url"
                                        :href="exercises.prev_page_url"
                                        class="relative inline-flex items-center px-4 py-2 border border-neutral-300 dark:border-neutral-600 text-sm font-medium rounded-md text-neutral-700 dark:text-neutral-300 bg-white dark:bg-neutral-800 hover:bg-neutral-50 dark:hover:bg-neutral-700"
                                    >
                                        Предыдущая
                                    </Link>
                                    <Link
                                        v-if="exercises.next_page_url"
                                        :href="exercises.next_page_url"
                                        class="ml-3 relative inline-flex items-center px-4 py-2 border border-neutral-300 dark:border-neutral-600 text-sm font-medium rounded-md text-neutral-700 dark:text-neutral-300 bg-white dark:bg-neutral-800 hover:bg-neutral-50 dark:hover:bg-neutral-700"
                                    >
                                        Следующая
                                    </Link>
                                </div>
                                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                    <div>
                                        <p class="text-sm text-neutral-700 dark:text-neutral-300">
                                            Показано
                                            <span class="font-medium">{{ exercises.from }}</span>
                                            -
                                            <span class="font-medium">{{ exercises.to }}</span>
                                            из
                                            <span class="font-medium">{{ exercises.total }}</span>
                                            результатов
                                        </p>
                                    </div>
                                    <div>
                                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                            <Link
                                                v-for="link in exercises.links"
                                                :key="link.label"
                                                :href="link.url"
                                                v-html="link.label"
                                                :class="[
                                                    'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                                                    link.url === null
                                                        ? 'bg-neutral-100 dark:bg-neutral-700 text-neutral-400 dark:text-neutral-500 cursor-not-allowed'
                                                        : link.active
                                                            ? 'z-10 bg-accent-50 dark:bg-accent-900 border-accent-500 dark:border-accent-400 text-accent-600 dark:text-accent-300'
                                                            : 'bg-white dark:bg-neutral-800 border-neutral-300 dark:border-neutral-600 text-neutral-500 dark:text-neutral-400 hover:bg-neutral-50 dark:hover:bg-neutral-700'
                                                ]"
                                            />
                                        </nav>
                                    </div>
                                </div>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import { useForm, router } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import DangerButton from '@/Components/DangerButton.vue'

interface Exercise {
    id: number
    title: string
    description?: string
    type: string
    type_label: string
    planned_duration: number
    status: string
    status_label: string
    created_at: string
}

interface ExercisesData {
    data: Exercise[]
    links: any[]
    from: number
    to: number
    total: number
    prev_page_url: string | null
    next_page_url: string | null
}

interface Props {
    exercises: ExercisesData
}

const props = defineProps<Props>()
const form = useForm({})

// Функции для управления упражнениями
const getTypeIcon = (type: string): string => {
    const icons = {
        warmup: '🔥',
        technique: '⚡',
        repertoire: '🎵',
        improvisation: '🎨',
        sight_reading: '👀',
        theory: '📚',
        break: '☕',
        custom: '⭐',
    }
    return icons[type as keyof typeof icons] || '⭐'
}

const getStatusBadgeClass = (status: string): string => {
    const baseClass = 'px-2 py-1 text-xs font-medium rounded-full'
    const statusClasses = {
        planned: 'bg-primary-100 text-primary-800 dark:bg-neutral-600 dark:text-neutral-200',
        active: 'bg-primary-100 text-primary-800 dark:bg-success-900 dark:text-success-200',
        paused: 'bg-warning-100 text-warning-800 dark:bg-warning-900 dark:text-warning-200',
        completed: 'bg-danger-100 text-danger-800 dark:bg-accent-900 dark:text-accent-200',
        cancelled: 'bg-danger-200 text-danger-800 dark:bg-danger-900 dark:text-danger-200',
    }
    return `${baseClass} ${statusClasses[status as keyof typeof statusClasses] || 'bg-primary-100 text-primary-800 dark:bg-neutral-600 dark:text-neutral-200'}`
}

const addToSession = (exercise: Exercise) => {
    // Переходим к созданию сессии с предзаполненным упражнением
    router.visit('/sessions/create', {
        data: {
            exercise_id: exercise.id,
            exercise_title: exercise.title,
            exercise_type: exercise.type,
            exercise_duration: exercise.planned_duration,
            exercise_description: exercise.description
        }
    })
}

const deleteExercise = (exercise: Exercise) => {
    if (confirm(`Вы уверены, что хотите удалить упражнение "${exercise.title}"? Это действие нельзя отменить.`)) {
        router.delete(route('exercises.destroy', exercise.id), {
            onSuccess: () => {
                // Успешно удалено
            },
            onError: () => {
                alert('Ошибка при удалении упражнения')
            }
        })
    }
}
</script>