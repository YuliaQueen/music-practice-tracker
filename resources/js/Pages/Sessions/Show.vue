<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-amber-800 dark:text-gray-200 leading-tight">
                    {{ session.title }}
                </h2>
                <div class="flex space-x-2">
                    <span :class="statusBadgeClass">{{ statusLabel }}</span>
                    <DangerButton
                        @click="deleteSession"
                        size="sm"
                        class="text-xs"
                    >
                        🗑️ Удалить
                    </DangerButton>
                </div>
            </div>
        </template>

        <div class="py-4 sm:py-6">
            <div class="max-w-7xl mx-auto sm:px-4 lg:px-6">
                <!-- Информация о сессии -->
                <div class="bg-gradient-to-br from-amber-50 to-orange-50 dark:from-gray-800 dark:to-gray-900 overflow-hidden shadow-lg sm:rounded-xl mb-4 border border-amber-200 dark:border-gray-700">
                    <div class="p-4 sm:p-6 text-amber-900 dark:text-gray-100">
                        <!-- Статистика сессии -->
                        <div class="grid grid-cols-3 gap-4 sm:gap-6 mb-4">
                            <div class="text-center">
                                <div class="inline-flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 bg-amber-100 dark:bg-blue-900 rounded-xl mb-2 shadow-sm">
                                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-amber-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <h3 class="text-xs sm:text-sm font-medium text-amber-700 dark:text-gray-400 mb-1">Запланировано</h3>
                                <p class="text-lg sm:text-xl font-bold text-amber-900 dark:text-gray-100">{{ session.planned_duration }} мин</p>
                            </div>
                            <div class="text-center">
                                <div class="inline-flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 bg-orange-100 dark:bg-green-900 rounded-xl mb-2 shadow-sm">
                                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-orange-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <h3 class="text-xs sm:text-sm font-medium text-amber-700 dark:text-gray-400 mb-1">Фактически</h3>
                                <p class="text-lg sm:text-xl font-bold text-amber-900 dark:text-gray-100">
                                    {{ session.actual_duration || '—' }} мин
                                </p>
                            </div>
                            <div class="text-center">
                                <div class="inline-flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 bg-red-100 dark:bg-purple-900 rounded-xl mb-2 shadow-sm">
                                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-red-500 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                </div>
                                <h3 class="text-xs sm:text-sm font-medium text-amber-700 dark:text-gray-400 mb-1">Прогресс</h3>
                                <p class="text-lg sm:text-xl font-bold text-amber-900 dark:text-gray-100">{{ progressPercentage }}%</p>
                            </div>
                        </div>

                        <!-- Описание сессии -->
                        <div v-if="session.description" class="mb-8 p-6 bg-orange-50/80 dark:bg-gray-800 rounded-2xl border border-orange-200 dark:border-gray-700 shadow-sm">
                            <h3 class="text-lg font-semibold text-amber-800 dark:text-gray-100 mb-3 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-amber-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Описание
                            </h3>
                            <p class="text-amber-700 dark:text-gray-300 leading-relaxed">{{ session.description }}</p>
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
                <div v-if="currentBlock" class="bg-gradient-to-br from-orange-50/80 to-red-50/80 dark:from-indigo-900 dark:to-purple-900 overflow-hidden shadow-lg sm:rounded-xl mb-4 border border-orange-200 dark:border-indigo-800">
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
                                        :stroke-dashoffset="circumference - (currentBlockProgress / 100) * circumference"
                                        class="text-indigo-500 transition-all duration-1000 ease-in-out"
                                        :class="{ 'text-red-500': currentBlockProgress >= 100 }"
                                    />
                                </svg>
                                
                                <!-- Время в центре -->
                                <div class="absolute inset-0 flex flex-col items-center justify-center">
                                    <div 
                                        class="text-sm sm:text-lg font-bold transition-colors duration-300"
                                        :class="currentBlockProgress >= 100 ? 'text-red-500 dark:text-red-400' : 'text-orange-500 dark:text-indigo-400'"
                                    >
                                        {{ formatTime(currentBlockTime) }}
                                    </div>
                                    <div class="text-xs text-orange-500 dark:text-gray-400">
                                        {{ Math.round(currentBlockProgress) }}%
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Линейный прогресс-бар -->
                        <div class="mt-4">
                            <div class="w-full bg-orange-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden shadow-inner">
                                <div
                                    class="h-2 rounded-full transition-all duration-1000 ease-out relative"
                                    :class="currentBlockProgress >= 100 ? 'bg-gradient-to-r from-red-400 to-red-500' : 'bg-gradient-to-r from-orange-400 to-red-500'"
                                    :style="{ width: Math.min(currentBlockProgress, 100) + '%' }"
                                >
                                    <div class="absolute inset-0 bg-white dark:bg-gray-300 opacity-30 animate-pulse"></div>
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
                            
                            <!-- Кнопка управления звуками -->
                            <button
                                @click="toggleSoundSettings"
                                @dblclick="showSoundSettings = true"
                                :class="soundSettings.enabled ? 'bg-blue-500 hover:bg-blue-600' : 'bg-gray-500 hover:bg-gray-600'"
                                class="px-3 py-2 text-white font-medium rounded-lg shadow transition-colors text-sm"
                                :title="soundSettings.enabled ? 'Звуки включены (двойной клик для настроек)' : 'Звуки выключены (двойной клик для настроек)'"
                            >
                                {{ soundSettings.enabled ? '🔊' : '🔇' }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Список блоков -->
                <div class="bg-orange-50/90 dark:bg-gray-800 overflow-hidden shadow-lg sm:rounded-xl border border-orange-200 dark:border-gray-700">
                    <div class="p-4 sm:p-6">
                        <h3 class="text-lg sm:text-xl font-bold text-orange-800 dark:text-gray-100 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-orange-500 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                            <h4 class="text-sm sm:text-base font-semibold text-orange-800 dark:text-gray-100 truncate">
                                                {{ block.title }}
                                            </h4>
                                            <div class="flex items-center space-x-2 mt-1">
                                                <span :class="getBlockBadgeClass(block.status)">
                                                    {{ getBlockStatusLabel(block.status) }}
                                                </span>
                                                <span class="text-xs text-orange-500 dark:text-gray-400">
                                                    {{ block.planned_duration }} мин
                                                </span>
                                                <span v-if="block.actual_duration" class="text-xs text-orange-500 dark:text-gray-400">
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
        
        <!-- Модальное окно настроек звука -->
        <div v-if="showSoundSettings" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md w-full mx-4">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Настройки звука</h3>
                    <button @click="showSoundSettings = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        ✕
                    </button>
                </div>
                
                <div class="space-y-4">
                    <!-- Общее включение/выключение звуков -->
                    <div class="flex items-center justify-between">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Включить звуки</label>
                        <input
                            type="checkbox"
                            v-model="soundSettings.enabled"
                            class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 dark:text-indigo-400 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700"
                        />
                    </div>
                    
                    <!-- Громкость -->
                    <div class="flex items-center justify-between">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Громкость</label>
                        <input
                            type="range"
                            min="0"
                            max="1"
                            step="0.1"
                            v-model="soundSettings.volume"
                            :disabled="!soundSettings.enabled"
                            class="w-24"
                        />
                        <span class="text-sm text-gray-500 dark:text-gray-400 w-8">{{ Math.round(soundSettings.volume * 100) }}%</span>
                    </div>
                    
                    <!-- Звук начала -->
                    <div class="flex items-center justify-between">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Звук начала</label>
                        <input
                            type="checkbox"
                            v-model="soundSettings.startSound"
                            :disabled="!soundSettings.enabled"
                            class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 dark:text-indigo-400 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700"
                        />
                    </div>
                    
                    <!-- Звук паузы -->
                    <div class="flex items-center justify-between">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Звук паузы</label>
                        <input
                            type="checkbox"
                            v-model="soundSettings.pauseSound"
                            :disabled="!soundSettings.enabled"
                            class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 dark:text-indigo-400 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700"
                        />
                    </div>
                    
                    <!-- Звук завершения -->
                    <div class="flex items-center justify-between">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Звук завершения</label>
                        <input
                            type="checkbox"
                            v-model="soundSettings.completeSound"
                            :disabled="!soundSettings.enabled"
                            class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 dark:text-indigo-400 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700"
                        />
                    </div>
                    
                    <!-- Звук предупреждения -->
                    <div class="flex items-center justify-between">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Звук предупреждения</label>
                        <input
                            type="checkbox"
                            v-model="soundSettings.warningSound"
                            :disabled="!soundSettings.enabled"
                            class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 dark:text-indigo-400 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700"
                        />
                    </div>
                </div>
                
                <div class="flex justify-end gap-2 mt-6">
                    <button
                        @click="showSoundSettings = false"
                        class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 transition-colors"
                    >
                        Отмена
                    </button>
                    <button
                        @click="saveSoundSettings"
                        class="px-4 py-2 bg-indigo-500 dark:bg-indigo-600 text-white rounded-lg hover:bg-indigo-600 dark:hover:bg-indigo-700 transition-colors"
                    >
                        Сохранить
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import { computed, ref, onMounted, onUnmounted, watch } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import DangerButton from '@/Components/DangerButton.vue'
import { useTimerSounds } from '@/composables/useTimerSounds'

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

// Звуки таймера
const {
  settings: soundSettings,
  playStartSound,
  playPauseSound,
  playCompleteSound,
  playWarningSound,
  playTimeUpSound,
  playBlockSwitchSound,
  loadSettings: loadSoundSettings,
} = useTimerSounds()

// Таймер
const timerRunning = ref(false)
const currentBlockTime = ref(0) // Оставшееся время в секундах
const timerInterval = ref<number | null>(null)
const startTime = ref<number | null>(null)
const blockStartTime = ref<number | null>(null) // Время начала блока
const warningPlayed = ref(false) // Флаг для предотвращения повторного воспроизведения предупреждения
const showSoundSettings = ref(false) // Показать модальное окно настроек звука

const currentBlock = computed(() => {
    return props.session.blocks.find(block => block.status === 'active')
})

// Ключи для localStorage
const TIMER_STATE_KEY = 'timer-state'
const TIMER_SESSION_KEY = 'timer-session-id'

// Сохранение состояния таймера
const saveTimerState = () => {
    if (!currentBlock.value || !timerRunning.value) return
    
    const timerState = {
        sessionId: props.session.id,
        blockId: currentBlock.value.id,
        startTime: startTime.value,
        blockStartTime: blockStartTime.value,
        remainingTime: currentBlockTime.value,
        plannedDuration: currentBlock.value.planned_duration,
        timestamp: Date.now()
    }
    
    localStorage.setItem(TIMER_STATE_KEY, JSON.stringify(timerState))
    localStorage.setItem(TIMER_SESSION_KEY, props.session.id.toString())
}

// Восстановление состояния таймера
const restoreTimerState = () => {
    try {
        const savedState = localStorage.getItem(TIMER_STATE_KEY)
        const savedSessionId = localStorage.getItem(TIMER_SESSION_KEY)
        
        if (!savedState || !savedSessionId) return false
        
        const timerState = JSON.parse(savedState)
        
        // Проверяем, что это та же сессия
        if (timerState.sessionId !== props.session.id) {
            clearTimerState()
            return false
        }
        
        // Проверяем, что блок все еще активен
        const savedBlock = props.session.blocks.find(block => block.id === timerState.blockId)
        if (!savedBlock || savedBlock.status !== 'active') {
            clearTimerState()
            return false
        }
        
        // Проверяем, что состояние не слишком старое (максимум 24 часа)
        const maxAge = 24 * 60 * 60 * 1000 // 24 часа в миллисекундах
        if (Date.now() - timerState.timestamp > maxAge) {
            clearTimerState()
            return false
        }
        
        // Восстанавливаем состояние
        startTime.value = timerState.startTime
        blockStartTime.value = timerState.blockStartTime
        
        // Вычисляем оставшееся время с учетом прошедшего времени
        const now = Date.now()
        const elapsed = Math.floor((now - timerState.startTime) / 1000)
        const plannedSeconds = timerState.plannedDuration * 60
        const remaining = Math.max(0, plannedSeconds - elapsed)
        
        currentBlockTime.value = remaining
        
        // Если время еще не истекло, запускаем таймер
        if (remaining > 0) {
            timerRunning.value = true
            startTimerInterval()
            return true
        } else {
            // Время истекло, завершаем блок
            clearTimerState()
            completeCurrentBlock()
            return false
        }
        
    } catch (error) {
        console.warn('Ошибка при восстановлении состояния таймера:', error)
        clearTimerState()
        return false
    }
}

// Очистка состояния таймера
const clearTimerState = () => {
    localStorage.removeItem(TIMER_STATE_KEY)
    localStorage.removeItem(TIMER_SESSION_KEY)
}

// Запуск интервала таймера (отдельная функция)
const startTimerInterval = () => {
    if (timerInterval.value) {
        clearInterval(timerInterval.value)
    }
    
    timerInterval.value = setInterval(() => {
        if (!currentBlock.value) return
        
        const now = Date.now()
        const elapsed = Math.floor((now - (startTime.value || 0)) / 1000)
        const plannedSeconds = currentBlock.value.planned_duration * 60
        const remaining = Math.max(0, plannedSeconds - elapsed)
        
        currentBlockTime.value = remaining
        
        // Звук предупреждения за 30 секунд до окончания
        if (remaining <= 30 && remaining > 0 && !warningPlayed.value) {
            playWarningSound()
            warningPlayed.value = true
        }
        
        // Автоматически завершаем блок при достижении нуля
        if (remaining <= 0) {
            playTimeUpSound()
            completeCurrentBlock()
        }
        
        // Сохраняем состояние каждые 5 секунд
        if (elapsed % 5 === 0) {
            saveTimerState()
        }
    }, 100) // Обновляем каждые 100мс для более плавного отображения
}

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
    const baseClass = 'px-3 py-1 rounded-full text-sm font-medium shadow-sm'
    const statusClasses = {
        planned: 'bg-amber-100 dark:bg-gray-700 text-amber-700 dark:text-gray-200',
        active: 'bg-orange-100 dark:bg-green-900 text-orange-700 dark:text-green-200',
        paused: 'bg-yellow-100 dark:bg-yellow-900 text-yellow-700 dark:text-yellow-200',
        completed: 'bg-red-100 dark:bg-blue-900 text-red-700 dark:text-blue-200',
        cancelled: 'bg-red-200 dark:bg-red-900 text-red-800 dark:text-red-200',
    }
    return `${baseClass} ${statusClasses[props.session.status as keyof typeof statusClasses] || 'bg-amber-100 dark:bg-gray-700 text-amber-700 dark:text-gray-200'}`
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
    warningPlayed.value = false // Сбрасываем флаг предупреждения
    
    // Если таймер запускается впервые для этого блока
    if (!startTime.value || !blockStartTime.value) {
        startTime.value = Date.now()
        blockStartTime.value = Date.now()
        // Инициализируем таймер с запланированным временем
        currentBlockTime.value = currentBlock.value.planned_duration * 60
        // Воспроизводим звук начала
        playStartSound()
    } else {
        // При возобновлении сохраняем уже прошедшее время
        // Таймер продолжит отсчет с того места, где остановился
        const elapsed = Math.floor((Date.now() - startTime.value) / 1000)
        const plannedSeconds = currentBlock.value.planned_duration * 60
        const remaining = Math.max(0, plannedSeconds - elapsed)
        currentBlockTime.value = remaining
        // Воспроизводим звук возобновления
        playStartSound()
    }
    
    // Запускаем интервал таймера
    startTimerInterval()
    
    // Сохраняем состояние
    saveTimerState()
}

