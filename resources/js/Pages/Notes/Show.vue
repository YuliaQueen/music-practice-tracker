<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <div class="flex items-center space-x-4">
          <Link
            :href="route('notes.index')"
            class="text-neutral-500 hover:text-neutral-700 dark:text-neutral-400 dark:hover:text-neutral-200"
          >
            <i class="fas fa-arrow-left"></i>
          </Link>
          <h2 class="font-semibold text-xl text-neutral-800 dark:text-neutral-200 leading-tight">
            {{ note.title }}
          </h2>
        </div>
        <div class="flex space-x-2">
          <Link
            :href="route('notes.edit', note.id)"
            class="bg-accent-500 hover:bg-accent-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200"
          >
            <i class="fas fa-edit mr-2"></i>
            {{ $t('common.edit') }}
          </Link>
          <button
            @click="downloadNote"
            class="bg-success-500 hover:bg-success-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200"
          >
            <i class="fas fa-download mr-2"></i>
            {{ $t('notes.download') }}
          </button>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Информация о файле -->
          <div class="lg:col-span-1">
            <div class="bg-white dark:bg-neutral-800 overflow-hidden shadow-sm sm:rounded-lg">
              <div class="p-6">
                <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100 mb-4">
                  {{ $t('notes.file_info') }}
                </h3>

                <!-- Основная информация -->
                <div class="space-y-4">
                  <div>
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                      {{ $t('notes.filename') }}
                    </label>
                    <p class="mt-1 text-sm text-neutral-900 dark:text-neutral-100">
                      {{ note.filename }}
                    </p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                      {{ $t('notes.file_type') }}
                    </label>
                    <div class="mt-1 flex items-center space-x-2">
                      <i :class="getFileTypeIcon(note.mime_type)" class="text-lg"></i>
                      <span class="text-sm text-neutral-900 dark:text-neutral-100">
                        {{ getFileTypeName(note.mime_type) }}
                      </span>
                    </div>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                      {{ $t('notes.file_size') }}
                    </label>
                    <p class="mt-1 text-sm text-neutral-900 dark:text-neutral-100">
                      {{ note.formatted_file_size }}
                    </p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                      {{ $t('notes.uploaded') }}
                    </label>
                    <p class="mt-1 text-sm text-neutral-900 dark:text-neutral-100">
                      {{ formatDate(note.created_at) }}
                    </p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                      {{ $t('notes.visibility') }}
                    </label>
                    <div class="mt-1 flex items-center space-x-2">
                      <i :class="note.is_public ? 'fas fa-globe text-success-500' : 'fas fa-lock text-neutral-500'"></i>
                      <span class="text-sm text-neutral-900 dark:text-neutral-100">
                        {{ note.is_public ? $t('notes.public') : $t('notes.private') }}
                      </span>
                    </div>
                  </div>

                  <!-- Связанное упражнение -->
                  <div v-if="note.exercise">
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300">
                      {{ $t('notes.associated_exercise') }}
                    </label>
                    <Link
                      :href="route('exercises.show', note.exercise.id)"
                      class="mt-1 inline-flex items-center text-sm text-accent-600 hover:text-accent-800 dark:text-accent-400 dark:hover:text-accent-200"
                    >
                      <i class="fas fa-dumbbell mr-1"></i>
                      {{ note.exercise.title }}
                    </Link>
                  </div>
                </div>

                <!-- Описание -->
                <div v-if="note.description" class="mt-6">
                  <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                    {{ $t('notes.description') }}
                  </label>
                  <p class="text-sm text-neutral-900 dark:text-neutral-100 whitespace-pre-wrap">
                    {{ note.description }}
                  </p>
                </div>

                <!-- Действия -->
                <div class="mt-6 space-y-3">
                  <button
                    @click="toggleFullscreen"
                    class="w-full bg-neutral-500 hover:bg-neutral-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200"
                  >
                    <i class="fas fa-expand mr-2"></i>
                    {{ isFullscreen ? $t('notes.exit_fullscreen') : $t('notes.fullscreen') }}
                  </button>

                  <button
                    v-if="isPdf"
                    @click="toggleZoom"
                    class="w-full bg-secondary-500 hover:bg-secondary-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200"
                  >
                    <i :class="isZoomed ? 'fas fa-search-minus' : 'fas fa-search-plus'" class="mr-2"></i>
                    {{ isZoomed ? $t('notes.zoom_out') : $t('notes.zoom_in') }}
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Просмотр файла -->
          <div class="lg:col-span-2">
            <div class="bg-white dark:bg-neutral-800 overflow-hidden shadow-sm sm:rounded-lg">
              <div class="p-6">
                <!-- Загрузка -->
                <div v-if="loading" class="flex items-center justify-center h-96">
                  <div class="text-center">
                    <i class="fas fa-spinner fa-spin text-4xl text-neutral-400 mb-4"></i>
                    <p class="text-neutral-500 dark:text-neutral-400">{{ $t('notes.loading') }}</p>
                  </div>
                </div>

                <!-- Ошибка загрузки -->
                <div v-else-if="error" class="flex items-center justify-center h-96">
                  <div class="text-center">
                    <i class="fas fa-exclamation-triangle text-4xl text-danger-400 mb-4"></i>
                    <p class="text-danger-500 dark:text-danger-400 mb-4">{{ error }}</p>
                    <button
                      @click="loadFile"
                      class="bg-accent-500 hover:bg-accent-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200"
                    >
                      {{ $t('notes.retry') }}
                    </button>
                  </div>
                </div>

                <!-- PDF просмотрщик -->
                <div v-else-if="isPdf && fileUrl" class="relative">
                  <div :class="['pdf-container', { 'fullscreen': isFullscreen, 'zoomed': isZoomed }]">
                    <iframe
                      :src="fileUrl"
                      class="w-full h-full border-0 rounded-lg"
                      :style="{ height: isFullscreen ? '100vh' : '600px' }"
                    ></iframe>
                  </div>
                  
                  <!-- Навигация для PDF -->
                  <div v-if="isPdf && !isFullscreen" class="mt-4 flex justify-center space-x-2">
                    <button
                      @click="previousPage"
                      :disabled="currentPage <= 1"
                      class="bg-neutral-500 hover:bg-neutral-700 disabled:bg-neutral-300 disabled:cursor-not-allowed text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200"
                    >
                      <i class="fas fa-chevron-left"></i>
                    </button>
                    <span class="flex items-center px-4 py-2 text-sm text-neutral-700 dark:text-neutral-300">
                      {{ currentPage }} / {{ totalPages }}
                    </span>
                    <button
                      @click="nextPage"
                      :disabled="currentPage >= totalPages"
                      class="bg-neutral-500 hover:bg-neutral-700 disabled:bg-neutral-300 disabled:cursor-not-allowed text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200"
                    >
                      <i class="fas fa-chevron-right"></i>
                    </button>
                  </div>
                </div>

                <!-- Изображение -->
                <div v-else-if="isImage && fileUrl" class="text-center">
                  <img
                    :src="fileUrl"
                    :alt="note.title"
                    class="max-w-full h-auto rounded-lg shadow-lg mx-auto"
                    :class="{ 'max-h-screen': isFullscreen }"
                  />
                </div>

                <!-- Аудио плеер -->
                <div v-else-if="isAudio && fileUrl" class="text-center">
                  <div class="bg-neutral-100 dark:bg-neutral-700 rounded-lg p-8">
                    <i class="fas fa-music text-6xl text-neutral-400 mb-4"></i>
                    <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100 mb-4">
                      {{ note.title }}
                    </h3>
                    <audio
                      :src="fileUrl"
                      controls
                      class="w-full max-w-md mx-auto"
                    >
                      {{ $t('notes.audio_not_supported') }}
                    </audio>
                  </div>
                </div>

                <!-- MusicXML -->
                <div v-else-if="isMusicXml && fileUrl" class="text-center">
                  <div class="bg-neutral-100 dark:bg-neutral-700 rounded-lg p-8">
                    <i class="fas fa-file-music text-6xl text-accent-400 mb-4"></i>
                    <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100 mb-4">
                      {{ note.title }}
                    </h3>
                    <p class="text-neutral-600 dark:text-neutral-400 mb-4">
                      {{ $t('notes.musicxml_description') }}
                    </p>
                    <button
                      @click="downloadNote"
                      class="bg-accent-500 hover:bg-accent-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200"
                    >
                      <i class="fas fa-download mr-2"></i>
                      {{ $t('notes.download_to_view') }}
                    </button>
                  </div>
                </div>

                <!-- Неподдерживаемый формат -->
                <div v-else class="text-center">
                  <div class="bg-neutral-100 dark:bg-neutral-700 rounded-lg p-8">
                    <i class="fas fa-file text-6xl text-neutral-400 mb-4"></i>
                    <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100 mb-4">
                      {{ note.title }}
                    </h3>
                    <p class="text-neutral-600 dark:text-neutral-400 mb-4">
                      {{ $t('notes.preview_not_available') }}
                    </p>
                    <button
                      @click="downloadNote"
                      class="bg-accent-500 hover:bg-accent-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200"
                    >
                      <i class="fas fa-download mr-2"></i>
                      {{ $t('notes.download') }}
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
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { useSimpleI18n } from '@/composables/useSimpleI18n'

