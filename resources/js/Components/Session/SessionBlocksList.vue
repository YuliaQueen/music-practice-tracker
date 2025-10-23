<template>
    <div class="bg-primary-50/90 dark:bg-neutral-800 overflow-hidden shadow-lg sm:rounded-xl border border-primary-200 dark:border-neutral-700">
        <div class="p-4 sm:p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg sm:text-xl font-bold text-primary-800 dark:text-neutral-100 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-primary-500 dark:text-accent-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                    </svg>
                    Упражнения
                </h3>
                
                <!-- Подсказка о перетаскивании -->
                <div v-if="canReorder" class="text-xs text-primary-500 dark:text-neutral-400 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                    </svg>
                    Перетащите для изменения порядка
                </div>
            </div>

            <draggable
                v-model="localBlocks"
                :disabled="!canReorder"
                item-key="id"
                @end="onDragEnd"
                :animation="200"
                handle=".drag-handle"
                ghost-class="opacity-50"
                class="space-y-2 sm:space-y-3"
            >
                <template #item="{ element: block }">
                    <div
                        :class="[
                            'group relative border rounded-lg p-3 sm:p-4 transition-all duration-300 hover:shadow-md',
                            getBlockStatusClass(block.status),
                            canReorder ? 'cursor-move' : ''
                        ]"
                    >
                        <!-- Индикатор прогресса слева -->
                        <div class="absolute left-0 top-0 bottom-0 w-1 rounded-l-2xl"
                             :class="getBlockProgressClass(block.status)">
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3 flex-1 min-w-0">
                                <!-- Drag handle -->
                                <div 
                                    v-if="canReorder"
                                    class="drag-handle flex-shrink-0 cursor-grab active:cursor-grabbing text-primary-400 dark:text-neutral-500 hover:text-primary-600 dark:hover:text-neutral-300 transition-colors"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
                                    </svg>
                                </div>
                                
                                <!-- Номер упражнения -->
                                <div class="flex-shrink-0 w-6 h-6 sm:w-8 sm:h-8 rounded-full bg-primary-100 dark:bg-neutral-700 flex items-center justify-center">
                                    <span class="text-xs sm:text-sm font-bold text-primary-600 dark:text-neutral-300">
                                        {{ block.sort_order }}
                                    </span>
                                </div>
                                
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

                            <!-- Кнопка начать упражнение -->
                            <button
                                v-if="block.status === 'planned' || block.status === 'paused'"
                                @click="startBlock(block.id)"
                                class="ml-2 px-3 py-1.5 sm:px-4 sm:py-2 bg-accent-500 hover:bg-accent-600 text-white text-xs sm:text-sm font-medium rounded-lg transition-colors duration-200 flex items-center space-x-1"
                                :disabled="isStarting"
                            >
                                <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="hidden sm:inline">Начать</span>
                            </button>
                        </div>
                    </div>
                </template>
            </draggable>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import draggable from 'vuedraggable';
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
    sort_order: number
    started_at: string | null
    completed_at: string | null
}

interface Props {
    blocks: SessionBlock[]
    sessionId: number
}

const props = defineProps<Props>();

const localBlocks = ref<SessionBlock[]>([...props.blocks]);
const isStarting = ref(false);

// Можно изменять порядок только для запланированных сессий
const canReorder = computed(() => {
    return localBlocks.value.every(block => 
        block.status === 'planned' || block.status === 'paused'
    );
});

const startBlock = (blockId: number) => {
    if (isStarting.value) return;
    
    isStarting.value = true;
    
    router.post(
        route('sessions.blocks.start', { session: props.sessionId, block: blockId }),
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                isStarting.value = false;
            }
        }
    );
};

const onDragEnd = () => {
    // Обновляем sort_order локально для немедленного отображения
    localBlocks.value.forEach((block, index) => {
        block.sort_order = index + 1;
    });

    // Отправляем новый порядок на сервер
    const blockIds = localBlocks.value.map(block => block.id);
    
    router.post(
        route('sessions.blocks.reorder', { session: props.sessionId }),
        { block_ids: blockIds },
        {
            preserveScroll: true,
            onError: () => {
                // При ошибке возвращаем исходный порядок
                localBlocks.value = [...props.blocks];
            }
        }
    );
};
</script>
