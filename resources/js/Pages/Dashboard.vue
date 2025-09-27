<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, router, useForm } from '@inertiajs/vue3';

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

</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800"
            >
                Дашборд
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Создать занятие -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-8 w-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-medium text-gray-900">Создать занятие</h3>
                                    <p class="text-sm text-gray-500">Начните новое музыкальное занятие</p>
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
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-medium text-gray-900">Мои занятия</h3>
                                    <p class="text-sm text-gray-500">Просмотр и управление занятиями</p>
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
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-medium text-gray-900">Статистика</h3>
                                    <p class="text-sm text-gray-500">Ваш прогресс в обучении</p>
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

                <!-- Упражнения -->
                <div class="mt-8 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Мои упражнения</h3>
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
                        
                        <div v-else class="space-y-3">
                            <div v-for="exercise in exercises.slice(0, 5)" :key="exercise.id" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <span class="text-2xl">{{ getTypeIcon(exercise.type) }}</span>
                                    <div>
                                        <h4 class="font-medium text-gray-900">{{ exercise.title }}</h4>
                                        <div class="flex items-center space-x-2 text-sm text-gray-500">
                                            <span>{{ exercise.type_label }}</span>
                                            <span>•</span>
                                            <span>{{ exercise.planned_duration }} мин</span>
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">
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
                                    Показать все ({{ exercises.length }})
                                </PrimaryButton>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Добро пожаловать -->
                <div class="mt-8 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Добро пожаловать в Music Practice Tracker!</h3>
                        <p class="text-gray-600 mb-4">
                            Это ваш личный помощник для планирования и отслеживания музыкальных занятий. 
                            Здесь вы можете создавать структурированные занятия, использовать таймеры для упражнений 
                            и отслеживать свой прогресс.
                        </p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-500">
                            <div>
                                <h4 class="font-medium text-gray-900 mb-2">Возможности:</h4>
                                <ul class="space-y-1">
                                    <li>• Создание структурированных занятий</li>
                                    <li>• Таймеры для каждого упражнения</li>
                                    <li>• Отслеживание прогресса</li>
                                    <li>• Шаблоны занятий</li>
                                </ul>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-900 mb-2">Как начать:</h4>
                                <ul class="space-y-1">
                                    <li>• Создайте ваше первое занятие</li>
                                    <li>• Добавьте упражнения с таймерами</li>
                                    <li>• Начните занятие и следите за прогрессом</li>
                                    <li>• Создавайте шаблоны для повторного использования</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
