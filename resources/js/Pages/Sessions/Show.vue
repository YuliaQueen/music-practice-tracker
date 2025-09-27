<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ session.title }}
                </h2>
                <div class="flex space-x-2">
                    <span :class="statusBadgeClass">{{ statusLabel }}</span>
                </div>
            </div>
        </template>

        <div class="py-4 sm:py-6">
            <div class="max-w-7xl mx-auto sm:px-4 lg:px-6">
                <!-- Информация о сессии -->
                <div class="bg-gradient-to-br from-white to-gray-50 overflow-hidden shadow-lg sm:rounded-xl mb-4 border border-gray-100">
                    <div class="p-4 sm:p-6 text-gray-900">
                        <!-- Статистика сессии -->
                        <div class="grid grid-cols-3 gap-4 sm:gap-6 mb-4">
                            <div class="text-center">
                                <div class="inline-flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 bg-blue-100 rounded-xl mb-2">
                                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <h3 class="text-xs sm:text-sm font-medium text-gray-500 mb-1">Запланировано</h3>
                                <p class="text-lg sm:text-xl font-bold text-gray-900">{{ session.planned_duration }} мин</p>
                            </div>
                            <div class="text-center">
                                <div class="inline-flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 bg-green-100 rounded-xl mb-2">
                                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <h3 class="text-xs sm:text-sm font-medium text-gray-500 mb-1">Фактически</h3>
                                <p class="text-lg sm:text-xl font-bold text-gray-900">
                                    {{ session.actual_duration || '—' }} мин
                                </p>
                            </div>
                            <div class="text-center">
                                <div class="inline-flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 bg-purple-100 rounded-xl mb-2">
                                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                </div>
                                <h3 class="text-xs sm:text-sm font-medium text-gray-500 mb-1">Прогресс</h3>
                                <p class="text-lg sm:text-xl font-bold text-gray-900">{{ progressPercentage }}%</p>
                            </div>
                        </div>

                        <!-- Описание сессии -->
                        <div v-if="session.description" class="mb-8 p-6 bg-white rounded-2xl border border-gray-100">
                            <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Описание
                            </h3>
                            <p class="text-gray-700 leading-relaxed">{{ session.description }}</p>
                        </div>

                        <!-- Управление сессией -->
                        <div class="flex flex-wrap justify-center gap-4">
                            <button
                                v-if="session.status === 'planned'"
                                @click="startSession"
                                :disabled="form.processing"
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
                                @click="pauseSession"
                                :disabled="form.processing"
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
                                @click="startSession"
                                :disabled="form.processing"
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
                                @click="completeSession"
                                :disabled="form.processing"
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

                <!-- Таймер и текущий блок -->
                <div v-if="currentBlock" class="bg-gradient-to-br from-indigo-50 to-purple-50 overflow-hidden shadow-lg sm:rounded-xl mb-4 border border-indigo-100">
                    <div class="p-4 sm:p-6">
                        <div class="flex items-center justify-between">
                            <!-- Информация о блоке -->
                            <div class="flex-1">
                                <div class="flex items-center space-x-3 mb-2">
                                    <span class="text-2xl">{{ getTypeIcon(currentBlock.type) }}</span>
                                    <h3 class="text-lg sm:text-xl font-bold text-gray-900">
                                        {{ currentBlock.title }}
                                    </h3>
                                </div>
                                <p v-if="currentBlock.description" class="text-sm text-gray-600 mb-2">
                                    {{ currentBlock.description }}
                                </p>
                                <div class="text-sm text-gray-500">
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
                                        class="text-gray-200"
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
                                        :stroke-dashoffset="circumference - (currentBlockProgress / 100) * circumference"
                                        class="text-indigo-500 transition-all duration-1000 ease-in-out"
                                        :class="{ 'text-red-500': currentBlockProgress >= 100 }"
                                    />
                                </svg>
                                
                                <!-- Время в центре -->
                                <div class="absolute inset-0 flex flex-col items-center justify-center">
                                    <div 
                                        class="text-sm sm:text-lg font-bold transition-colors duration-300"
                                        :class="currentBlockProgress >= 100 ? 'text-red-600' : 'text-indigo-600'"
                                    >
                                        {{ formatTime(currentBlockTime) }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ Math.round(currentBlockProgress) }}%
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Линейный прогресс-бар -->
                        <div class="mt-4">
                            <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                                <div
                                    class="h-2 rounded-full transition-all duration-1000 ease-out relative"
                                    :class="currentBlockProgress >= 100 ? 'bg-gradient-to-r from-red-400 to-red-600' : 'bg-gradient-to-r from-indigo-400 to-indigo-600'"
                                    :style="{ width: Math.min(currentBlockProgress, 100) + '%' }"
                                >
                                    <div class="absolute inset-0 bg-white opacity-20 animate-pulse"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Управление таймером -->
                        <div class="flex justify-center gap-2 mt-4">
                            <button
                                v-if="!timerRunning && currentBlock"
                                @click="startBlock(currentBlock)"
                                :disabled="session.status !== 'active'"
                                class="px-4 py-2 bg-green-500 text-white font-medium rounded-lg shadow hover:bg-green-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-sm"
                            >
                                ▶ Запустить
                            </button>
                            
                            <button
                                v-if="timerRunning && currentBlock"
                                @click="pauseBlock(currentBlock)"
                                class="px-4 py-2 bg-yellow-500 text-white font-medium rounded-lg shadow hover:bg-yellow-600 transition-colors text-sm"
                            >
                                ⏸ Пауза
                            </button>
                            
                            <button
                                @click="completeCurrentBlock"
                                :disabled="session.status !== 'active'"
                                class="px-4 py-2 bg-red-500 text-white font-medium rounded-lg shadow hover:bg-red-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-sm"
                            >
                                ✓ Завершить
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Список блоков -->
                <div class="bg-white overflow-hidden shadow-lg sm:rounded-xl border border-gray-100">
                    <div class="p-4 sm:p-6">
                        <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                            </svg>
                            Упражнения
                        </h3>
                        
                        <div class="space-y-2 sm:space-y-3">
                            <div
                                v-for="(block, index) in session.blocks"
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
                                            <h4 class="text-sm sm:text-base font-semibold text-gray-900 truncate">
                                                {{ block.title }}
                                            </h4>
                                            <div class="flex items-center space-x-2 mt-1">
                                                <span :class="getBlockBadgeClass(block.status)">
                                                    {{ getBlockStatusLabel(block.status) }}
                                                </span>
                                                <span class="text-xs text-gray-500">
                                                    {{ block.planned_duration }} мин
                                                </span>
                                                <span v-if="block.actual_duration" class="text-xs text-gray-500">
                                                    ({{ block.actual_duration }} мин)
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="flex gap-1 ml-3">
                                        <button
                                            v-if="block.status === 'planned' && session.status === 'active'"
                                            @click="startBlock(block)"
                                            class="px-2 py-1 bg-green-500 text-white text-xs rounded hover:bg-green-600 transition-colors"
                                        >
                                            ▶
                                        </button>
                                        
                                        <button
                                            v-if="block.status === 'active' && session.status === 'active'"
                                            @click="pauseBlock(block)"
                                            class="px-2 py-1 bg-yellow-500 text-white text-xs rounded hover:bg-yellow-600 transition-colors"
                                        >
                                            ⏸
                                        </button>
                                        
                                        <button
                                            v-if="block.status === 'paused' && session.status === 'active'"
                                            @click="startBlock(block)"
                                            class="px-2 py-1 bg-green-500 text-white text-xs rounded hover:bg-green-600 transition-colors"
                                        >
                                            ▶
                                        </button>
                                        
                                        <button
                                            v-if="['active', 'paused'].includes(block.status) && session.status === 'active'"
                                            @click="completeBlock(block)"
                                            class="px-2 py-1 bg-red-500 text-white text-xs rounded hover:bg-red-600 transition-colors"
                                        >
                                            ✓
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import { computed, ref, onMounted, onUnmounted, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import DangerButton from '@/Components/DangerButton.vue'

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
    started_at: string | null
    completed_at: string | null
    blocks: SessionBlock[]
}