const pauseTimer = () => {
    timerRunning.value = false
    if (timerInterval.value) {
        clearInterval(timerInterval.value)
        timerInterval.value = null
    }
    // Воспроизводим звук паузы
    playPauseSound()
    // Сохраняем состояние при паузе
    saveTimerState()
    // Просто останавливаем таймер, время паузы будет учтено при возобновлении
}

const resetTimer = () => {
    pauseTimer()
    currentBlockTime.value = 0
    startTime.value = null
    blockStartTime.value = null
    // Очищаем сохраненное состояние
    clearTimerState()
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
            // Очищаем сохраненное состояние
            clearTimerState()
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
            // Воспроизводим звук переключения блока
            playBlockSwitchSound()
        }
    })
}

const completeCurrentBlock = () => {
    if (currentBlock.value) {
        // Воспроизводим звук завершения блока
        playCompleteSound()
        completeBlock(currentBlock.value)
    }
}

// Управление настройками звука
const toggleSoundSettings = () => {
    soundSettings.value.enabled = !soundSettings.value.enabled
    // Сохраняем настройки
    localStorage.setItem('timer-sound-settings', JSON.stringify(soundSettings.value))
}

const saveSoundSettings = () => {
    // Сохраняем настройки
    localStorage.setItem('timer-sound-settings', JSON.stringify(soundSettings.value))
    showSoundSettings.value = false
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
        planned: 'border-amber-200 dark:border-gray-600 bg-amber-50/80 dark:bg-gray-800 hover:border-amber-300 dark:hover:border-gray-500',
        active: 'border-orange-200 dark:border-indigo-600 bg-gradient-to-r from-orange-50/80 to-red-50/80 dark:from-indigo-900 dark:to-purple-900 hover:border-orange-300 dark:hover:border-indigo-500',
        paused: 'border-yellow-200 dark:border-yellow-600 bg-gradient-to-r from-yellow-50/80 to-orange-50/80 dark:from-yellow-900 dark:to-orange-900 hover:border-yellow-300 dark:hover:border-yellow-500',
        completed: 'border-red-200 dark:border-green-600 bg-gradient-to-r from-red-50/80 to-orange-50/80 dark:from-green-900 dark:to-emerald-900 hover:border-red-300 dark:hover:border-green-500',
        skipped: 'border-amber-200 dark:border-gray-600 bg-amber-100/80 dark:bg-gray-800 hover:border-amber-300 dark:hover:border-gray-500',
    }
    return classes[status as keyof typeof classes] || 'border-amber-200 dark:border-gray-600 bg-amber-50/80 dark:bg-gray-800'
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
        planned: 'bg-amber-100 dark:bg-gray-700 shadow-sm',
        active: 'bg-gradient-to-br from-orange-100 to-red-100 dark:from-indigo-800 dark:to-purple-800 shadow-sm',
        paused: 'bg-gradient-to-br from-yellow-100 to-orange-100 dark:from-yellow-800 dark:to-orange-800 shadow-sm',
        completed: 'bg-gradient-to-br from-red-100 to-orange-100 dark:from-green-800 dark:to-emerald-800 shadow-sm',
        skipped: 'bg-amber-100 dark:bg-gray-700 shadow-sm',
    }
    return classes[status as keyof typeof classes] || 'bg-amber-100 dark:bg-gray-700 shadow-sm'
}

