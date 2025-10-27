<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
                <h2 class="font-semibold text-xl text-primary-800 dark:text-neutral-200 leading-tight">
                    Архив упражнений
                </h2>
                <PrimaryButton @click="router.visit('/exercises')" class="w-full sm:w-auto">
                    ← К активным упражнениям
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
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                </svg>
                            </div>
                            <h4 class="text-lg font-medium text-primary-900 dark:text-neutral-100 mb-2">Архив пуст</h4>
                            <p class="text-primary-600 dark:text-neutral-400">Здесь будут отображаться изученные упражнения</p>
                        </div>

                        <div v-else class="grid grid-cols-1 gap-4">
                            <div
                                v-for="exercise in exercises.data"
                                :key="exercise.id"
                                class="bg-white dark:bg-neutral-700 rounded-lg shadow p-6 hover:shadow-md transition-shadow"
                            >
                                <div class="flex justify-between items-start mb-4">
                                    <div class="flex-1">
                                        <h3 class="text-lg font-semibold text-primary-900 dark:text-neutral-100">
                                            {{ exercise.title }}
                                        </h3>
                                        <p v-if="exercise.description" class="text-sm text-primary-600 dark:text-neutral-400 mt-1">
                                            {{ exercise.description }}
                                        </p>
                                        <div class="flex flex-wrap gap-2 mt-3">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-primary-100 text-primary-800 dark:bg-neutral-600 dark:text-neutral-200">
                                                {{ getTypeLabel(exercise.type) }}
                                            </span>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                ✓ Изучено
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Статистика -->
                                <ExerciseStatistics :exercise="exercise" />

                                <!-- Действия -->
                                <div class="flex gap-2 mt-4">
                                    <button
                                        @click="restoreExercise(exercise.id)"
                                        class="px-4 py-2 bg-accent-600 text-white rounded-md hover:bg-accent-700 transition-colors text-sm"
                                    >
                                        ↩ Вернуть в активные
                                    </button>
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
import { router, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import ExerciseStatistics from '@/Components/Exercise/ExerciseStatistics.vue'
import { getTypeLabel } from '@/utils/exerciseHelpers'
import type { Exercise } from '@/types/models'

interface Props {
    exercises: {
        data: Exercise[]
        links: any[]
        from: number
        to: number
        total: number
        current_page: number
        last_page: number
        prev_page_url: string | null
        next_page_url: string | null
    }
}

defineProps<Props>()

const restoreExercise = (exerciseId: number) => {
    if (confirm('Вернуть упражнение в список активных?')) {
        router.post(`/exercises/${exerciseId}/restore`, {}, {
            preserveScroll: true,
        })
    }
}
</script>
