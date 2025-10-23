<template>
    <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm p-4 sm:p-6">
        <div class="space-y-4">
            <!-- Заголовок -->
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100">
                    Запись исполнения
                </h3>
                <div v-if="isRecording || audioUrl" class="flex items-center space-x-2">
                    <svg v-if="isRecording && !isPaused" class="w-3 h-3 text-danger-500 animate-pulse" fill="currentColor" viewBox="0 0 8 8">
                        <circle cx="4" cy="4" r="4" />
                    </svg>
                    <span class="text-sm font-mono text-neutral-600 dark:text-neutral-400">
                        {{ formattedDuration }}
                    </span>
                </div>
            </div>

            <!-- Ошибка -->
            <div v-if="error" class="p-3 bg-danger-50 dark:bg-danger-900/20 border border-danger-200 dark:border-danger-800 rounded-md">
                <p class="text-sm text-danger-800 dark:text-danger-200">{{ error }}</p>
            </div>

            <!-- Предупреждение о неподдерживаемом браузере -->
            <div v-if="!isSupported" class="p-3 bg-warning-50 dark:bg-warning-900/20 border border-warning-200 dark:border-warning-800 rounded-md">
                <p class="text-sm text-warning-800 dark:text-warning-200">
                    Ваш браузер не поддерживает запись аудио. Попробуйте использовать современный браузер, такой как Chrome, Firefox или Edge.
                </p>
            </div>

            <!-- Аудио плеер (если есть запись) -->
            <div v-if="audioUrl && !isRecording" class="space-y-3">
                <audio
                    ref="audioElement"
                    :src="audioUrl"
                    controls
                    class="w-full"
                    controlsList="nodownload"
                />

                <!-- Поле для названия записи -->
                <div>
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                        Название записи (необязательно)
                    </label>
                    <input
                        v-model="recordingTitle"
                        type="text"
                        class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-700 dark:text-neutral-100"
                        placeholder="Название записи..."
                    />
                </div>

                <!-- Поле для заметок -->
                <div>
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                        Заметки (необязательно)
                    </label>
                    <textarea
                        v-model="recordingNotes"
                        rows="3"
                        class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-700 dark:text-neutral-100"
                        placeholder="Заметки о записи..."
                    />
                </div>

                <!-- Оценка качества -->
                <div>
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                        Оценка качества исполнения
                    </label>
                    <div class="flex space-x-2">
                        <button
                            v-for="rating in 5"
                            :key="rating"
                            type="button"
                            @click="qualityRating = rating"
                            class="focus:outline-none transition-transform hover:scale-110"
                        >
                            <svg
                                class="w-8 h-8"
                                :class="rating <= qualityRating ? 'text-warning-400' : 'text-neutral-300 dark:text-neutral-600'"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Кнопки управления записью -->
            <div class="flex flex-wrap gap-2">
                <!-- Кнопка начала записи -->
                <button
                    v-if="!isRecording && !audioUrl"
                    @click="handleStartRecording"
                    :disabled="!isSupported"
                    class="flex-1 sm:flex-none inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-danger-600 hover:bg-danger-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-danger-500 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                    </svg>
                    Начать запись
                </button>

                <!-- Кнопка паузы/возобновления -->
                <button
                    v-if="isRecording && !isPaused"
                    @click="pauseRecording"
                    class="flex-1 sm:flex-none inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-warning-600 hover:bg-warning-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-warning-500"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Пауза
                </button>

                <button
                    v-if="isRecording && isPaused"
                    @click="resumeRecording"
                    class="flex-1 sm:flex-none inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-success-600 hover:bg-success-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-success-500"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Продолжить
                </button>

                <!-- Кнопка остановки записи -->
                <button
                    v-if="isRecording"
                    @click="handleStopRecording"
                    class="flex-1 sm:flex-none inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-neutral-600 hover:bg-neutral-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-neutral-500"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z" />
                    </svg>
                    Остановить
                </button>

                <!-- Кнопка сохранения -->
                <button
                    v-if="audioUrl && !isRecording"
                    @click="handleSaveRecording"
                    :disabled="isSaving"
                    class="flex-1 sm:flex-none inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <svg v-if="!isSaving" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                    </svg>
                    <svg v-else class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ isSaving ? 'Сохранение...' : 'Сохранить запись' }}
                </button>

                <!-- Кнопка новой записи -->
                <button
                    v-if="audioUrl && !isRecording"
                    @click="handleNewRecording"
                    class="inline-flex items-center justify-center px-4 py-2 border border-neutral-300 dark:border-neutral-600 text-sm font-medium rounded-md text-neutral-700 dark:text-neutral-300 bg-white dark:bg-neutral-700 hover:bg-neutral-50 dark:hover:bg-neutral-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Новая запись
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { useAudioRecorder } from '@/composables/useAudioRecorder'

interface Props {
    exerciseId?: number
    sessionBlockId?: number
}

const props = defineProps<Props>()
const emit = defineEmits<{
    saved: [recordingId: number]
}>()

const {
    isRecording,
    isPaused,
    formattedDuration,
    audioUrl,
    error,
    isSupported,
    startRecording,
    pauseRecording,
    resumeRecording,
    stopRecording,
    resetRecording,
    getRecordingBlob,
    getMimeType,
} = useAudioRecorder()

const recordingTitle = ref('')
const recordingNotes = ref('')
const qualityRating = ref(0)
const isSaving = ref(false)
const audioElement = ref<HTMLAudioElement | null>(null)

const handleStartRecording = async () => {
    await startRecording()
}

const handleStopRecording = () => {
    stopRecording()
}

const handleNewRecording = () => {
    if (confirm('Вы уверены? Текущая запись будет удалена.')) {
        resetRecording()
        recordingTitle.value = ''
        recordingNotes.value = ''
        qualityRating.value = 0
    }
}

const handleSaveRecording = async () => {
    const blob = getRecordingBlob()
    if (!blob) {
        return
    }

    isSaving.value = true

    try {
        const formData = new FormData()

        // Добавляем аудио файл
        const mimeType = getMimeType()
        const extension = mimeType.includes('webm') ? 'webm' : 'mp4'
        formData.append('audio_file', blob, `recording-${Date.now()}.${extension}`)

        // Добавляем метаданные
        if (props.exerciseId) {
            formData.append('exercise_id', props.exerciseId.toString())
        }
        if (props.sessionBlockId) {
            formData.append('practice_session_block_id', props.sessionBlockId.toString())
        }
        if (recordingTitle.value) {
            formData.append('title', recordingTitle.value)
        }
        if (recordingNotes.value) {
            formData.append('notes', recordingNotes.value)
        }
        if (qualityRating.value > 0) {
            formData.append('quality_rating', qualityRating.value.toString())
        }

        const form = useForm(formData)

        form.post(route('audio-recordings.store'), {
            preserveScroll: true,
            onSuccess: (page) => {
                // Очищаем форму после успешного сохранения
                resetRecording()
                recordingTitle.value = ''
                recordingNotes.value = ''
                qualityRating.value = 0

                // Сообщаем родительскому компоненту об успешном сохранении
                // @ts-ignore
                const recordingId = page.props.flash?.recordingId
                if (recordingId) {
                    emit('saved', recordingId)
                }
            },
            onError: (errors) => {
                console.error('Ошибка при сохранении записи:', errors)
                alert('Не удалось сохранить запись. Попробуйте еще раз.')
            },
            onFinish: () => {
                isSaving.value = false
            }
        })

    } catch (err) {
        console.error('Error saving recording:', err)
        alert('Не удалось сохранить запись')
        isSaving.value = false
    }
}
</script>
