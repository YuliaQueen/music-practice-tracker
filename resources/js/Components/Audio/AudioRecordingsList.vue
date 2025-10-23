<template>
    <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm p-4 sm:p-6">
        <div class="space-y-4">
            <!-- Заголовок -->
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100">
                    История записей
                </h3>
                <span v-if="recordings.length > 0" class="text-sm text-neutral-500 dark:text-neutral-400">
                    Всего записей: {{ recordings.length }}
                </span>
            </div>

            <!-- Пустое состояние -->
            <div v-if="recordings.length === 0" class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-neutral-100">Нет записей</h3>
                <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                    Начните запись во время практики, чтобы отслеживать свой прогресс.
                </p>
            </div>

            <!-- Список записей -->
            <div v-else class="space-y-4">
                <div
                    v-for="recording in sortedRecordings"
                    :key="recording.id"
                    class="border border-neutral-200 dark:border-neutral-700 rounded-lg p-4 hover:border-primary-300 dark:hover:border-primary-700 transition-colors"
                >
                    <!-- Заголовок записи -->
                    <div class="flex items-start justify-between mb-2">
                        <div class="flex-1">
                            <h4 class="text-sm font-medium text-neutral-900 dark:text-neutral-100">
                                {{ recording.title || `Запись от ${formatDate(recording.recorded_at)}` }}
                            </h4>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                                {{ formatDate(recording.recorded_at) }}
                            </p>
                        </div>

                        <!-- Оценка качества -->
                        <div v-if="recording.quality_rating" class="flex items-center ml-2">
                            <svg
                                v-for="star in 5"
                                :key="star"
                                class="w-4 h-4"
                                :class="star <= recording.quality_rating ? 'text-warning-400' : 'text-neutral-300 dark:text-neutral-600'"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        </div>
                    </div>

                    <!-- Аудио плеер -->
                    <audio
                        :src="getAudioUrl(recording)"
                        controls
                        class="w-full mb-2"
                        controlsList="nodownload"
                    />

                    <!-- Заметки -->
                    <div v-if="recording.notes" class="mb-2">
                        <div v-if="expandedRecording === recording.id">
                            <p class="text-sm text-neutral-700 dark:text-neutral-300 whitespace-pre-wrap">
                                {{ recording.notes }}
                            </p>
                            <button
                                @click="expandedRecording = null"
                                class="text-xs text-primary-600 dark:text-primary-400 hover:underline mt-1"
                            >
                                Скрыть заметки
                            </button>
                        </div>
                        <button
                            v-else
                            @click="expandedRecording = recording.id"
                            class="text-xs text-primary-600 dark:text-primary-400 hover:underline"
                        >
                            Показать заметки
                        </button>
                    </div>

                    <!-- Кнопки действий -->
                    <div class="flex items-center justify-end space-x-2 mt-2">
                        <button
                            @click="downloadRecording(recording)"
                            class="inline-flex items-center px-2 py-1 text-xs font-medium text-neutral-700 dark:text-neutral-300 bg-neutral-100 dark:bg-neutral-700 hover:bg-neutral-200 dark:hover:bg-neutral-600 rounded"
                        >
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Скачать
                        </button>

                        <button
                            v-if="canEdit"
                            @click="editRecording(recording)"
                            class="inline-flex items-center px-2 py-1 text-xs font-medium text-primary-700 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20 hover:bg-primary-100 dark:hover:bg-primary-900/30 rounded"
                        >
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Редактировать
                        </button>

                        <button
                            v-if="canDelete"
                            @click="deleteRecording(recording)"
                            class="inline-flex items-center px-2 py-1 text-xs font-medium text-danger-700 dark:text-danger-400 bg-danger-50 dark:bg-danger-900/20 hover:bg-danger-100 dark:hover:bg-danger-900/30 rounded"
                        >
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Удалить
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

interface AudioRecording {
    id: number
    title: string | null
    notes: string | null
    file_path: string
    audio_url: string | null
    quality_rating: number | null
    recorded_at: string
    duration: number | null
}

interface Props {
    recordings: AudioRecording[]
    canEdit?: boolean
    canDelete?: boolean
}

const props = withDefaults(defineProps<Props>(), {
    canEdit: true,
    canDelete: true,
})

const emit = defineEmits<{
    refresh: []
}>()

const expandedRecording = ref<number | null>(null)

const sortedRecordings = computed(() => {
    return [...props.recordings].sort((a, b) => {
        return new Date(b.recorded_at).getTime() - new Date(a.recorded_at).getTime()
    })
})

const getAudioUrl = (recording: AudioRecording) => {
    return recording.audio_url || ''
}

const formatDate = (dateString: string) => {
    const date = new Date(dateString)
    return date.toLocaleString('ru-RU', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    })
}

const downloadRecording = (recording: AudioRecording) => {
    window.location.href = route('audio-recordings.download', recording.id)
}

const editRecording = (recording: AudioRecording) => {
    router.visit(route('audio-recordings.edit', recording.id))
}

const deleteRecording = (recording: AudioRecording) => {
    if (confirm('Вы уверены, что хотите удалить эту запись? Это действие нельзя отменить.')) {
        router.delete(route('audio-recordings.destroy', recording.id), {
            preserveScroll: true,
            onSuccess: () => {
                emit('refresh')
            },
            onError: () => {
                alert('Не удалось удалить запись')
            }
        })
    }
}
</script>