interface Props {
    session: Session
}

const props = defineProps<Props>()

const form = useForm({})

// Таймер
const timerRunning = ref(false)
const currentBlockTime = ref(0) // Оставшееся время в секундах
const timerInterval = ref<number | null>(null)
const startTime = ref<number | null>(null)
const blockStartTime = ref<number | null>(null) // Время начала блока

const currentBlock = computed(() => {
    return props.session.blocks.find(block => block.status === 'active')
})

const currentBlockProgress = computed(() => {
    if (!currentBlock.value) return 0
    const plannedSeconds = currentBlock.value.planned_duration * 60
    const remainingSeconds = currentBlockTime.value
    const elapsedSeconds = plannedSeconds - remainingSeconds
    return Math.min((elapsedSeconds / plannedSeconds) * 100, 100)
})

const circumference = computed(() => {
    return 2 * Math.PI * 45 // радиус 45
})

const statusLabel = computed(() => {
    const statusMap = {
        planned: 'Запланировано',
        active: 'Активно',
        paused: 'Приостановлено',
        completed: 'Завершено',
        cancelled: 'Отменено',
    }
    return statusMap[props.session.status as keyof typeof statusMap] || props.session.status
})

const statusBadgeClass = computed(() => {
    const baseClass = 'px-3 py-1 rounded-full text-sm font-medium'
    const statusClasses = {
        planned: 'bg-gray-100 text-gray-800',
        active: 'bg-green-100 text-green-800',
        paused: 'bg-yellow-100 text-yellow-800',
        completed: 'bg-blue-100 text-blue-800',
        cancelled: 'bg-red-100 text-red-800',
    }
    return `${baseClass} ${statusClasses[props.session.status as keyof typeof statusClasses] || 'bg-gray-100 text-gray-800'}`
})

