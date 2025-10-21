<template>
    <div class="bg-gradient-to-br from-orange-50/80 to-red-50/80 dark:from-indigo-900 dark:to-purple-900 overflow-hidden shadow-lg sm:rounded-xl mb-4 border border-orange-200 dark:border-indigo-800">
        <div class="p-4 sm:p-6">
            <div class="flex items-center justify-between">
                <!-- Информация о блоке -->
                <div class="flex-1">
                    <div class="flex items-center space-x-3 mb-2">
                        <span class="text-2xl">{{ getTypeIcon(currentBlock.type) }}</span>
                        <h3 class="text-lg sm:text-xl font-bold text-orange-800 dark:text-gray-100">
                            {{ currentBlock.title }}
                        </h3>
                    </div>
                    <p v-if="currentBlock.description" class="text-sm text-orange-600 dark:text-gray-300 mb-2">
                        {{ currentBlock.description }}
                    </p>
                    <div class="text-sm text-orange-500 dark:text-gray-400">
                        {{ currentBlock.planned_duration }} мин запланировано
                    </div>
                </div>

                <!-- Компактный круговой таймер -->
                <div class="relative w-20 h-20 sm:w-24 sm:h-24 ml-4">
                    <svg class="w-full h-full transform -rotate-90" viewBox="0 0 100 100">
                        <circle
                            cx="50"
                            cy="50"
                            r="40"
                            stroke="currentColor"
                            stroke-width="6"
                            fill="none"
                            class="text-orange-200 dark:text-gray-700"
                        />
                        <circle
                            cx="50"
                            cy="50"
                            r="40"
                            stroke="currentColor"
                            stroke-width="6"
                            fill="none"
                            stroke-linecap="round"
                            :stroke-dasharray="circumference"
                            :stroke-dashoffset="circumference - (progress / 100) * circumference"
                            class="text-indigo-500 transition-all duration-1000 ease-in-out"
                            :class="{ 'text-red-500': progress >= 100 }"
                        />
                    </svg>

                    <!-- Время в центре -->
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <div
                            class="text-sm sm:text-lg font-bold transition-colors duration-300"
                            :class="progress >= 100 ? 'text-red-500 dark:text-red-400' : 'text-orange-500 dark:text-indigo-400'"
                        >
                            {{ formatTime(timeRemaining) }}
                        </div>
                        <div class="text-xs text-orange-500 dark:text-gray-400">
                            {{ Math.round(progress) }}%
                        </div>
                    </div>
                </div>
            </div>

            <!-- Линейный прогресс-бар -->
            <div class="mt-4">
                <div class="w-full bg-orange-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden shadow-inner">
                    <div
                        class="h-2 rounded-full transition-all duration-1000 ease-out relative"
                        :class="progress >= 100 ? 'bg-gradient-to-r from-red-400 to-red-500' : 'bg-gradient-to-r from-orange-400 to-red-500'"
                        :style="{ width: Math.min(progress, 100) + '%' }"
                    >
                        <div class="absolute inset-0 bg-white dark:bg-gray-300 opacity-30 animate-pulse"></div>
                    </div>
                </div>
            </div>

            <!-- Управление таймером -->
            <div class="flex justify-center gap-2 mt-4">
                <button
                    v-if="!isRunning"
                    @click="$emit('start-timer')"
                    :disabled="!canStart"
                    class="px-4 py-2 bg-green-500 text-white font-medium rounded-lg shadow hover:bg-green-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-sm"
                >
                    ▶ Запустить
                </button>

                <button
                    v-if="isRunning"
                    @click="$emit('pause-timer')"
                    class="px-4 py-2 bg-yellow-500 text-white font-medium rounded-lg shadow hover:bg-yellow-600 transition-colors text-sm"
                >
                    ⏸ Пауза
                </button>

                <button
                    @click="$emit('complete-timer')"
                    :disabled="!canStart"
                    class="px-4 py-2 bg-red-500 text-white font-medium rounded-lg shadow hover:bg-red-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-sm"
                >
                    ✓ Завершить
                </button>

                <!-- Кнопка управления звуками -->
                <button
                    @click="$emit('toggle-sound')"
                    @dblclick="$emit('show-sound-settings')"
                    :class="soundEnabled ? 'bg-blue-500 hover:bg-blue-600' : 'bg-gray-500 hover:bg-gray-600'"
                    class="px-3 py-2 text-white font-medium rounded-lg shadow transition-colors text-sm"
                    :title="soundEnabled ? 'Звуки включены (двойной клик для настроек)' : 'Звуки выключены (двойной клик для настроек)'"
                >
                    {{ soundEnabled ? '🔊' : '🔇' }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { getTypeIcon } from '@/utils/exerciseHelpers';
import { formatTime } from '@/utils/timeHelpers';

interface SessionBlock {
    id: number
    title: string
    description: string
    type: string
    planned_duration: number
}

interface Props {
    currentBlock: SessionBlock
    timeRemaining: number
    progress: number
    isRunning: boolean
    soundEnabled: boolean
    canStart: boolean
}

const props = defineProps<Props>();

defineEmits<{
    'start-timer': []
    'pause-timer': []
    'complete-timer': []
    'toggle-sound': []
    'show-sound-settings': []
}>();

const circumference = computed(() => {
    return 2 * Math.PI * 40; // радиус 40
});
</script>
