<template>
    <div class="bg-gradient-to-br from-primary-50/80 to-danger-50/80 dark:from-accent-900 dark:to-secondary-900 overflow-hidden shadow-2xl sm:rounded-2xl mb-6 border-2 border-primary-300 dark:border-accent-700">
        <div class="p-6 sm:p-8">
            <div class="flex items-center justify-between">
                <!-- Информация о блоке -->
                <div class="flex-1">
                    <div class="flex items-center space-x-3 mb-3">
                        <span class="text-3xl sm:text-4xl">{{ getTypeIcon(currentBlock.type) }}</span>
                        <h3 class="text-xl sm:text-2xl font-bold text-primary-800 dark:text-neutral-100">
                            {{ currentBlock.title }}
                        </h3>
                    </div>
                    <p v-if="currentBlock.description" class="text-base sm:text-lg text-primary-600 dark:text-neutral-300 mb-2">
                        {{ currentBlock.description }}
                    </p>
                    <div class="text-base sm:text-lg font-medium text-primary-500 dark:text-neutral-400">
                        {{ currentBlock.planned_duration }} мин запланировано
                    </div>
                </div>

                <!-- Большой круговой таймер -->
                <div class="relative w-40 h-40 sm:w-52 sm:h-52 ml-4 shadow-xl">
                    <svg class="w-full h-full transform -rotate-90" viewBox="0 0 100 100">
                        <circle
                            cx="50"
                            cy="50"
                            r="40"
                            stroke="currentColor"
                            stroke-width="8"
                            fill="none"
                            class="text-primary-200 dark:text-neutral-700"
                        />
                        <circle
                            cx="50"
                            cy="50"
                            r="40"
                            stroke="currentColor"
                            stroke-width="8"
                            fill="none"
                            stroke-linecap="round"
                            :stroke-dasharray="circumference"
                            :stroke-dashoffset="circumference - (progress / 100) * circumference"
                            class="text-accent-500 transition-all duration-1000 ease-in-out drop-shadow-lg"
                            :class="{ 'text-danger-500': progress >= 100 }"
                        />
                    </svg>

                    <!-- Время в центре -->
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <div
                            class="text-2xl sm:text-4xl font-bold transition-colors duration-300"
                            :class="progress >= 100 ? 'text-danger-500 dark:text-danger-400' : 'text-primary-600 dark:text-accent-400'"
                        >
                            {{ formatTime(timeRemaining) }}
                        </div>
                        <div class="text-sm sm:text-base font-semibold text-primary-500 dark:text-neutral-400 mt-1">
                            {{ Math.round(progress) }}%
                        </div>
                    </div>
                </div>
            </div>

            <!-- Линейный прогресс-бар -->
            <div class="mt-4">
                <div class="w-full bg-primary-200 dark:bg-neutral-700 rounded-full h-2 overflow-hidden shadow-inner">
                    <div
                        class="h-2 rounded-full transition-all duration-1000 ease-out relative"
                        :class="progress >= 100 ? 'bg-gradient-to-r from-danger-400 to-danger-500' : 'bg-gradient-to-r from-primary-400 to-danger-500'"
                        :style="{ width: Math.min(progress, 100) + '%' }"
                    >
                        <div class="absolute inset-0 bg-white dark:bg-neutral-300 opacity-30 animate-pulse"></div>
                    </div>
                </div>
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
    actual_duration: number | null
    status: string
    started_at: string | null
    completed_at: string | null
}

interface Props {
    currentBlock: SessionBlock
    timeRemaining: number
    progress: number
    isRunning: boolean
}

const props = defineProps<Props>();

const circumference = computed(() => {
    return 2 * Math.PI * 40; // радиус 40
});
</script>
