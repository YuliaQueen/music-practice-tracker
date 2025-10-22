<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-primary-800 dark:text-neutral-200 leading-tight">
                    {{ session.title }}
                </h2>
                <div class="flex space-x-2">
                    <span :class="getStatusBadgeClass(session.status)">{{ getStatusLabel(session.status) }}</span>
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

        <!-- Основной контент -->
        <div class="py-4 sm:py-6 pb-24 sm:pb-28">
            <div class="max-w-7xl mx-auto sm:px-4 lg:px-6">
                <!-- Уведомление о продлении времени -->
                <Transition
                    enter-active-class="transition ease-out duration-300"
                    enter-from-class="opacity-0 transform translate-y-2"
                    enter-to-class="opacity-100 transform translate-y-0"
                    leave-active-class="transition ease-in duration-200"
                    leave-from-class="opacity-100 transform translate-y-0"
                    leave-to-class="opacity-0 transform translate-y-2"
                >
                    <div
                        v-if="extensionNotification.show"
                        class="mb-4 p-4 bg-success-100 border border-success-200 rounded-lg shadow-sm dark:bg-success-900/20 dark:border-success-800"
                    >
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-success-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-medium text-success-800 dark:text-success-200">
                                    {{ extensionNotification.message }}
                                </h4>
                            </div>
                        </div>
                    </div>
                </Transition>

                <!-- Информация о сессии -->
                <SessionInfo
                    :session="session"
                />

                <!-- Таймер и текущий блок -->
                <SessionTimer
                    v-if="currentBlock"
                    :current-block="currentBlock"
                    :time-remaining="currentBlockTime"
                    :progress="currentBlockProgress"
                    :is-running="timerRunning"
                />

                <!-- Управление продлением времени -->
                <TimerExtensionControls
                    v-if="session.status === 'active' && session.blocks.length > 0"
                    :blocks="session.blocks"
                    v-model:selectedBlockId="selectedBlockForExtension"
                    @extend="extendTimer"
                    @restart="restartTimerForBlock"
                />

                <!-- Список блоков -->
                <SessionBlocksList
                    :blocks="session.blocks"
                />
            </div>
        </div>

        <!-- Модальное окно настроек звука -->
        <SoundSettingsModal
            :show="showSoundSettings"
            :settings="soundSettings"
            @update:settings="(newSettings) => soundSettings = newSettings"
            @close="showSoundSettings = false"
            @save="saveSoundSettings"
        />

        <!-- Фиксированная панель управления -->
        <SessionControlBar
            :session="session"
            :current-block="currentBlock"
            :processing="form.processing"
            @start="startSession"
            @pause="pauseSession"
            @complete="completeSession"
            @start-next-block="startNextBlock"
        />
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import { computed, ref, onMounted, onUnmounted, watch } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import DangerButton from '@/Components/DangerButton.vue'
import SessionInfo from '@/Components/Session/SessionInfo.vue'
import SessionTimer from '@/Components/Session/SessionTimer.vue'
import SessionBlocksList from '@/Components/Session/SessionBlocksList.vue'
import SessionControlBar from '@/Components/Session/SessionControlBar.vue'
import SoundSettingsModal from '@/Components/Session/SoundSettingsModal.vue'
import TimerExtensionControls from '@/Components/Session/TimerExtensionControls.vue'
import { useTimerSounds } from '@/composables/useTimerSounds'
import { getStatusLabel, getStatusBadgeClass } from '@/utils/statusHelpers'

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

// Состояние таймера
const timerRunning = ref(false)
const currentBlockTime = ref(0)
const timerInterval = ref<number | null>(null)
const startTime = ref<number | null>(null)
const blockStartTime = ref<number | null>(null)
const warningPlayed = ref(false)
const showSoundSettings = ref(false)
const extensionNotification = ref<{ show: boolean; message: string; minutes: number }>({
    show: false,
    message: '',
    minutes: 0
})
const selectedBlockForExtension = ref<number | null>(null)

// Ключи для localStorage
const TIMER_STATE_KEY = 'timer-state'
const TIMER_SESSION_KEY = 'timer-session-id'

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

