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
                  <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd"/>
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
        <div class="mb-6">
          <SessionInfo
              :session="session as any"
          />
        </div>

        <!-- Таймер и текущий блок (sticky) -->
        <div v-if="currentBlock"
             class="sticky top-0 z-20 mb-6 bg-gradient-to-b from-neutral-50 via-neutral-50 to-transparent dark:from-neutral-900 dark:via-neutral-900 dark:to-transparent pb-2">
          <SessionTimer
              :current-block="currentBlock"
              :time-remaining="currentBlockTime"
              :progress="currentBlockProgress"
              :is-running="timerRunning"
          />
        </div>

        <!-- Аудио рекордер -->
        <div v-if="currentBlock" class="mb-6">
          <AudioRecorder
              :session-block-id="currentBlock.id"
              @saved="handleRecordingSaved"
          />
        </div>

        <!-- Список блоков -->
        <SessionBlocksList
            :blocks="session.blocks"
            :session-id="session.id"
            :session-mode="session.session_mode"
            :auto-advance="session.auto_advance"
        />

        <!-- Метроном (компактный, под упражнениями) -->
        <div class="mt-6 mb-6">
          <CompactMetronome :initially-collapsed="true"/>
        </div>

        <!-- Записи теперь показываются внутри каждого упражнения в SessionBlocksList -->
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
        :session="session as any"
        :current-block="currentBlock as any"
        :processing="form.processing"
        @start="startSession"
        @pause="pauseSession"
        @complete="completeSession"
        @start-next-block="startNextBlock"
    />
  </AuthenticatedLayout>
</template>

<script setup lang="ts">
import {computed, onMounted, onUnmounted, ref, watch} from 'vue'
import {router, useForm} from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import DangerButton from '@/Components/DangerButton.vue'
import SessionInfo from '@/Components/Session/SessionInfo.vue'
import SessionTimer from '@/Components/Session/SessionTimer.vue'
import SessionBlocksList from '@/Components/Session/SessionBlocksList.vue'
import SessionControlBar from '@/Components/Session/SessionControlBar.vue'
import SoundSettingsModal from '@/Components/Session/SoundSettingsModal.vue'
import CompactMetronome from '@/Components/Metronome/CompactMetronome.vue'
import AudioRecorder from '@/Components/Audio/AudioRecorder.vue'
import {useTimerSounds} from '@/composables/useTimerSounds'
import {getStatusBadgeClass, getStatusLabel} from '@/utils/statusHelpers'
import type {Session, SessionBlock} from '@/types/models'

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

// Таймер автозавершения сессии
const autoCompleteTimerId = ref<number | null>(null)
const AUTO_COMPLETE_DELAY = 10 * 60 * 1000 // 10 минут в миллисекундах

// Константы для таймера
const SECONDS_IN_MINUTE = 60
const MS_IN_SECOND = 1000
const TIMER_INTERVAL_MS = 100
const WARNING_THRESHOLD_SECONDS = 30
const MAX_TIMER_AGE_MS = 24 * 60 * 60 * 1000 // 24 часа
const SAVE_INTERVAL_SECONDS = 5
const NOTIFICATION_DURATION_MS = 3000
const MAX_PROGRESS_PERCENT = 100

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

const stopTimerInternal = () => {
  timerRunning.value = false
  if (timerInterval.value) {
    clearInterval(timerInterval.value)
    timerInterval.value = null
  }
}

const pauseTimer = () => {
  stopTimerInternal()
  playPauseSound()
  saveTimerState()
}