interface Note {
  id: number
  title: string
  description?: string
  filename: string
  mime_type: string
  file_size: number
  formatted_file_size: string
  is_public: boolean
  created_at: string
  exercise?: {
    id: number
    title: string
  }
}

const props = defineProps<{
  note: Note
}>()

const { t: $t } = useSimpleI18n()

// Реактивные данные
const loading = ref(true)
const error = ref('')
const fileUrl = ref('')
const isFullscreen = ref(false)
const isZoomed = ref(false)
const currentPage = ref(1)
const totalPages = ref(1)

// Вычисляемые свойства для типов файлов
const isPdf = computed(() => props.note.mime_type === 'application/pdf')
const isImage = computed(() => props.note.mime_type.startsWith('image/'))
const isAudio = computed(() => props.note.mime_type.startsWith('audio/'))
const isMusicXml = computed(() => 
  props.note.mime_type.includes('musicxml') || 
  props.note.mime_type === 'application/vnd.recordare.musicxml'
)

// Методы
const loadFile = async () => {
  loading.value = true
  error.value = ''
  
  try {
    const response = await fetch(route('notes.view', props.note.id))
    const data = await response.json()
    
    if (response.ok) {
      fileUrl.value = data.url
    } else {
      error.value = data.message || $t('notes.load_error')
    }
  } catch (err) {
    error.value = $t('notes.load_error')
  } finally {
    loading.value = false
  }
}

