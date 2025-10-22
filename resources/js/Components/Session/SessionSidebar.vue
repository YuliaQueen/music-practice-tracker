<template>
    <!-- Сайдбар для десктопа (lg+) -->
    <div class="hidden lg:block lg:sticky lg:top-0 lg:self-start w-96 bg-white dark:bg-neutral-800 border-l border-neutral-200 dark:border-neutral-700 shadow-xl overflow-y-auto max-h-screen">
        <div class="p-6 space-y-6">
            <!-- Заголовок с статусом -->
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">
                    Управление сессией
                </h3>
                <span
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold"
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

            <!-- Текущий блок с таймером -->
            <div v-if="currentBlock" class="bg-gradient-to-br from-primary-50/80 to-danger-50/80 dark:from-accent-900/30 dark:to-secondary-900/30 rounded-xl p-5 border border-primary-200 dark:border-accent-800/30">
                <!-- Информация о блоке -->
                <div class="mb-4">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="text-2xl">{{ getTypeIcon(currentBlock.type) }}</span>
                        <h4 class="text-base font-bold text-primary-800 dark:text-neutral-100">
                            {{ currentBlock.title }}
                        </h4>
                    </div>
                    <p v-if="currentBlock.description" class="text-xs text-primary-600 dark:text-neutral-300 mb-2">
                        {{ currentBlock.description }}
                    </p>
                    <div class="text-xs text-primary-500 dark:text-neutral-400">
                        {{ currentBlock.planned_duration }} мин запланировано
                    </div>
                </div>

                <!-- Круговой таймер -->
                <div class="relative w-32 h-32 mx-auto mb-4">
                    <svg class="w-full h-full transform -rotate-90" viewBox="0 0 100 100">
                        <circle
                            cx="50"
                            cy="50"
                            r="40"
                            stroke="currentColor"
                            stroke-width="6"
                            fill="none"
                            class="text-primary-200 dark:text-neutral-700"
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
                            class="text-accent-500 transition-all duration-1000 ease-in-out"
                            :class="{ 'text-danger-500': progress >= 100 }"
                        />
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <div
                            class="text-xl font-bold transition-colors"
                            :class="progress >= 100 ? 'text-danger-500 dark:text-danger-400' : 'text-primary-500 dark:text-accent-400'"
                        >
                            {{ formatTime(timeRemaining) }}
                        </div>
                        <div class="text-xs text-primary-500 dark:text-neutral-400">
                            {{ Math.round(progress) }}%
                        </div>
                    </div>
                </div>

                <!-- Линейный прогресс -->
                <div class="w-full bg-primary-200 dark:bg-neutral-700 rounded-full h-2 overflow-hidden">
                    <div
                        class="h-2 rounded-full transition-all duration-1000"
                        :class="progress >= 100 ? 'bg-gradient-to-r from-danger-400 to-danger-500' : 'bg-gradient-to-r from-primary-400 to-danger-500'"
                        :style="{ width: Math.min(progress, 100) + '%' }"
                    ></div>
                </div>
            </div>

            <!-- Управление временем -->
            <div class="bg-accent-50/50 dark:bg-accent-900/10 rounded-xl p-4 border border-accent-200 dark:border-accent-800/30">
                <div class="flex items-center justify-center gap-2 mb-4">
                    <svg class="w-5 h-5 text-accent-600 dark:text-accent-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h4 class="text-sm font-semibold text-accent-900 dark:text-accent-200">
                        Управление временем
                    </h4>
                </div>

                <!-- Выбор блока -->
                <div class="mb-3">
                    <select
                        :value="selectedBlockId"
                        @change="$emit('update:selectedBlockId', parseInt(($event.target as HTMLSelectElement).value))"
                        class="w-full text-sm border-neutral-300 focus:border-accent-500 focus:ring-accent-500 rounded-lg dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-100"
                    >
                        <option value="">Выберите упражнение</option>
                        <option v-for="block in blocks" :key="block.id" :value="block.id">
                            {{ block.title }} ({{ getBlockStatusText(block.status) }})
                        </option>
                    </select>
                </div>

                <!-- Кнопки добавления времени -->
                <div class="flex gap-2 mb-3">
                    <button
                        @click="$emit('extend', 5)"
                        :disabled="!selectedBlockId"
                        class="flex-1 px-3 py-2 bg-accent-500 text-white font-medium rounded-lg shadow hover:bg-accent-600 transition-colors text-xs disabled:opacity-50"
                    >
                        +5 мин
                    </button>
                    <button
                        @click="$emit('extend', 10)"
                        :disabled="!selectedBlockId"
                        class="flex-1 px-3 py-2 bg-accent-500 text-white font-medium rounded-lg shadow hover:bg-accent-600 transition-colors text-xs disabled:opacity-50"
                    >
                        +10 мин
                    </button>
                    <button
                        @click="$emit('extend', 15)"
                        :disabled="!selectedBlockId"
                        class="flex-1 px-3 py-2 bg-accent-500 text-white font-medium rounded-lg shadow hover:bg-accent-600 transition-colors text-xs disabled:opacity-50"
                    >
                        +15 мин
                    </button>
                </div>

                <!-- Кнопка перезапуска -->
                <Transition
                    enter-active-class="transition ease-out duration-200"
                    enter-from-class="opacity-0"
                    enter-to-class="opacity-100"
                >
                    <div v-if="selectedBlockId" class="pt-3 border-t border-accent-200 dark:border-accent-800/30">
                        <button
                            @click="$emit('restart')"
                            class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 bg-success-500 text-white font-medium rounded-lg shadow hover:bg-success-600 transition-colors text-sm"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Перезапустить таймер
                        </button>
                    </div>
                </Transition>
            </div>

            <!-- Управление сессией -->
            <div class="space-y-3">
                <button
                    v-if="['planned', 'paused'].includes(session.status)"
                    @click="$emit('start')"
                    :disabled="processing"
                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 bg-success-500 hover:bg-success-600 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all disabled:opacity-50"
                >
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                    </svg>
                    {{ session.status === 'paused' ? 'Продолжить' : 'Начать' }} занятие
                </button>

                <button
                    v-if="session.status === 'active'"
                    @click="$emit('pause')"
                    :disabled="processing"
                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 bg-warning-500 hover:bg-warning-600 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all disabled:opacity-50"
                >
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM7 8a1 1 0 012 0v4a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v4a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    Приостановить
                </button>

                <button
                    v-if="['active', 'paused'].includes(session.status)"
                    @click="confirmComplete"
                    :disabled="processing"
                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 bg-neutral-600 hover:bg-neutral-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all disabled:opacity-50"
                >
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    Завершить занятие
                </button>

                <!-- Кнопка настроек звука -->
                <button
                    @click="$emit('toggle-sound-settings')"
                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 border border-neutral-300 dark:border-neutral-600 text-neutral-700 dark:text-neutral-300 font-medium rounded-lg hover:bg-neutral-50 dark:hover:bg-neutral-700 transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                    </svg>
                    Настройки звука
                </button>
            </div>

            <!-- Подтверждение завершения -->
            <Transition
                enter-active-class="transition ease-out duration-200"
                enter-from-class="opacity-0 transform scale-95"
                enter-to-class="opacity-100 transform scale-100"
            >
                <div
                    v-if="showConfirmComplete"
                    class="bg-danger-50 dark:bg-danger-900/20 border border-danger-200 dark:border-danger-800 rounded-lg p-4"
                >
                    <p class="text-sm text-danger-800 dark:text-danger-300 mb-3">
                        Завершить занятие досрочно?
                    </p>
                    <div class="flex gap-2">
                        <button
                            @click="showConfirmComplete = false"
                            class="flex-1 px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded-md hover:bg-neutral-50 dark:hover:bg-neutral-700 transition-colors"
                        >
                            Отмена
                        </button>
                        <button
                            @click="handleComplete"
                            class="flex-1 px-3 py-2 bg-danger-500 hover:bg-danger-600 text-white text-sm font-medium rounded-md transition-all"
                        >
                            Завершить
                        </button>
                    </div>
                </div>
            </Transition>
        </div>
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
    blocks: SessionBlock[]
    currentBlock?: SessionBlock | null
    selectedBlockId: number | null
    timeRemaining: number
    progress: number
    isRunning: boolean
    canStart: boolean
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
    'update:selectedBlockId': [value: number]
    extend: [minutes: number]
    restart: []
    'toggle-sound-settings': []
}>();

const showConfirmComplete = ref(false);
const circumference = 2 * Math.PI * 40;

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

const getTypeIcon = (type: string): string => {
    const icons: Record<string, string> = {
        warmup: '🔥',
        technique: '⚡',
        repertoire: '🎵',
        improvisation: '🎨',
        sight_reading: '👀',
        theory: '📚',
        break: '☕',
        custom: '⭐',
    };
    return icons[type] || '⭐';
};

const getBlockStatusText = (status: string): string => {
    const statusMap: Record<string, string> = {
        completed: 'завершено',
        active: 'активно',
        paused: 'приостановлено',
        planned: 'запланировано',
    };
    return statusMap[status] || status;
};

const formatTime = (seconds: number): string => {
    const mins = Math.floor(Math.abs(seconds) / 60);
    const secs = Math.abs(seconds) % 60;
    const sign = seconds < 0 ? '-' : '';
    return `${sign}${mins}:${secs.toString().padStart(2, '0')}`;
};

const confirmComplete = () => {
    showConfirmComplete.value = true;
};

const handleComplete = () => {
    showConfirmComplete.value = false;
    emit('complete');
};
</script>
