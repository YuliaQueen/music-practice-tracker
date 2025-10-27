<template>
    <!-- Фиксированная панель управления внизу экрана -->
    <div class="fixed bottom-0 left-0 right-0 z-40 bg-white dark:bg-neutral-800 border-t border-neutral-200 dark:border-neutral-700 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 py-3 sm:py-4">
            <div class="flex items-center justify-between gap-3">
                <!-- Информация о текущем статусе (слева) -->
                <div class="flex items-center gap-2 min-w-0">
                    <!-- Статус-бейдж -->
                    <div class="flex-shrink-0">
                        <span
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold whitespace-nowrap"
                            :class="getStatusBadgeClass(session.status)"
                        >
                            <span
                                class="w-2 h-2 rounded-full animate-pulse"
                                :class="{
                                    'bg-success-600': session.status === 'active',
                                    'bg-warning-600': session.status === 'paused',
                                    'bg-neutral-500': session.status === 'planned',
                                    'bg-accent-600': session.status === 'completed'
                                }"
                            ></span>
                            {{ getStatusText(session.status) }}
                        </span>
                    </div>
                    <!-- Текущий блок (только на больших экранах) -->
                    <div v-if="currentBlock" class="min-w-0 hidden md:block">
                        <p class="text-xs text-neutral-600 dark:text-neutral-400 truncate">
                            {{ currentBlock.title }}
                        </p>
                    </div>
                </div>

                <!-- Кнопки управления (справа) -->
                <div class="flex items-center gap-2 flex-shrink-0">
                    <!-- Кнопка "Начать" или "Продолжить" -->
                    <button
                        v-if="['planned', 'paused'].includes(session.status)"
                        @click="$emit('start')"
                        :disabled="processing"
                        class="inline-flex items-center gap-1.5 px-4 sm:px-6 py-2 sm:py-2.5 bg-success-500 hover:bg-success-600 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed text-sm sm:text-base"
                    >
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                        </svg>
                        <span class="hidden sm:inline">{{ session.status === 'paused' ? 'Продолжить' : 'Начать' }}</span>
                        <span class="sm:hidden">{{ session.status === 'paused' ? 'Продолжить' : 'Старт' }}</span>
                    </button>

                    <!-- Кнопка "Следующее упражнение" (когда сессия активна, но нет текущего блока) -->
                    <button
                        v-if="session.status === 'active' && !currentBlock && hasPlannedBlocks"
                        @click="$emit('start-next-block')"
                        :disabled="processing"
                        class="inline-flex items-center gap-1.5 px-4 sm:px-6 py-2 sm:py-2.5 bg-success-500 hover:bg-success-600 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed text-sm sm:text-base animate-pulse"
                    >
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                        </svg>
                        <span class="hidden sm:inline">Следующее упражнение</span>
                        <span class="sm:hidden">Далее</span>
                    </button>

                    <!-- Кнопка "Пауза" -->
                    <button
                        v-if="session.status === 'active'"
                        @click="$emit('pause')"
                        :disabled="processing"
                        class="inline-flex items-center gap-1.5 px-3 sm:px-6 py-2 sm:py-2.5 bg-warning-500 hover:bg-warning-600 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed text-sm sm:text-base"
                    >
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM7 8a1 1 0 012 0v4a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v4a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        <span class="hidden xs:inline">Пауза</span>
                    </button>

                    <!-- Кнопка "Завершить" -->
                    <button
                        v-if="['active', 'paused'].includes(session.status)"
                        @click="confirmComplete"
                        :disabled="processing"
                        class="inline-flex items-center gap-1.5 px-4 sm:px-6 py-2 sm:py-2.5 bg-neutral-600 hover:bg-neutral-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed text-sm sm:text-base"
                    >
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        <span class="hidden sm:inline">Завершить</span>
                        <span class="sm:hidden">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Подтверждение завершения -->
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 transform translate-y-2"
            enter-to-class="opacity-100 transform translate-y-0"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100 transform translate-y-0"
            leave-to-class="opacity-0 transform translate-y-2"
        >
            <div
                v-if="showConfirmComplete"
                class="border-t border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-900/50 px-4 py-3"
            >
                <div class="max-w-7xl mx-auto flex items-center justify-between gap-3">
                    <p class="text-sm text-neutral-700 dark:text-neutral-300">
                        Завершить занятие досрочно?
                    </p>
                    <div class="flex items-center gap-2">
                        <button
                            @click="showConfirmComplete = false"
                            class="px-3 py-1.5 text-sm text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-neutral-100 transition-colors"
                        >
                            Отмена
                        </button>
                        <button
                            @click="handleComplete"
                            class="px-4 py-1.5 bg-danger-500 hover:bg-danger-600 text-white text-sm font-medium rounded-md shadow-sm transition-all"
                        >
                            Завершить
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';

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
    currentBlock?: SessionBlock | null
    processing?: boolean
}

const props = withDefaults(defineProps<Props>(), {
    processing: false,
    currentBlock: null
});

const emit = defineEmits<{
    start: []
    pause: []
    complete: []
    'start-next-block': []
}>();

const hasPlannedBlocks = computed(() => {
    return props.session.blocks.some(block => block.status === 'planned');
});

const showConfirmComplete = ref(false);

const getStatusText = (status: string): string => {
    const statusMap: Record<string, string> = {
        'planned': 'Запланировано',
        'active': 'Идет занятие',
        'paused': 'Приостановлено',
        'completed': 'Завершено',
        'cancelled': 'Отменено'
    };
    return statusMap[status] || status;
};

const getStatusBadgeClass = (status: string): string => {
    const badgeClasses: Record<string, string> = {
        'planned': 'bg-neutral-100 text-neutral-800 dark:bg-neutral-700 dark:text-neutral-300',
        'active': 'bg-success-100 text-success-800 dark:bg-success-900/40 dark:text-success-300',
        'paused': 'bg-warning-100 text-warning-800 dark:bg-warning-900/40 dark:text-warning-300',
        'completed': 'bg-accent-100 text-accent-800 dark:bg-accent-900/40 dark:text-accent-300',
        'cancelled': 'bg-danger-100 text-danger-800 dark:bg-danger-900/40 dark:text-danger-300'
    };
    return badgeClasses[status] || 'bg-neutral-100 text-neutral-800 dark:bg-neutral-700 dark:text-neutral-300';
};

const confirmComplete = () => {
    showConfirmComplete.value = true;
};

const handleComplete = () => {
    showConfirmComplete.value = false;
    emit('complete');
};
</script>
