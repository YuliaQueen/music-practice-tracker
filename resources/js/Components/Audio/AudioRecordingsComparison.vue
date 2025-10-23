<template>
    <div class="bg-white dark:bg-neutral-800 rounded-lg shadow-sm p-4 sm:p-6">
        <div class="space-y-4">
            <!-- Заголовок -->
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100">
                    Сравнение записей и прогресс
                </h3>
            </div>

            <!-- Пустое состояние -->
            <div v-if="recordings.length < 2" class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-neutral-900 dark:text-neutral-100">
                    Недостаточно записей
                </h3>
                <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                    Для сравнения нужно минимум 2 записи. Продолжайте практиковаться!
                </p>
            </div>

            <!-- Селектор записей для сравнения -->
            <div v-else class="space-y-4">
                <!-- График прогресса оценок -->
                <div v-if="hasRatings" class="bg-neutral-50 dark:bg-neutral-900 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-neutral-900 dark:text-neutral-100 mb-4">
                        График качества исполнения
                    </h4>
                    <div class="space-y-2">
                        <div
                            v-for="(recording, index) in sortedRecordings"
                            :key="recording.id"
                            class="flex items-center"
                        >
                            <div class="w-32 text-xs text-neutral-600 dark:text-neutral-400">
                                {{ formatShortDate(recording.recorded_at) }}
                            </div>
                            <div class="flex-1 flex items-center">
                                <div
                                    class="h-8 bg-gradient-to-r from-primary-400 to-primary-600 rounded transition-all"
                                    :style="{ width: getRatingWidth(recording.quality_rating) }"
                                />
                                <span class="ml-2 text-sm font-medium text-neutral-900 dark:text-neutral-100">
                                    {{ recording.quality_rating || 'Н/Д' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Средняя оценка и динамика -->
                    <div class="mt-4 grid grid-cols-2 gap-4">
                        <div class="bg-white dark:bg-neutral-800 rounded p-3">
                            <div class="text-xs text-neutral-500 dark:text-neutral-400">Средняя оценка</div>
                            <div class="text-2xl font-bold text-neutral-900 dark:text-neutral-100 mt-1">
                                {{ averageRating }}
                            </div>
                        </div>
                        <div class="bg-white dark:bg-neutral-800 rounded p-3">
                            <div class="text-xs text-neutral-500 dark:text-neutral-400">Динамика</div>
                            <div class="text-2xl font-bold mt-1" :class="progressClass">
                                {{ progressText }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Сравнение двух записей -->
                <div class="border-t border-neutral-200 dark:border-neutral-700 pt-4">
                    <h4 class="text-sm font-medium text-neutral-900 dark:text-neutral-100 mb-4">
                        Сравнить две записи
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Первая запись -->
                        <div class="space-y-2">
                            <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                                Запись 1
                            </label>
                            <select
                                v-model="selectedRecording1"
                                class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-700 dark:text-neutral-100"
                            >
                                <option :value="null">Выберите запись...</option>
                                <option
                                    v-for="recording in sortedRecordings"
                                    :key="recording.id"
                                    :value="recording.id"
                                >
                                    {{ recording.title || formatDate(recording.recorded_at) }}
                                    {{ recording.quality_rating ? `★ ${recording.quality_rating}` : '' }}
                                </option>
                            </select>

                            <div v-if="recording1" class="bg-neutral-50 dark:bg-neutral-900 rounded p-3 space-y-2">
                                <audio
                                    :src="getAudioUrl(recording1)"
                                    controls
                                    class="w-full"
                                />
                                <div class="text-xs text-neutral-600 dark:text-neutral-400">
                                    Дата: {{ formatDate(recording1.recorded_at) }}
                                </div>
                                <div v-if="recording1.quality_rating" class="flex items-center">
                                    <span class="text-xs text-neutral-600 dark:text-neutral-400 mr-2">Оценка:</span>
                                    <div class="flex">
                                        <svg
                                            v-for="star in recording1.quality_rating"
                                            :key="star"
                                            class="w-4 h-4 text-warning-400"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                    </div>
                                </div>
                                <div v-if="recording1.notes" class="text-xs text-neutral-600 dark:text-neutral-400">
                                    Заметки: {{ recording1.notes }}
                                </div>
                            </div>
                        </div>

                        <!-- Вторая запись -->
                        <div class="space-y-2">
                            <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                                Запись 2
                            </label>
                            <select
                                v-model="selectedRecording2"
                                class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 dark:bg-neutral-700 dark:text-neutral-100"
                            >
                                <option :value="null">Выберите запись...</option>
                                <option
                                    v-for="recording in sortedRecordings"
                                    :key="recording.id"
                                    :value="recording.id"
                                >
                                    {{ recording.title || formatDate(recording.recorded_at) }}
                                    {{ recording.quality_rating ? `★ ${recording.quality_rating}` : '' }}
                                </option>
                            </select>

                            <div v-if="recording2" class="bg-neutral-50 dark:bg-neutral-900 rounded p-3 space-y-2">
                                <audio
                                    :src="getAudioUrl(recording2)"
                                    controls
                                    class="w-full"
                                />
                                <div class="text-xs text-neutral-600 dark:text-neutral-400">
                                    Дата: {{ formatDate(recording2.recorded_at) }}
                                </div>
                                <div v-if="recording2.quality_rating" class="flex items-center">
                                    <span class="text-xs text-neutral-600 dark:text-neutral-400 mr-2">Оценка:</span>
                                    <div class="flex">
                                        <svg
                                            v-for="star in recording2.quality_rating"
                                            :key="star"
                                            class="w-4 h-4 text-warning-400"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                    </div>
                                </div>
                                <div v-if="recording2.notes" class="text-xs text-neutral-600 dark:text-neutral-400">
                                    Заметки: {{ recording2.notes }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Результат сравнения -->
                    <div v-if="recording1 && recording2" class="mt-4 bg-primary-50 dark:bg-primary-900/20 rounded p-4">
                        <h5 class="text-sm font-medium text-primary-900 dark:text-primary-100 mb-2">
                            Результат сравнения
                        </h5>
                        <div class="space-y-2 text-sm">
                            <div v-if="recording1.quality_rating && recording2.quality_rating">
                                <span class="text-primary-700 dark:text-primary-300">Изменение оценки:</span>
                                <span class="ml-2 font-medium" :class="getRatingChangeClass">
                                    {{ getRatingChange }}
                                </span>
                            </div>
                            <div>
                                <span class="text-primary-700 dark:text-primary-300">Разница во времени:</span>
                                <span class="ml-2 font-medium text-primary-900 dark:text-primary-100">
                                    {{ getTimeDifference }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'

interface AudioRecording {
    id: number
    title: string | null
    notes: string | null
    file_path: string
    quality_rating: number | null
    recorded_at: string
}

interface Props {
    recordings: AudioRecording[]
}

const props = defineProps<Props>()

const selectedRecording1 = ref<number | null>(null)
const selectedRecording2 = ref<number | null>(null)

const sortedRecordings = computed(() => {
    return [...props.recordings].sort((a, b) => {
        return new Date(a.recorded_at).getTime() - new Date(b.recorded_at).getTime()
    })
})

const hasRatings = computed(() => {
    return props.recordings.some(r => r.quality_rating !== null)
})

const recording1 = computed(() => {
    return props.recordings.find(r => r.id === selectedRecording1.value) || null
})

const recording2 = computed(() => {
    return props.recordings.find(r => r.id === selectedRecording2.value) || null
})

const averageRating = computed(() => {
    const ratingsWithValues = props.recordings.filter(r => r.quality_rating !== null)
    if (ratingsWithValues.length === 0) return 'Н/Д'

    const sum = ratingsWithValues.reduce((acc, r) => acc + (r.quality_rating || 0), 0)
    return (sum / ratingsWithValues.length).toFixed(1)
})

const progressText = computed(() => {
    const ratingsWithValues = sortedRecordings.value.filter(r => r.quality_rating !== null)
    if (ratingsWithValues.length < 2) return 'Н/Д'

    const first = ratingsWithValues[0].quality_rating || 0
    const last = ratingsWithValues[ratingsWithValues.length - 1].quality_rating || 0
    const diff = last - first

    if (diff > 0) return `+${diff.toFixed(1)}`
    if (diff < 0) return diff.toFixed(1)
    return '0'
})

const progressClass = computed(() => {
    const ratingsWithValues = sortedRecordings.value.filter(r => r.quality_rating !== null)
    if (ratingsWithValues.length < 2) return 'text-neutral-500'

    const first = ratingsWithValues[0].quality_rating || 0
    const last = ratingsWithValues[ratingsWithValues.length - 1].quality_rating || 0
    const diff = last - first

    if (diff > 0) return 'text-success-600 dark:text-success-400'
    if (diff < 0) return 'text-danger-600 dark:text-danger-400'
    return 'text-neutral-500'
})

const getRatingChange = computed(() => {
    if (!recording1.value?.quality_rating || !recording2.value?.quality_rating) return 'Н/Д'

    const diff = recording2.value.quality_rating - recording1.value.quality_rating
    if (diff > 0) return `+${diff} (улучшение)`
    if (diff < 0) return `${diff} (ухудшение)`
    return 'Без изменений'
})

const getRatingChangeClass = computed(() => {
    if (!recording1.value?.quality_rating || !recording2.value?.quality_rating) return ''

    const diff = recording2.value.quality_rating - recording1.value.quality_rating
    if (diff > 0) return 'text-success-700 dark:text-success-300'
    if (diff < 0) return 'text-danger-700 dark:text-danger-300'
    return 'text-neutral-700 dark:text-neutral-300'
})

const getTimeDifference = computed(() => {
    if (!recording1.value || !recording2.value) return 'Н/Д'

    const date1 = new Date(recording1.value.recorded_at)
    const date2 = new Date(recording2.value.recorded_at)
    const diffMs = Math.abs(date2.getTime() - date1.getTime())
    const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24))

    if (diffDays === 0) return 'Сегодня'
    if (diffDays === 1) return '1 день'
    if (diffDays < 7) return `${diffDays} дней`
    if (diffDays < 30) return `${Math.floor(diffDays / 7)} недель`
    return `${Math.floor(diffDays / 30)} месяцев`
})

const getRatingWidth = (rating: number | null) => {
    if (!rating) return '0%'
    return `${(rating / 5) * 100}%`
}

const getAudioUrl = (recording: AudioRecording) => {
    return `/storage/${recording.file_path}`
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

const formatShortDate = (dateString: string) => {
    const date = new Date(dateString)
    return date.toLocaleDateString('ru-RU', {
        day: '2-digit',
        month: '2-digit',
        year: '2-digit',
    })
}
</script>
