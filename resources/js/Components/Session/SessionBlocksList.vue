<template>
    <div class="bg-primary-50/90 dark:bg-neutral-800 overflow-hidden shadow-lg sm:rounded-xl border border-primary-200 dark:border-neutral-700">
        <div class="p-4 sm:p-6">
            <div class="mb-4">
                <button
                    @click="isCollapsed = !isCollapsed"
                    class="w-full flex items-center justify-between hover:bg-primary-100/50 dark:hover:bg-neutral-700/50 rounded-lg px-2 py-2 transition-colors"
                >
                    <h3 class="text-lg sm:text-xl font-bold text-primary-800 dark:text-neutral-100 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-primary-500 dark:text-accent-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                        </svg>
                        Упражнения
                        <span class="ml-2 text-sm font-normal text-primary-600 dark:text-neutral-400">
                            ({{ localBlocks.length }})
                        </span>
                    </h3>
                    <svg
                        class="w-5 h-5 text-primary-500 dark:text-neutral-400 transition-transform duration-200"
                        :class="{ 'rotate-180': !isCollapsed }"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
            </div>

            <!-- Контент (сворачиваемый) -->
            <div v-show="!isCollapsed">
                <!-- Подсказка о перетаскивании -->
                <div 
                    v-if="canReorder" 
                    class="mb-3 px-3 py-2 bg-accent-50 dark:bg-accent-900/20 border border-accent-200 dark:border-accent-800 rounded-lg"
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

                <draggable
                v-model="localBlocks"
                :disabled="!canReorder"
                item-key="id"
                @end="onDragEnd"
                @start="isDragging = true"
                :animation="200"
                ghost-class="dragging-ghost"
                chosen-class="dragging-chosen"
                drag-class="dragging-active"
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
                                        <!-- Счетчик записей -->
                                        <span 
                                            v-if="block.audioRecordings && block.audioRecordings.length > 0" 
                                            class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 rounded-full"
                                            :title="`Записей: ${block.audioRecordings.length}`"
                                        >
                                            🎙️ {{ block.audioRecordings.length }}
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

                        <!-- Управление временем (для активных/приостановленных блоков) -->
                        <div v-if="['active', 'paused'].includes(block.status)" class="mt-3 pt-3 border-t border-primary-200 dark:border-neutral-700">
                            <div class="flex items-center justify-between flex-wrap gap-2">
                                <span class="text-xs font-medium text-neutral-600 dark:text-neutral-400">
                                    ⏱️ Управление временем:
                                </span>
                                <div class="flex items-center gap-1.5">
                                    <!-- Кнопки добавления времени -->
                                    <button
                                        @click="extendBlockTime(block.id, 5)"
                                        class="px-2 py-1 text-xs font-medium bg-accent-100 hover:bg-accent-200 dark:bg-accent-900/30 dark:hover:bg-accent-800/50 text-accent-700 dark:text-accent-300 rounded transition-colors"
                                        title="Добавить 5 минут"
                                    >
                                        +5
                                    </button>
                                    <button
                                        @click="extendBlockTime(block.id, 10)"
                                        class="px-2 py-1 text-xs font-medium bg-accent-100 hover:bg-accent-200 dark:bg-accent-900/30 dark:hover:bg-accent-800/50 text-accent-700 dark:text-accent-300 rounded transition-colors"
                                        title="Добавить 10 минут"
                                    >
                                        +10
                                    </button>
                                    <button
                                        @click="extendBlockTime(block.id, 15)"
                                        class="px-2 py-1 text-xs font-medium bg-accent-100 hover:bg-accent-200 dark:bg-accent-900/30 dark:hover:bg-accent-800/50 text-accent-700 dark:text-accent-300 rounded transition-colors"
                                        title="Добавить 15 минут"
                                    >
                                        +15
                                    </button>
                                    <div class="w-px h-4 bg-neutral-300 dark:bg-neutral-600"></div>
                                    <!-- Кнопка перезапуска -->
                                    <button
                                        @click="restartBlock(block.id)"
                                        class="px-2 py-1 text-xs font-medium bg-primary-100 hover:bg-primary-200 dark:bg-primary-900/30 dark:hover:bg-primary-800/50 text-primary-700 dark:text-primary-300 rounded transition-colors flex items-center gap-1"
                                        title="Перезапустить таймер"
                                    >
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                        <span class="hidden sm:inline">Сброс</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Список аудио записей (сворачиваемый) -->
                        <div v-if="block.audioRecordings && block.audioRecordings.length > 0" class="mt-3 pt-3 border-t border-primary-200 dark:border-neutral-700">
                            <button
                                @click="toggleRecordings(block.id)"
                                class="w-full flex items-center justify-between text-sm text-primary-700 dark:text-neutral-300 hover:text-primary-900 dark:hover:text-neutral-100 transition-colors"
                            >
                                <span class="font-medium flex items-center gap-2">
                                    🎙️ Записи упражнения ({{ block.audioRecordings.length }})
                                </span>
                                <svg
                                    class="w-4 h-4 transition-transform duration-200"
                                    :class="{ 'rotate-180': expandedRecordings[block.id] }"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <!-- Список записей -->
                            <div v-show="expandedRecordings[block.id]" class="mt-2 space-y-2">
                                <div
                                    v-for="recording in block.audioRecordings"
                                    :key="recording.id"
                                    class="p-3 bg-neutral-50 dark:bg-neutral-900/50 rounded-lg border border-neutral-200 dark:border-neutral-700"
                                >
                                    <!-- Заголовок записи -->
                                    <div class="flex items-start justify-between mb-2">
                                        <div class="flex-1">
                                            <h5 class="text-sm font-medium text-neutral-900 dark:text-neutral-100">
                                                {{ recording.title || recording.file_name }}
                                            </h5>
                                            <div class="flex items-center gap-2 mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                                                <span>{{ new Date(recording.recorded_at).toLocaleString('ru-RU') }}</span>
                                                <span v-if="recording.formatted_duration">• {{ recording.formatted_duration }}</span>
                                                <span>• {{ recording.formatted_file_size }}</span>
                                            </div>
                                        </div>
                                        <!-- Оценка качества -->
                                        <div v-if="recording.quality_rating" class="flex items-center gap-0.5 ml-2">
                                            <svg
                                                v-for="star in 5"
                                                :key="star"
                                                class="w-3 h-3"
                                                :class="star <= recording.quality_rating ? 'text-warning-400' : 'text-neutral-300 dark:text-neutral-600'"
                                                fill="currentColor"
                                                viewBox="0 0 20 20"
                                            >
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                            </svg>
                                        </div>
                                    </div>

                                    <!-- Заметки -->
                                    <p v-if="recording.notes" class="text-xs text-neutral-600 dark:text-neutral-400 mb-2">
                                        {{ recording.notes }}
                                    </p>

                                    <!-- Аудио плеер -->
                                    <audio
                                        :src="recording.audio_url"
                                        controls
                                        controlsList="nodownload"
                                        class="w-full h-8"
                                        style="max-height: 32px;"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </draggable>
            </div>
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