// Управление таймером
const startTimer = () => {
    if (!currentBlock.value) return

    timerRunning.value = true
    warningPlayed.value = false

    if (!startTime.value || !blockStartTime.value) {
        startTime.value = Date.now()
        blockStartTime.value = Date.now()
        currentBlockTime.value = currentBlock.value.planned_duration * 60
        playStartSound()
    } else {
        const elapsed = Math.floor((Date.now() - startTime.value) / 1000)
        const plannedSeconds = currentBlock.value.planned_duration * 60
        const remaining = Math.max(0, plannedSeconds - elapsed)
        currentBlockTime.value = remaining
        playStartSound()
    }

    startTimerInterval()
    saveTimerState()
}

const pauseTimer = () => {
    timerRunning.value = false
    if (timerInterval.value) {
        clearInterval(timerInterval.value)
        timerInterval.value = null
    }
    playPauseSound()
    saveTimerState()
}

const resetTimer = () => {
    pauseTimer()
    currentBlockTime.value = 0
    startTime.value = null
    blockStartTime.value = null
    clearTimerState()
}

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

        if (remaining <= 30 && remaining > 0 && !warningPlayed.value) {
            playWarningSound()
            warningPlayed.value = true
        }

        if (remaining <= 0) {
            playTimeUpSound()
            completeCurrentBlock()
        }

        if (elapsed % 5 === 0) {
            saveTimerState()
        }
    }, 100)
}

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

const clearTimerState = () => {
    localStorage.removeItem(TIMER_STATE_KEY)
    localStorage.removeItem(TIMER_SESSION_KEY)
}

const restoreTimerState = () => {
    try {
        const savedState = localStorage.getItem(TIMER_STATE_KEY)
        const savedSessionId = localStorage.getItem(TIMER_SESSION_KEY)

        if (!savedState || !savedSessionId) return false

        const timerState = JSON.parse(savedState)

        if (timerState.sessionId !== props.session.id) {
            clearTimerState()
            return false
        }

        const savedBlock = props.session.blocks.find(block => block.id === timerState.blockId)
        if (!savedBlock || savedBlock.status !== 'active') {
            clearTimerState()
            return false
        }

        const maxAge = 24 * 60 * 60 * 1000
        if (Date.now() - timerState.timestamp > maxAge) {
            clearTimerState()
            return false
        }

        startTime.value = timerState.startTime
        blockStartTime.value = timerState.blockStartTime

        const now = Date.now()
        const elapsed = Math.floor((now - timerState.startTime) / 1000)
        const plannedSeconds = timerState.plannedDuration * 60
        const remaining = Math.max(0, plannedSeconds - elapsed)

        currentBlockTime.value = remaining

        if (remaining > 0) {
            timerRunning.value = true
            startTimerInterval()
            return true
        } else {
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

// Управление блоками
const startBlock = (block: SessionBlock) => {
    const blockForm = useForm({
        status: 'active',
        ...(block.status === 'planned' ? { started_at: new Date().toISOString() } : {}),
    })
    blockForm.patch(route('sessions.blocks.update', { session: props.session.id, block: block.id }), {
        preserveScroll: true,
        onSuccess: () => {
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
            pauseTimer()
            clearTimerState()
        }
    })
}

const completeBlock = (block: SessionBlock) => {
    const actualDuration = blockStartTime.value
        ? Math.round((Date.now() - blockStartTime.value) / 1000 / 60)
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
            resetTimer()
            playBlockSwitchSound()
        }
    })
}

const completeCurrentBlock = () => {
    if (currentBlock.value) {
        playCompleteSound()
        completeBlock(currentBlock.value)
    }
}

// Продление времени
const extendTimer = (minutes: number) => {
    if (!selectedBlockForExtension.value) return

    const selectedBlock = props.session.blocks.find(block => block.id === selectedBlockForExtension.value)
    if (!selectedBlock) return

    const newPlannedDuration = selectedBlock.planned_duration + minutes

    const blockForm = useForm({
        planned_duration: newPlannedDuration,
    })

    blockForm.patch(route('sessions.blocks.update', {
        session: props.session.id,
        block: selectedBlock.id
    }), {
        preserveScroll: true,
        onSuccess: () => {
            selectedBlock.planned_duration = newPlannedDuration

            if (selectedBlock.id === currentBlock.value?.id && timerRunning.value) {
                if (startTime.value) {
                    const now = Date.now()
                    const elapsed = Math.floor((now - startTime.value) / 1000)
                    const newPlannedSeconds = newPlannedDuration * 60
                    const newRemaining = Math.max(0, newPlannedSeconds - elapsed)
                    currentBlockTime.value = newRemaining
                }
            }

            saveTimerState()
            showExtensionNotification(minutes, selectedBlock.title)
        }
    })
}

