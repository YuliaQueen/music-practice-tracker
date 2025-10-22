<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import MetronomeWidget from '@/Components/Metronome/MetronomeWidget.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { useSimpleI18n } from '@/composables/useSimpleI18n';

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

interface Props {
    exercises: Exercise[]
}

const props = defineProps<Props>()
const form = useForm({})
const { t } = useSimpleI18n()

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
    return `${baseClass} ${statusClasses[status as keyof typeof statusClasses] || 'bg-neutral-100 text-neutral-800 dark:bg-neutral-600 dark:text-neutral-200'}`
}

</script>

<template>
    <Head :title="t('dashboard.title')" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-primary-800 dark:text-neutral-200"
            >
                {{ t('dashboard.title') }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Создать занятие -->
                    <div class="bg-primary-50 overflow-hidden shadow-sm sm:rounded-lg dark:bg-neutral-800 dark:shadow-neutral-900/20">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-8 w-8 text-primary-600 dark:text-accent-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M18 3a1 1 0 00-1.196-.98l-10 2A1 1 0 006 5v9.114A4.369 4.369 0 005 14c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V7.82l8-1.6v5.894A4.369 4.369 0 0015 12c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V3z"/>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-medium text-primary-900 dark:text-neutral-100">Создать занятие</h3>
                                    <p class="text-sm text-primary-600 dark:text-neutral-400">Начните новое музыкальное занятие</p>
                                </div>
                            </div>
                            <div class="mt-4">
                                <PrimaryButton @click="router.visit('/sessions/create')" class="w-full">
                                    Создать занятие
                                </PrimaryButton>
                            </div>
                        </div>
                    </div>

                    <!-- Мои занятия -->
                    <div class="bg-danger-50 overflow-hidden shadow-sm sm:rounded-lg dark:bg-neutral-800 dark:shadow-neutral-900/20">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-8 w-8 text-danger-600 dark:text-success-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M18 3a1 1 0 00-1.196-.98l-10 2A1 1 0 006 5v9.114A4.369 4.369 0 005 14c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V7.82l8-1.6v5.894A4.369 4.369 0 0015 12c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V3z"/>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-medium text-primary-900 dark:text-neutral-100">Мои занятия</h3>
                                    <p class="text-sm text-primary-600 dark:text-neutral-400">Просмотр и управление занятиями</p>
                                </div>
                            </div>
                            <div class="mt-4">
                                <PrimaryButton @click="router.visit('/sessions')" class="w-full">
                                    Открыть занятия
                                </PrimaryButton>
                            </div>
                        </div>
                    </div>

                    <!-- Статистика -->
                    <div class="bg-warning-50 overflow-hidden shadow-sm sm:rounded-lg dark:bg-neutral-800 dark:shadow-neutral-900/20">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-8 w-8 text-warning-600 dark:text-accent-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M18 3a1 1 0 00-1.196-.98l-10 2A1 1 0 006 5v9.114A4.369 4.369 0 005 14c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V7.82l8-1.6v5.894A4.369 4.369 0 0015 12c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V3z"/>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-medium text-primary-900 dark:text-neutral-100">Статистика</h3>
                                    <p class="text-sm text-primary-600 dark:text-neutral-400">Ваш прогресс в обучении</p>
                                </div>
                            </div>
                            <div class="mt-4">
                                <PrimaryButton @click="router.visit('/statistics')" class="w-full">
                                    Открыть статистику
                                </PrimaryButton>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Метроном -->
                <div class="mt-8">
                    <MetronomeWidget />
                </div>

                <!-- Упражнения -->
                <div class="mt-8 bg-white overflow-hidden shadow-sm sm:rounded-lg dark:bg-neutral-800 dark:shadow-neutral-900/20">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100">Мои упражнения</h3>
                            <div class="flex space-x-2">
                                <PrimaryButton @click="router.visit('/exercises')" size="sm">
                                    Все упражнения
                                </PrimaryButton>
                                <PrimaryButton @click="router.visit('/exercises/create')" size="sm">
                                    + Добавить упражнение
                                </PrimaryButton>
                            </div>
                        </div>
                        
                        <div v-if="exercises.length === 0" class="text-center py-8">
                            <div class="text-neutral-500 mb-4">
                                <svg class="mx-auto h-12 w-12 text-neutral-400 dark:text-neutral-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <h4 class="text-lg font-medium text-neutral-900 dark:text-neutral-100 mb-2">Нет упражнений</h4>
                            <p class="text-neutral-500 dark:text-neutral-400 mb-4">Создайте свое первое упражнение для быстрой практики</p>
                            <PrimaryButton @click="router.visit('/exercises/create')">
                                Создать упражнение
                            </PrimaryButton>
                        </div>
                        
                        <div v-else class="space-y-3">
                            <div v-for="exercise in exercises.slice(0, 5)" :key="exercise.id" class="flex items-center justify-between p-3 bg-neutral-50 dark:bg-neutral-700 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <span class="text-2xl">{{ getTypeIcon(exercise.type) }}</span>
                                    <div>
                                        <h4 class="font-medium text-neutral-900 dark:text-neutral-100">{{ exercise.title }}</h4>
                                        <div class="flex items-center space-x-2 text-sm text-neutral-500 dark:text-neutral-400">
                                            <span>{{ exercise.type_label }}</span>
                                            <span>•</span>
                                            <span>{{ exercise.planned_duration }} мин</span>
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-neutral-100 text-neutral-800 dark:bg-neutral-600 dark:text-neutral-200">
                                                Готово к использованию
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex space-x-2">
                                    <PrimaryButton @click="router.visit('/exercises')" size="sm">
                                        Управлять
                                    </PrimaryButton>
                                </div>
                            </div>
                            
                            <div v-if="exercises.length > 5" class="text-center pt-4">
                                <PrimaryButton @click="router.visit('/exercises')" size="sm">
                                    {{ t('dashboard.showAll') }} ({{ exercises.length }})
                                </PrimaryButton>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Добро пожаловать -->
                <div class="mt-8 bg-white overflow-hidden shadow-sm sm:rounded-lg dark:bg-neutral-800 dark:shadow-neutral-900/20">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100 mb-4">{{ t('dashboard.welcome') }}</h3>
                        <p class="text-neutral-600 dark:text-neutral-300 mb-4">
                            {{ t('dashboard.description') }}
                        </p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-neutral-500 dark:text-neutral-400">
                            <div>
                                <h4 class="font-medium text-neutral-900 dark:text-neutral-100 mb-2">{{ t('dashboard.features') }}:</h4>
                                <ul class="space-y-1">
                                    <li>• {{ t('dashboard.feature1') }}</li>
                                    <li>• {{ t('dashboard.feature2') }}</li>
                                    <li>• {{ t('dashboard.feature3') }}</li>
                                    <li>• {{ t('dashboard.feature4') }}</li>
                                </ul>
                            </div>
                            <div>
                                <h4 class="font-medium text-neutral-900 dark:text-neutral-100 mb-2">{{ t('dashboard.howToStart') }}:</h4>
                                <ul class="space-y-1">
                                    <li>• {{ t('dashboard.step1') }}</li>
                                    <li>• {{ t('dashboard.step2') }}</li>
                                    <li>• {{ t('dashboard.step3') }}</li>
                                    <li>• {{ t('dashboard.step4') }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