const progressPercentage = computed(() => {
    const completedBlocks = props.session.blocks.filter(block => block.status === 'completed').length
    const totalBlocks = props.session.blocks.length
    return totalBlocks > 0 ? Math.round((completedBlocks / totalBlocks) * 100) : 0
})

// Методы таймера
const startTimer = () => {
    if (!currentBlock.value) return
    
    timerRunning.value = true
    
    // Если таймер запускается впервые для этого блока
    if (!startTime.value || !blockStartTime.value) {
        startTime.value = Date.now()
        blockStartTime.value = Date.now()
        // Инициализируем таймер с запланированным временем
        currentBlockTime.value = currentBlock.value.planned_duration * 60
    } else {
        // При возобновлении сохраняем уже прошедшее время
        // Таймер продолжит отсчет с того места, где остановился
        const elapsed = Math.floor((Date.now() - startTime.value) / 1000)
        const plannedSeconds = currentBlock.value.planned_duration * 60
        const remaining = Math.max(0, plannedSeconds - elapsed)
        currentBlockTime.value = remaining
    }
    
    timerInterval.value = setInterval(() => {
        if (!currentBlock.value) return
        
        const now = Date.now()
        const elapsed = Math.floor((now - (startTime.value || 0)) / 1000)
        const plannedSeconds = currentBlock.value.planned_duration * 60
        const remaining = Math.max(0, plannedSeconds - elapsed)
        
        currentBlockTime.value = remaining
        
        // Автоматически завершаем блок при достижении нуля
        if (remaining <= 0) {
            completeCurrentBlock()
        }
    }, 100) // Обновляем каждые 100мс для более плавного отображения
}

const pauseTimer = () => {
    timerRunning.value = false
    if (timerInterval.value) {
        clearInterval(timerInterval.value)
        timerInterval.value = null
    }
    // Просто останавливаем таймер, время паузы будет учтено при возобновлении
}

const resetTimer = () => {
    pauseTimer()
    currentBlockTime.value = 0
    startTime.value = null
    blockStartTime.value = null
}

const formatTime = (seconds: number) => {
    const minutes = Math.floor(seconds / 60)
    const remainingSeconds = seconds % 60
    return `${minutes.toString().padStart(2, '0')}:${remainingSeconds.toString().padStart(2, '0')}`
}

// Методы управления блоками
const startBlock = (block: SessionBlock) => {
    const blockForm = useForm({
        status: 'active',
        // Для возобновления не обновляем started_at, чтобы сохранить оригинальное время начала
        ...(block.status === 'planned' ? { started_at: new Date().toISOString() } : {}),
    })
    blockForm.patch(route('sessions.blocks.update', { session: props.session.id, block: block.id }), {
        preserveScroll: true,
        onSuccess: () => {
            // После успешного обновления запускаем таймер
            if (props.session.status === 'active') {
                startTimer()
            }
        }
    })
}

const pauseBlock = (block: SessionBlock) => {
    const blockForm = useForm({
        status: 'paused',
    })
    blockForm.patch(route('sessions.blocks.update', { session: props.session.id, block: block.id }), {
        preserveScroll: true,
        onSuccess: () => {
            // После успешного обновления останавливаем таймер
            pauseTimer()
        }
    })
}

const completeBlock = (block: SessionBlock) => {
    // Вычисляем фактическое время выполнения
    const actualDuration = blockStartTime.value 
        ? Math.round((Date.now() - blockStartTime.value) / 1000 / 60) // в минутах
        : block.planned_duration
    
    const blockForm = useForm({
        status: 'completed',
        actual_duration: actualDuration,
        started_at: blockStartTime.value ? new Date(blockStartTime.value).toISOString() : null,
        completed_at: new Date().toISOString(),
    })
    blockForm.patch(route('sessions.blocks.update', { session: props.session.id, block: block.id }), {
        preserveScroll: true,
        onSuccess: () => {
            // После успешного обновления сбрасываем таймер
            resetTimer()
        }
    })
}