const restartTimerForBlock = () => {
    if (!selectedBlockForExtension.value) return

    const selectedBlock = props.session.blocks.find(block => block.id === selectedBlockForExtension.value)
    if (!selectedBlock) return

    const blockForm = useForm({
        status: 'active',
        actual_duration: null,
        completed_at: null,
    })

    blockForm.patch(route('sessions.blocks.update', {
        session: props.session.id,
        block: selectedBlock.id
    }), {
        preserveScroll: true,
        onSuccess: () => {
            selectedBlock.status = 'active'
            selectedBlock.actual_duration = null
            selectedBlock.completed_at = null

            resetTimer()
            showExtensionNotification(0, selectedBlock.title, 'Перезапущен')
        }
    })
}

const showExtensionNotification = (minutes: number, blockTitle?: string, action?: string) => {
    const title = blockTitle ? ` для "${blockTitle}"` : ''
    let message = ''

    if (action === 'Перезапущен') {
        message = `Таймер перезапущен${title}`
    } else {
        message = `Время продлено на ${minutes} минут${title}`
    }

    extensionNotification.value = {
        show: true,
        message: message,
        minutes: minutes
    }

    setTimeout(() => {
        extensionNotification.value.show = false
    }, 3000)
}

// Управление сессией
const startSession = () => {
    form.post(route('sessions.start', props.session.id), {
        preserveScroll: true,
        onSuccess: () => {
            // После успешного старта сессии, запускаем таймер если есть активный блок
            if (currentBlock.value) {
                startTimer()
            }
        }
    })
}

const pauseSession = () => {
    // Сначала останавливаем таймер локально
    pauseTimer()

    // Затем отправляем запрос на сервер
    form.post(route('sessions.pause', props.session.id), {
        preserveScroll: true,
    })
}

const completeSession = () => {
    // Останавливаем таймер при завершении сессии
    pauseTimer()

    form.post(route('sessions.complete', props.session.id), {
        preserveScroll: true,
    })
}

const startNextBlock = () => {
    // Находим первый блок со статусом 'planned'
    const nextBlock = props.session.blocks.find(block => block.status === 'planned')
    if (nextBlock) {
        startBlock(nextBlock)
    }
}

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

// Управление звуками
const toggleSoundSettings = () => {
    soundSettings.value.enabled = !soundSettings.value.enabled
    localStorage.setItem('timer-sound-settings', JSON.stringify(soundSettings.value))
}

const saveSoundSettings = () => {
    localStorage.setItem('timer-sound-settings', JSON.stringify(soundSettings.value))
    showSoundSettings.value = false
}

// Жизненный цикл
onMounted(() => {
    loadSoundSettings()

    if (currentBlock.value) {
        selectedBlockForExtension.value = currentBlock.value.id
    }

    const timerRestored = restoreTimerState()

    if (!timerRestored && currentBlock.value && props.session.status === 'active') {
        startTimer()
    }
})

onUnmounted(() => {
    pauseTimer()
    saveTimerState()
})

// Отслеживание изменений
watch(currentBlock, (newBlock, oldBlock) => {
    if (oldBlock && newBlock && oldBlock.id !== newBlock.id) {
        resetTimer()
    }

    if (newBlock) {
        selectedBlockForExtension.value = newBlock.id
    }

    if (newBlock && props.session.status === 'active' && !timerRunning.value) {
        startTimer()
    }
})

watch(() => props.session.blocks, (newBlocks) => {
    const activeBlock = newBlocks.find(block => block.status === 'active')

    if (activeBlock && props.session.status === 'active' && !timerRunning.value) {
        startTimer()
    } else if (!activeBlock && timerRunning.value) {
        pauseTimer()
    }
}, { deep: true })
</script>
