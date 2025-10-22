<template>
    <div class="bg-primary-50/90 dark:bg-neutral-800 overflow-hidden shadow-lg sm:rounded-xl border border-primary-200 dark:border-neutral-700">
        <div class="p-4 sm:p-6">
            <h3 class="text-lg sm:text-xl font-bold text-primary-800 dark:text-neutral-100 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-primary-500 dark:text-accent-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                </svg>
                Упражнения
            </h3>

            <div class="space-y-2 sm:space-y-3">
                <div
                    v-for="block in blocks"
                    :key="block.id"
                    :class="[
                        'group relative border rounded-lg p-3 sm:p-4 transition-all duration-300 hover:shadow-md',
                        getBlockStatusClass(block.status)
                    ]"
                >
                    <!-- Индикатор прогресса слева -->
                    <div class="absolute left-0 top-0 bottom-0 w-1 rounded-l-2xl"
                         :class="getBlockProgressClass(block.status)">
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3 flex-1 min-w-0">
                            <div class="flex items-center justify-center w-8 h-8 sm:w-10 sm:h-10 rounded-lg"
                                 :class="getBlockIconBgClass(block.status)">
                                <span class="text-lg sm:text-xl">{{ getTypeIcon(block.type) }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm sm:text-base font-semibold text-primary-800 dark:text-neutral-100 truncate">
                                    {{ block.title }}
                                </h4>
                                <div class="flex items-center space-x-2 mt-1">
                                    <span :class="getBlockBadgeClass(block.status)">
                                        {{ getStatusLabel(block.status) }}
                                    </span>
                                    <span class="text-xs text-primary-500 dark:text-neutral-400">
                                        {{ block.planned_duration }} мин
                                    </span>
                                    <span v-if="block.actual_duration" class="text-xs text-primary-500 dark:text-neutral-400">
                                        ({{ block.actual_duration }} мин)
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-1 ml-3">
                            <button
                                v-if="block.status === 'planned' && canControl"
                                @click="$emit('start-block', block)"
                                class="px-2 py-1 bg-success-500 text-white text-xs rounded hover:bg-success-600 transition-colors"
                            >
                                ▶
                            </button>

                            <button
                                v-if="block.status === 'active' && canControl"
                                @click="$emit('pause-block', block)"
                                class="px-2 py-1 bg-warning-500 text-white text-xs rounded hover:bg-warning-600 transition-colors"
                            >
                                ⏸
                            </button>

                            <button
                                v-if="block.status === 'paused' && canControl"
                                @click="$emit('start-block', block)"
                                class="px-2 py-1 bg-success-500 text-white text-xs rounded hover:bg-success-600 transition-colors"
                            >
                                ▶
                            </button>

                            <button
                                v-if="['active', 'paused'].includes(block.status) && canControl"
                                @click="$emit('complete-block', block)"
                                class="px-2 py-1 bg-danger-500 text-white text-xs rounded hover:bg-danger-600 transition-colors"
                            >
                                ✓
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { getTypeIcon } from '@/utils/exerciseHelpers';
import {
    getStatusLabel,
    getBlockStatusClass,
    getBlockProgressClass,
    getBlockIconBgClass,
    getBlockBadgeClass
} from '@/utils/statusHelpers';

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
    blocks: SessionBlock[]
    canControl: boolean
}

defineProps<Props>();

defineEmits<{
    'start-block': [block: SessionBlock]
    'pause-block': [block: SessionBlock]
    'complete-block': [block: SessionBlock]
}>();
</script>