const getBlockBadgeClass = (status: string) => {
    const baseClass = 'px-2 py-1 rounded-full text-xs font-medium shadow-sm'
    const statusClasses = {
        planned: 'bg-amber-100 dark:bg-gray-700 text-amber-700 dark:text-gray-200',
        active: 'bg-orange-100 dark:bg-indigo-900 text-orange-700 dark:text-indigo-200',
        paused: 'bg-yellow-100 dark:bg-yellow-900 text-yellow-700 dark:text-yellow-200',
        completed: 'bg-red-100 dark:bg-green-900 text-red-700 dark:text-green-200',
        skipped: 'bg-amber-100 dark:bg-gray-700 text-amber-500 dark:text-gray-400',
    }
    return `${baseClass} ${statusClasses[status as keyof typeof statusClasses] || 'bg-amber-100 dark:bg-gray-700 text-amber-700 dark:text-gray-200'}`
}

// Жизненный цикл
onMounted(() => {
    // Загружаем настройки звука
    loadSoundSettings()
    
    // Пытаемся восстановить состояние таймера
    const timerRestored = restoreTimerState()
    
    // Если таймер не был восстановлен и есть активный блок, запускаем новый таймер
    if (!timerRestored && currentBlock.value && props.session.status === 'active') {
        startTimer()
    }
})

onUnmounted(() => {
    pauseTimer()
    // Сохраняем состояние перед размонтированием
    saveTimerState()
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

const deleteSession = () => {
    if (confirm(`Вы уверены, что хотите удалить сессию "${props.session.title}"? Это действие нельзя отменить.`)) {
        router.delete(route('sessions.destroy', props.session.id), {
            onSuccess: () => {
                // Успешно удалено
            },
            onError: () => {
                alert('Ошибка при удалении сессии')
            }
        })
    }
}
</script>