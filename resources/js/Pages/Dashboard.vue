<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, router } from '@inertiajs/vue3';
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