const downloadNote = () => {
  window.open(route('notes.download', props.note.id), '_blank')
}

const toggleFullscreen = () => {
  isFullscreen.value = !isFullscreen.value
}

const toggleZoom = () => {
  isZoomed.value = !isZoomed.value
}

const previousPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--
  }
}

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++
  }
}

const getFileTypeIcon = (mimeType: string): string => {
  if (mimeType === 'application/pdf') return 'fas fa-file-pdf text-danger-500'
  if (mimeType.startsWith('image/')) return 'fas fa-image text-success-500'
  if (mimeType.startsWith('audio/')) return 'fas fa-music text-secondary-500'
  if (mimeType.includes('musicxml')) return 'fas fa-file-music text-accent-500'
  return 'fas fa-file text-neutral-500'
}

const getFileTypeName = (mimeType: string): string => {
  if (mimeType === 'application/pdf') return 'PDF Document'
  if (mimeType.startsWith('image/')) return 'Image'
  if (mimeType.startsWith('audio/')) return 'Audio'
  if (mimeType.includes('musicxml')) return 'MusicXML'
  return 'File'
}

const formatDate = (dateString: string): string => {
  return new Date(dateString).toLocaleDateString()
}

// Обработчики клавиатуры
const handleKeydown = (event: KeyboardEvent) => {
  if (isFullscreen.value) {
    switch (event.key) {
      case 'Escape':
        isFullscreen.value = false
        break
      case 'ArrowLeft':
        if (isPdf.value) previousPage()
        break
      case 'ArrowRight':
        if (isPdf.value) nextPage()
        break
    }
  }
}

onMounted(() => {
  loadFile()
  document.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown)
})
</script>

<style scoped>
.pdf-container {
  transition: all 0.3s ease;
}

.pdf-container.fullscreen {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  z-index: 9999;
  background: white;
}

.pdf-container.zoomed {
  transform: scale(1.2);
  transform-origin: top left;
}
</style>