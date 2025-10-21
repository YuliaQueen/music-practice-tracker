<template>
    <div class="bg-gradient-to-br from-amber-50 to-orange-50 dark:from-gray-800 dark:to-gray-900 overflow-hidden shadow-lg sm:rounded-xl mb-4 border border-amber-200 dark:border-gray-700">
        <div class="p-4 sm:p-6 text-amber-900 dark:text-gray-100">
            <!-- Статистика сессии -->
            <div class="grid grid-cols-3 gap-4 sm:gap-6 mb-4">
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 bg-amber-100 dark:bg-blue-900 rounded-xl mb-2 shadow-sm">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-amber-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xs sm:text-sm font-medium text-amber-700 dark:text-gray-400 mb-1">Запланировано</h3>
                    <p class="text-lg sm:text-xl font-bold text-amber-900 dark:text-gray-100">{{ session.planned_duration }} мин</p>
                </div>
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 bg-orange-100 dark:bg-green-900 rounded-xl mb-2 shadow-sm">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-orange-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xs sm:text-sm font-medium text-amber-700 dark:text-gray-400 mb-1">Фактически</h3>
                    <p class="text-lg sm:text-xl font-bold text-amber-900 dark:text-gray-100">
                        {{ session.actual_duration || '—' }} мин
                    </p>
                </div>
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 bg-red-100 dark:bg-purple-900 rounded-xl mb-2 shadow-sm">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-red-500 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <h3 class="text-xs sm:text-sm font-medium text-amber-700 dark:text-gray-400 mb-1">Прогресс</h3>
                    <p class="text-lg sm:text-xl font-bold text-amber-900 dark:text-gray-100">{{ progressPercentage }}%</p>
                </div>
            </div>

            <!-- Описание сессии -->
            <div v-if="session.description" class="mb-8 p-6 bg-orange-50/80 dark:bg-gray-800 rounded-2xl border border-orange-200 dark:border-gray-700 shadow-sm">
                <h3 class="text-lg font-semibold text-amber-800 dark:text-gray-100 mb-3 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-amber-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Описание
                </h3>
                <p class="text-amber-700 dark:text-gray-300 leading-relaxed">{{ session.description }}</p>
            </div>

            <!-- Управление сессией -->
            <div class="flex flex-wrap justify-center gap-4">
                <button
                    v-if="session.status === 'planned'"
                    @click="$emit('start')"
                    :disabled="processing"
                    class="px-8 py-4 bg-gradient-to-r from-green-500 to-green-600 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                >
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                        </svg>
                        <span>Начать занятие</span>
                    </div>
                </button>

                <button
                    v-if="session.status === 'active'"
                    @click="$emit('pause')"
                    :disabled="processing"
                    class="px-8 py-4 bg-gradient-to-r from-yellow-500 to-yellow-600 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                >
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM7 8a1 1 0 012 0v4a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v4a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        <span>Приостановить</span>
                    </div>
                </button>

                <button
                    v-if="session.status === 'paused'"
                    @click="$emit('start')"
                    :disabled="processing"
                    class="px-8 py-4 bg-gradient-to-r from-green-500 to-green-600 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                >
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                        </svg>
                        <span>Продолжить</span>
                    </div>
                </button>

                <button
                    v-if="['active', 'paused'].includes(session.status)"
                    @click="$emit('complete')"
                    :disabled="processing"
                    class="px-8 py-4 bg-gradient-to-r from-red-500 to-red-600 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                >
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        <span>Завершить занятие</span>
                    </div>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

interface SessionBlock {
    id: number
    status: string
}

interface Session {
    id: number
    title: string
    description: string
    planned_duration: number
    actual_duration: number | null
    status: string
    blocks: SessionBlock[]
}

interface Props {
    session: Session
    processing?: boolean
}

const props = withDefaults(defineProps<Props>(), {
    processing: false
});

defineEmits<{
    start: []
    pause: []
    complete: []
}>();

const progressPercentage = computed(() => {
    const completedBlocks = props.session.blocks.filter(block => block.status === 'completed').length;
    const totalBlocks = props.session.blocks.length;
    return totalBlocks > 0 ? Math.round((completedBlocks / totalBlocks) * 100) : 0;
});
</script>
