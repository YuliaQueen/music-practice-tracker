<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-neutral-800 dark:text-neutral-200 leading-tight">
          {{ $t('notes.upload_new') }}
        </h2>
        <Link
          :href="route('notes.index')"
          class="bg-neutral-500 hover:bg-neutral-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200"
        >
          <i class="fas fa-arrow-left mr-2"></i>
          {{ $t('common.back') }}
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-neutral-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <form @submit.prevent="submitForm" enctype="multipart/form-data">
              <!-- Загрузка файла -->
              <div class="mb-6">
                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                  {{ $t('notes.select_file') }} <span class="text-danger-500">*</span>
                </label>
                
                <!-- Drag & Drop зона -->
                <div
                  @dragover.prevent
                  @dragenter.prevent
                  @drop.prevent="handleFileDrop"
                  :class="[
                    'border-2 border-dashed rounded-lg p-8 text-center transition-colors duration-200',
                    isDragOver 
                      ? 'border-accent-500 bg-accent-50 dark:bg-accent-900' 
                      : 'border-neutral-300 dark:border-neutral-600 hover:border-neutral-400 dark:hover:border-neutral-500'
                  ]"
                >
                  <div v-if="!selectedFile">
                    <i class="fas fa-cloud-upload-alt text-4xl text-neutral-400 mb-4"></i>
                    <p class="text-lg font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                      {{ $t('notes.drag_drop_file') }}
                    </p>
                    <p class="text-sm text-neutral-500 dark:text-neutral-400 mb-4">
                      {{ $t('notes.or_click_to_browse') }}
                    </p>
                    <input
                      ref="fileInput"
                      type="file"
                      @change="handleFileSelect"
                      accept=".pdf,.jpg,.jpeg,.png,.gif,.webp,.mp3,.wav,.ogg,.mxl,.musicxml"
                      class="hidden"
                    />
                    <button
                      type="button"
                      @click="() => fileInput?.click()"
                      class="bg-accent-500 hover:bg-accent-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200"
                    >
                      {{ $t('notes.browse_files') }}
                    </button>
                  </div>
                  
                  <div v-else class="space-y-4">
                    <div class="flex items-center justify-center space-x-4">
                      <div class="flex items-center justify-center w-16 h-16 rounded-full bg-neutral-100 dark:bg-neutral-600">
                        <i :class="getFileTypeIcon(selectedFile.type)" class="text-2xl"></i>
                      </div>
                      <div class="text-left">
                        <p class="text-lg font-medium text-neutral-900 dark:text-neutral-100">
                          {{ selectedFile.name }}
                        </p>
                        <p class="text-sm text-neutral-500 dark:text-neutral-400">
                          {{ formatFileSize(selectedFile.size) }}
                        </p>
                      </div>
                    </div>
                    <button
                      type="button"
                      @click="removeFile"
                      class="text-danger-500 hover:text-danger-700 text-sm font-medium"
                    >
                      <i class="fas fa-times mr-1"></i>
                      {{ $t('notes.remove_file') }}
                    </button>
                  </div>
                </div>

                <!-- Поддерживаемые форматы -->
                <div class="mt-3">
                  <p class="text-xs text-neutral-500 dark:text-neutral-400">
                    {{ $t('notes.supported_formats') }}: PDF, JPG, PNG, GIF, WebP, MP3, WAV, OGG, MusicXML
                  </p>
                  <p class="text-xs text-neutral-500 dark:text-neutral-400">
                    {{ $t('notes.max_file_size') }}: 50MB
                  </p>
                </div>

                <!-- Ошибки валидации -->
                <div v-if="errors.file" class="mt-2 text-sm text-danger-600 dark:text-danger-400">
                  {{ errors.file }}
                </div>
              </div>

              <!-- Название -->
              <div class="mb-6">
                <label for="title" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                  {{ $t('notes.title') }} <span class="text-danger-500">*</span>
                </label>
                <input
                  id="title"
                  v-model="form.title"
                  type="text"
                  :placeholder="$t('notes.title_placeholder')"
                  class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-accent-500 focus:border-accent-500 dark:bg-neutral-700 dark:text-white"
                  :class="{ 'border-danger-500': errors.title }"
                />
                <div v-if="errors.title" class="mt-2 text-sm text-danger-600 dark:text-danger-400">
                  {{ errors.title }}
                </div>
              </div>

              <!-- Описание -->
              <div class="mb-6">
                <label for="description" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                  {{ $t('notes.description') }}
                </label>
                <textarea
                  id="description"
                  v-model="form.description"
                  rows="3"
                  :placeholder="$t('notes.description_placeholder')"
                  class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-accent-500 focus:border-accent-500 dark:bg-neutral-700 dark:text-white"
                  :class="{ 'border-danger-500': errors.description }"
                ></textarea>
                <div v-if="errors.description" class="mt-2 text-sm text-danger-600 dark:text-danger-400">
                  {{ errors.description }}
                </div>
              </div>

              <!-- Упражнение -->
              <div class="mb-6">
                <label for="exercise_id" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                  {{ $t('notes.associate_exercise') }}
                </label>
                <select
                  id="exercise_id"
                  v-model="form.exercise_id"
                  class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-accent-500 focus:border-accent-500 dark:bg-neutral-700 dark:text-white"
                  :class="{ 'border-danger-500': errors.exercise_id }"
                >
                  <option value="">{{ $t('notes.no_exercise') }}</option>
                  <option v-for="exercise in exercises" :key="exercise.id" :value="exercise.id">
                    {{ exercise.title }}
                  </option>
                </select>
                <div v-if="errors.exercise_id" class="mt-2 text-sm text-danger-600 dark:text-danger-400">
                  {{ errors.exercise_id }}
                </div>
              </div>

              <!-- Публичность -->
              <div class="mb-6">
                <div class="flex items-center">
                  <input
                    id="is_public"
                    v-model="form.is_public"
                    type="checkbox"
                    class="h-4 w-4 text-accent-600 focus:ring-accent-500 border-neutral-300 rounded"
                  />
                  <label for="is_public" class="ml-2 block text-sm text-neutral-700 dark:text-neutral-300">
                    {{ $t('notes.make_public') }}
                  </label>
                </div>
                <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                  {{ $t('notes.public_description') }}
                </p>
              </div>

              <!-- Кнопки действий -->
              <div class="flex justify-end space-x-4">
                <Link
                  :href="route('notes.index')"
                  class="bg-neutral-500 hover:bg-neutral-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200"
                >
                  {{ $t('common.cancel') }}
                </Link>
                <button
                  type="submit"
                  :disabled="!selectedFile || processing"
                  class="bg-accent-500 hover:bg-accent-700 disabled:bg-neutral-400 disabled:cursor-not-allowed text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200"
                >
                  <i v-if="processing" class="fas fa-spinner fa-spin mr-2"></i>
                  <i v-else class="fas fa-upload mr-2"></i>
                  {{ processing ? $t('notes.uploading') : $t('notes.upload') }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { useSimpleI18n } from '@/composables/useSimpleI18n'

interface Exercise {
  id: number
  title: string
}

const props = defineProps<{
  exercises: Exercise[]
}>()

const { t: $t } = useSimpleI18n()

// Реактивные данные
const selectedFile = ref<File | null>(null)
const isDragOver = ref(false)
const processing = ref(false)
const fileInput = ref<HTMLInputElement | null>(null)

// Форма
const form = useForm({
  file: null as File | null,
  title: '',
  description: '',
  exercise_id: '',
  is_public: false,
})

// Ошибки валидации
const errors = reactive({
  file: '',
  title: '',
  description: '',
  exercise_id: '',
})

// Методы
const handleFileSelect = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files && target.files[0]) {
    setSelectedFile(target.files[0])
  }
}