const resetTimer = () => {
  stopTimerInternal()
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
      stopTimerInternal()
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
    ...(block.status === 'planned' ? {started_at: new Date().toISOString()} : {}),
  })
  blockForm.patch(route('sessions.blocks.update', {session: props.session.id, block: block.id}), {
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
  blockForm.patch(route('sessions.blocks.update', {session: props.session.id, block: block.id}), {
    preserveScroll: true,
    onSuccess: () => {
      pauseTimer()
      clearTimerState()
    }
  })
}

const completeBlock = (block: SessionBlock) => {
  console.log('🔄 Завершение блока:', block.title, 'ID:', block.id)

  const actualDuration = blockStartTime.value
      ? Math.round((Date.now() - blockStartTime.value) / 1000 / 60)
      : block.planned_duration

  const blockForm = useForm({
    status: 'completed',
    actual_duration: actualDuration,
    started_at: blockStartTime.value ? new Date(blockStartTime.value).toISOString() : null,
    completed_at: new Date().toISOString(),
  })

  console.log('📤 Отправка запроса на завершение блока...')

  blockForm.patch(route('sessions.blocks.update', {session: props.session.id, block: block.id}), {
    preserveScroll: true,
    onSuccess: () => {
      console.log('✅ Блок успешно завершен на сервере')

      setTimeout(() => {
        router.visit(route('sessions.show', props.session.id), {
          preserveScroll: true,
          preserveState: false,
          onSuccess: () => {
            console.log('📊 Страница обновлена, текущие блоки:', props.session.blocks.map(b => ({ id: b.id, title: b.title, status: b.status })))

            playBlockSwitchSound()
            checkAndScheduleAutoComplete()
          }
        })
      }, 150)
    },
    onError: (errors) => {
      console.error('❌ Ошибка при завершении блока:', errors)
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
// Методы extendTimer и restartTimerForBlock удалены - теперь в SessionBlocksList.vue

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
    onFinish: () => {
      console.log('✅ Сессия запущена, обновляем страницу')
      // Принудительно перезагружаем страницу для получения актуальных данных
      router.visit(route('sessions.show', props.session.id), {
        preserveScroll: true,
        preserveState: false,
        onSuccess: () => {
          console.log('📊 Страница обновлена после старта сессии')
          // После успешного старта сессии, запускаем таймер если есть активный блок
          if (currentBlock.value) {
            startTimer()
          }
        }
      })
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

const handleRecordingSaved = (recordingId: number) => {
  console.log('Recording saved:', recordingId)
  // Перезагружаем сессию, чтобы обновить записи в blocks
  router.reload({only: ['session']})
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

// Автозавершение сессии
const clearAutoCompleteTimer = () => {
  if (autoCompleteTimerId.value) {
    clearTimeout(autoCompleteTimerId.value)
    autoCompleteTimerId.value = null
  }
}

const checkAndScheduleAutoComplete = () => {
  // Очищаем предыдущий таймер если есть
  clearAutoCompleteTimer()

  // Проверяем, есть ли еще незавершенные блоки
  const hasUnfinishedBlocks = props.session.blocks.some(block =>
      block.status === 'planned' || block.status === 'active' || block.status === 'paused'
  )

  // Если все блоки завершены и сессия активна, запускаем таймер автозавершения
  if (!hasUnfinishedBlocks && props.session.status === 'active') {
    console.log('Все блоки завершены. Запуск таймера автозавершения на 10 минут...')

    autoCompleteTimerId.value = window.setTimeout(() => {
      console.log('Автозавершение сессии через 10 минут бездействия')
      completeSession()
    }, AUTO_COMPLETE_DELAY)
  }
}

// Жизненный цикл
onMounted(() => {
  loadSoundSettings()

  const timerRestored = restoreTimerState()

  if (!timerRestored && currentBlock.value && props.session.status === 'active') {
    startTimer()
  }
})

onUnmounted(() => {
  pauseTimer()
  saveTimerState()
  clearAutoCompleteTimer()
})

// Отслеживание изменений
watch(currentBlock, (newBlock, oldBlock) => {
  console.log('🔍 Изменение currentBlock:', {
    oldBlock: oldBlock ? { id: oldBlock.id, title: oldBlock.title, status: oldBlock.status } : null,
    newBlock: newBlock ? { id: newBlock.id, title: newBlock.title, status: newBlock.status } : null
  })

  // Если блок изменился (переход к следующему блоку)
  if (oldBlock && newBlock && oldBlock.id !== newBlock.id) {
    console.log('➡️ Автопереход к следующему блоку:', newBlock.title)

    stopTimerInternal()
    currentBlockTime.value = 0
    startTime.value = null
    blockStartTime.value = null
    clearTimerState()
    warningPlayed.value = false

    if (props.session.status === 'active') {
      console.log('▶️ Запуск таймера для нового блока:', newBlock.title)
      startTimer()
    } else {
      console.log('⏸️ Сессия не активна, таймер не запускается')
    }
  }
  // Если появился новый активный блок (например, после пересоздания компонента)
  else if (newBlock && props.session.status === 'active' && !timerRunning.value) {
    console.log('🎬 Новый активный блок обнаружен:', newBlock.title)
    startTimer()
  }
  // Если блок исчез (был завершен и нет следующего)
  else if (!newBlock && oldBlock) {
    console.log('⏹️ Активный блок исчез, останавливаем таймер')
    stopTimerInternal()
    currentBlockTime.value = 0
    startTime.value = null
    blockStartTime.value = null
    clearTimerState()
  }
})
</script>
