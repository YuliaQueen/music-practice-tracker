<template>
    <div class="bg-gradient-to-br from-primary-50 to-primary-50 dark:from-neutral-800 dark:to-neutral-900 overflow-hidden shadow-lg sm:rounded-xl mb-4 border border-primary-200 dark:border-neutral-700">
        <div class="p-4 sm:p-6 text-primary-900 dark:text-neutral-100">
            <!-- Статистика сессии -->
            <div class="grid grid-cols-3 gap-4 sm:gap-6 mb-4">
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 bg-primary-100 dark:bg-accent-900 rounded-xl mb-2 shadow-sm">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-primary-600 dark:text-accent-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xs sm:text-sm font-medium text-primary-700 dark:text-neutral-400 mb-1">Запланировано</h3>
                    <p class="text-lg sm:text-xl font-bold text-primary-900 dark:text-neutral-100">{{ session.planned_duration }} мин</p>
                </div>
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 bg-primary-100 dark:bg-success-900 rounded-xl mb-2 shadow-sm">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-primary-600 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xs sm:text-sm font-medium text-primary-700 dark:text-neutral-400 mb-1">Фактически</h3>
                    <p class="text-lg sm:text-xl font-bold text-primary-900 dark:text-neutral-100">
                        {{ session.actual_duration || '—' }} мин
                    </p>
                </div>
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 bg-danger-100 dark:bg-secondary-900 rounded-xl mb-2 shadow-sm">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-danger-500 dark:text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <h3 class="text-xs sm:text-sm font-medium text-primary-700 dark:text-neutral-400 mb-1">Прогресс</h3>
                    <p class="text-lg sm:text-xl font-bold text-primary-900 dark:text-neutral-100">{{ progressPercentage }}%</p>
                </div>
            </div>

            <!-- Описание сессии -->
            <div v-if="session.description" class="p-4 sm:p-5 bg-primary-50/80 dark:bg-neutral-800 rounded-xl border border-primary-200 dark:border-neutral-700 shadow-sm">
                <h3 class="text-sm sm:text-base font-semibold text-primary-800 dark:text-neutral-100 mb-2 flex items-center">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2 text-primary-600 dark:text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Описание
                </h3>
                <p class="text-sm sm:text-base text-primary-700 dark:text-neutral-300 leading-relaxed">{{ session.description }}</p>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

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
}

const props = defineProps<Props>();

const progressPercentage = computed(() => {
    const completedBlocks = props.session.blocks.filter(block => block.status === 'completed').length;
    const totalBlocks = props.session.blocks.length;
    return totalBlocks > 0 ? Math.round((completedBlocks / totalBlocks) * 100) : 0;
});
</script>