const handleFileDrop = (event: DragEvent) => {
  isDragOver.value = false
  if (event.dataTransfer && event.dataTransfer.files[0]) {
    setSelectedFile(event.dataTransfer.files[0])
  }
}

const setSelectedFile = (file: File) => {
  // Валидация размера файла
  const maxSize = 50 * 1024 * 1024 // 50MB
  if (file.size > maxSize) {
    errors.file = $t('notes.file_too_large')
    return
  }

  // Валидация типа файла
  const allowedTypes = [
    'application/pdf',
    'image/jpeg',
    'image/png',
    'image/gif',
    'image/webp',
    'application/vnd.recordare.musicxml+xml',
    'application/vnd.recordare.musicxml',
    'audio/mpeg',
    'audio/wav',
    'audio/ogg',
  ]
  
  if (!allowedTypes.includes(file.type)) {
    errors.file = $t('notes.unsupported_file_type')
    return
  }

  selectedFile.value = file
  form.file = file
  
  // Автоматически заполняем название, если не указано
  if (!form.title) {
    form.title = file.name.replace(/\.[^/.]+$/, '') // Убираем расширение
  }

  // Очищаем ошибки
  errors.file = ''
}

const removeFile = () => {
  selectedFile.value = null
  form.file = null
  errors.file = ''
}

const getFileTypeIcon = (mimeType: string): string => {
  if (mimeType === 'application/pdf') return 'fas fa-file-pdf text-danger-500'
  if (mimeType.startsWith('image/')) return 'fas fa-image text-success-500'
  if (mimeType.startsWith('audio/')) return 'fas fa-music text-secondary-500'
  if (mimeType.includes('musicxml')) return 'fas fa-file-music text-accent-500'
  return 'fas fa-file text-neutral-500'
}

const formatFileSize = (bytes: number): string => {
  const units = ['B', 'KB', 'MB', 'GB']
  let size = bytes
  let unitIndex = 0
  
  while (size >= 1024 && unitIndex < units.length - 1) {
    size /= 1024
    unitIndex++
  }
  
  return `${size.toFixed(2)} ${units[unitIndex]}`
}

const submitForm = () => {
  if (!selectedFile.value) {
    errors.file = $t('notes.file_required')
    return
  }

  processing.value = true
  
  form.post(route('notes.store'), {
    onSuccess: () => {
      processing.value = false
    },
    onError: (errors) => {
      processing.value = false
      // Обрабатываем ошибки валидации
      Object.assign(errors, errors)
    },
    onFinish: () => {
      processing.value = false
    }
  })
}

// Обработчики drag & drop
const handleDragOver = () => {
  isDragOver.value = true
}

const handleDragLeave = () => {
  isDragOver.value = false
}
</script>

<style scoped>
/* Дополнительные стили для drag & drop */
</style>