interface AudioRecording {
    id: number
    title: string | null
    notes: string | null
    file_name: string
    audio_url: string
    formatted_duration: string | null
    formatted_file_size: string
    quality_rating: number | null
    recorded_at: string
}

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
    audioRecordings?: AudioRecording[]
}

interface Props {
    blocks: SessionBlock[]
    sessionId: number
}

const props = defineProps<Props>();

const localBlocks = ref<SessionBlock[]>([...props.blocks]);
const isStarting = ref(false);
const isDragging = ref(false);
const isCollapsed = ref(false);
const expandedRecordings = ref<Record<number, boolean>>({});

// Можно изменять порядок если есть хотя бы 2 незавершенных блока
const canReorder = computed(() => {
    const unfinishedBlocks = localBlocks.value.filter(block => 
        block.status === 'planned' || block.status === 'paused' || block.status === 'active'
    );
    
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

const toggleRecordings = (blockId: number) => {
    expandedRecordings.value[blockId] = !expandedRecordings.value[blockId];
};

const extendBlockTime = (blockId: number, minutes: number) => {
    const block = props.blocks.find(b => b.id === blockId);
    if (!block) return;
    
    const newPlannedDuration = block.planned_duration + minutes;
    
    router.patch(
        route('sessions.blocks.update', { session: props.sessionId, block: blockId }),
        { planned_duration: newPlannedDuration },
        {
            preserveScroll: true,
            onSuccess: () => {
                // Обновляем локальный блок после успешного обновления
                const localBlock = localBlocks.value.find(b => b.id === blockId);
                if (localBlock) {
                    localBlock.planned_duration = newPlannedDuration;
                }
            }
        }
    );
};

const restartBlock = (blockId: number) => {
    if (confirm('Вы уверены, что хотите перезапустить таймер этого упражнения?')) {
        router.patch(
            route('sessions.blocks.update', { session: props.sessionId, block: blockId }),
            { 
                status: 'active',
                actual_duration: null,
                completed_at: null,
            },
            {
                preserveScroll: true,
            }
        );
    }
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

<style scoped>
/* Стили для drag & drop */

/* Ghost - элемент под курсором при перетаскивании */
.dragging-ghost {
    opacity: 0.6 !important;
    transform: scale(1.05) !important;
    cursor: grabbing !important;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 10px 10px -5px rgba(0, 0, 0, 0.2) !important;
}

/* Chosen - оригинальный элемент на своем месте (когда начинаем тянуть) */
.dragging-chosen {
    opacity: 0.5 !important;
    border: 2px dashed #cbd5e1 !important;
    background: #f8fafc !important;
}

/* Темная тема для chosen */
:global(.dark) .dragging-chosen {
    background: #0f172a !important;
    border-color: #334155 !important;
}

/* Drag - дополнительные эффекты во время перетаскивания */
.dragging-active {
    cursor: grabbing !important;
}
</style>
