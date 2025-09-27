<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Мои упражнения
                </h2>
                <PrimaryButton @click="router.visit('/exercises/create')">
                    + Создать упражнение
                </PrimaryButton>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div v-if="exercises.data.length === 0" class="text-center py-12">
                            <div class="text-gray-500 mb-4">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <h4 class="text-lg font-medium text-gray-900 mb-2">Нет упражнений</h4>
                            <p class="text-gray-500 mb-4">Создайте свое первое упражнение для быстрой практики</p>
                            <PrimaryButton @click="router.visit('/exercises/create')">
                                Создать упражнение
                            </PrimaryButton>
                        </div>

                        <div v-else class="space-y-4">
                            <div v-for="exercise in exercises.data" :key="exercise.id" class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <span class="text-3xl">{{ getTypeIcon(exercise.type) }}</span>
                                        <div>
                                            <h3 class="text-lg font-medium text-gray-900">{{ exercise.title }}</h3>
                                            <p v-if="exercise.description" class="text-sm text-gray-600 mt-1">{{ exercise.description }}</p>
                                            <div class="flex items-center space-x-3 mt-2">
                                                <span class="text-sm text-gray-500">{{ exercise.type_label }}</span>
                                                <span class="text-sm text-gray-500">•</span>
                                                <span class="text-sm text-gray-500">{{ exercise.planned_duration }} мин</span>
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
                                        class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                    >
                                        Предыдущая
                                    </Link>
                                    <Link
                                        v-if="exercises.next_page_url"
                                        :href="exercises.next_page_url"
                                        class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                    >
                                        Следующая
                                    </Link>
                                </div>
                                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                    <div>
                                        <p class="text-sm text-gray-700">
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
                                                        ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                                                        : link.active
                                                            ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600'
                                                            : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
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
        planned: 'bg-gray-100 text-gray-800',
        active: 'bg-green-100 text-green-800',
        paused: 'bg-yellow-100 text-yellow-800',
        completed: 'bg-blue-100 text-blue-800',
        cancelled: 'bg-red-100 text-red-800',
    }
    return `${baseClass} ${statusClasses[status as keyof typeof statusClasses] || 'bg-gray-100 text-gray-800'}`
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