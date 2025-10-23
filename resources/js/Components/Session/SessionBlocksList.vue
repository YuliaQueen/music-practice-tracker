<template>
    <div class="bg-primary-50/90 dark:bg-neutral-800 overflow-hidden shadow-lg sm:rounded-xl border border-primary-200 dark:border-neutral-700">
        <div class="p-4 sm:p-6">
            <div class="mb-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg sm:text-xl font-bold text-primary-800 dark:text-neutral-100 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-primary-500 dark:text-accent-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                        </svg>
                        Упражнения
                    </h3>
                </div>
                
                <!-- Подсказка о перетаскивании -->
                <div 
                    v-if="canReorder" 
                    class="mt-2 px-3 py-2 bg-accent-50 dark:bg-accent-900/20 border border-accent-200 dark:border-accent-800 rounded-lg"
                >
                    <div class="flex items-center text-xs sm:text-sm text-accent-700 dark:text-accent-300">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>
                            <strong>Совет:</strong> Зажмите и перетащите любое упражнение для изменения порядка. 
                            Иконка <span class="inline-flex align-middle mx-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
                                </svg>
                            </span> указывает что можно перетаскивать.
                        </span>
                    </div>
                </div>
            </div>

            <draggable
                v-model="localBlocks"
                :disabled="!canReorder"
                item-key="id"
                @end="onDragEnd"
                @start="isDragging = true"
                :animation="200"
                ghost-class="opacity-50 scale-105"
                chosen-class="shadow-2xl ring-2 ring-accent-400"
                drag-class="rotate-2"
                tag="div"
                class="space-y-2 sm:space-y-3"
            >
                <template #item="{ element: block }">
                    <div
                        :class="[
                            'group relative border rounded-lg p-3 sm:p-4 transition-all duration-300',
                            getBlockStatusClass(block.status),
                            canReorder ? 'cursor-move hover:shadow-lg hover:scale-[1.02] hover:border-accent-300 dark:hover:border-accent-600 select-none' : 'hover:shadow-md'
                        ]"
                    >
                        <!-- Индикатор прогресса слева -->
                        <div class="absolute left-0 top-0 bottom-0 w-1 rounded-l-2xl"
                             :class="getBlockProgressClass(block.status)">
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3 flex-1 min-w-0">
                                <!-- Drag handle - иконка для визуального указания -->
                                <div 
                                    v-if="canReorder"
                                    class="flex-shrink-0 text-primary-400 dark:text-neutral-500 group-hover:text-accent-500 dark:group-hover:text-accent-400 transition-all group-hover:scale-110"
                                    title="Зажмите и перетащите"
                                >
                                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 8h16M4 16h16" />
                                    </svg>
                                </div>
                                
                                <!-- Номер упражнения -->
                                <div 
                                    class="flex-shrink-0 w-6 h-6 sm:w-8 sm:h-8 rounded-full flex items-center justify-center transition-all"
                                    :class="canReorder ? 'bg-accent-100 dark:bg-accent-900/30' : 'bg-primary-100 dark:bg-neutral-700'"
                                >
                                    <span 
                                        class="text-xs sm:text-sm font-bold transition-colors"
                                        :class="canReorder ? 'text-accent-600 dark:text-accent-400' : 'text-primary-600 dark:text-neutral-300'"
                                    >
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
const isDragging = ref(false);

// Можно изменять порядок если есть хотя бы 2 незавершенных блока
const canReorder = computed(() => {
    const unfinishedBlocks = localBlocks.value.filter(block => 
        block.status === 'planned' || block.status === 'paused' || block.status === 'active'
    );
    
    // Отладка
    console.log('SessionBlocksList - canReorder check:', {
        totalBlocks: localBlocks.value.length,
        unfinishedBlocks: unfinishedBlocks.length,
        canReorder: unfinishedBlocks.length >= 2,
        blockStatuses: localBlocks.value.map(b => ({ id: b.id, title: b.title, status: b.status }))
    });
    
    return unfinishedBlocks.length >= 2;
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
    isDragging.value = false;
    
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
            onSuccess: () => {
                // Показываем уведомление об успехе (опционально)
            },
            onError: () => {
                // При ошибке возвращаем исходный порядок
                localBlocks.value = [...props.blocks];
            }
        }
    );
};
</script>