const completeCurrentBlock = () => {
    if (currentBlock.value) {
        completeBlock(currentBlock.value)
    }
}

// Методы управления сессией
const startSession = () => {
    form.post(route('sessions.start', props.session.id), {
        preserveScroll: true,
    })
}

const pauseSession = () => {
    form.post(route('sessions.pause', props.session.id), {
        preserveScroll: true,
    })
}

const completeSession = () => {
    form.post(route('sessions.complete', props.session.id), {
        preserveScroll: true,
    })
}

// Утилиты
const getTypeIcon = (type: string) => {
    const icons = {
        warmup: '🔥',
        technique: '⚡',
        repertoire: '🎵',
        improvisation: '🎨',
        sight_reading: '👀',
        theory: '📚',
        break: '☕',
        custom: '⭐',
    }
    return icons[type as keyof typeof icons] || '❓'
}

const getBlockStatusLabel = (status: string) => {
    const labels = {
        planned: 'Запланировано',
        active: 'Активно',
        paused: 'Приостановлено',
        completed: 'Завершено',
        skipped: 'Пропущено',
    }
    return labels[status as keyof typeof labels] || status
}

const getBlockStatusClass = (status: string) => {
    const classes = {
        planned: 'border-gray-200 bg-gray-50 hover:border-gray-300',
        active: 'border-indigo-300 bg-gradient-to-r from-indigo-50 to-purple-50 hover:border-indigo-400',
        paused: 'border-yellow-300 bg-gradient-to-r from-yellow-50 to-orange-50 hover:border-yellow-400',
        completed: 'border-green-300 bg-gradient-to-r from-green-50 to-emerald-50 hover:border-green-400',
        skipped: 'border-gray-200 bg-gray-100 hover:border-gray-300',
    }
    return classes[status as keyof typeof classes] || 'border-gray-200 bg-gray-50'
}

const getBlockProgressClass = (status: string) => {
    const classes = {
        planned: 'bg-gray-300',
        active: 'bg-gradient-to-b from-indigo-400 to-purple-500',
        paused: 'bg-gradient-to-b from-yellow-400 to-orange-500',
        completed: 'bg-gradient-to-b from-green-400 to-emerald-500',
        skipped: 'bg-gray-400',
    }
    return classes[status as keyof typeof classes] || 'bg-gray-300'
}

const getBlockIconBgClass = (status: string) => {
    const classes = {
        planned: 'bg-gray-100',
        active: 'bg-gradient-to-br from-indigo-100 to-purple-100',
        paused: 'bg-gradient-to-br from-yellow-100 to-orange-100',
        completed: 'bg-gradient-to-br from-green-100 to-emerald-100',
        skipped: 'bg-gray-100',
    }
    return classes[status as keyof typeof classes] || 'bg-gray-100'
}

const getBlockBadgeClass = (status: string) => {
    const baseClass = 'px-2 py-1 rounded-full text-xs font-medium'
    const statusClasses = {
        planned: 'bg-gray-100 text-gray-800',
        active: 'bg-indigo-100 text-indigo-800',
        paused: 'bg-yellow-100 text-yellow-800',
        completed: 'bg-green-100 text-green-800',
        skipped: 'bg-gray-100 text-gray-600',
    }
    return `${baseClass} ${statusClasses[status as keyof typeof statusClasses] || 'bg-gray-100 text-gray-800'}`
}

// Жизненный цикл
onMounted(() => {
    // Если есть активный блок, запускаем таймер
    if (currentBlock.value && props.session.status === 'active') {
        startTimer()
    }
})

onUnmounted(() => {
    pauseTimer()
})

// Следим за изменениями активного блока
watch(currentBlock, (newBlock, oldBlock) => {
    if (oldBlock && newBlock && oldBlock.id !== newBlock.id) {
        // Переключились на новый блок, сбрасываем таймер
        resetTimer()
    }
    
    // Если появился новый активный блок, запускаем таймер
    if (newBlock && props.session.status === 'active' && !timerRunning.value) {
        startTimer()
    }
})

// Следим за изменениями в блоках для автоматического запуска таймера
watch(() => props.session.blocks, (newBlocks, oldBlocks) => {
    const activeBlock = newBlocks.find(block => block.status === 'active')
    
    if (activeBlock && props.session.status === 'active' && !timerRunning.value) {
        // Если есть активный блок и сессия активна, запускаем таймер
        startTimer()
    } else if (!activeBlock && timerRunning.value) {
        // Если нет активных блоков, останавливаем таймер
        pauseTimer()
    }
}, { deep: true })
</script>