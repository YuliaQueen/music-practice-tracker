<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <div class="flex items-center space-x-4">
          <Link
            :href="route('notes.show', note.id)"
            class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
          >
            <i class="fas fa-arrow-left"></i>
          </Link>
          <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $t('notes.edit_note') }}
          </h2>
        </div>
        <Link
          :href="route('notes.index')"
          class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200"
        >
          <i class="fas fa-times mr-2"></i>
          {{ $t('common.cancel') }}
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <!-- Информация о текущем файле -->
            <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
              <div class="flex items-center space-x-4">
                <div class="flex items-center justify-center w-12 h-12 rounded-full bg-gray-200 dark:bg-gray-600">
                  <i :class="getFileTypeIcon(note.mime_type)" class="text-xl"></i>
                </div>
                <div>
                  <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    {{ note.filename }}
                  </h3>
                  <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ note.formatted_file_size }} • {{ getFileTypeName(note.mime_type) }}
                  </p>
                </div>
              </div>
            </div>

            <form @submit.prevent="submitForm">
              <!-- Название -->
              <div class="mb-6">
                <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  {{ $t('notes.title') }} <span class="text-red-500">*</span>
                </label>
                <input
                  id="title"
                  v-model="form.title"
                  type="text"
                  :placeholder="$t('notes.title_placeholder')"
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                  :class="{ 'border-red-500': errors.title }"
                />
                <div v-if="errors.title" class="mt-2 text-sm text-red-600 dark:text-red-400">
                  {{ errors.title }}
                </div>
              </div>

              <!-- Описание -->
              <div class="mb-6">
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  {{ $t('notes.description') }}
                </label>
                <textarea
                  id="description"
                  v-model="form.description"
                  rows="4"
                  :placeholder="$t('notes.description_placeholder')"
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                  :class="{ 'border-red-500': errors.description }"
                ></textarea>
                <div v-if="errors.description" class="mt-2 text-sm text-red-600 dark:text-red-400">
                  {{ errors.description }}
                </div>
              </div>

              <!-- Упражнение -->
              <div class="mb-6">
                <label for="exercise_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  {{ $t('notes.associate_exercise') }}
                </label>
                <select
                  id="exercise_id"
                  v-model="form.exercise_id"
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                  :class="{ 'border-red-500': errors.exercise_id }"
                >
                  <option value="">{{ $t('notes.no_exercise') }}</option>
                  <option v-for="exercise in exercises" :key="exercise.id" :value="exercise.id">
                    {{ exercise.title }}
                  </option>
                </select>
                <div v-if="errors.exercise_id" class="mt-2 text-sm text-red-600 dark:text-red-400">
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
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                  />
                  <label for="is_public" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
                    {{ $t('notes.make_public') }}
                  </label>
                </div>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                  {{ $t('notes.public_description') }}
                </p>
              </div>

              <!-- Дополнительные действия -->
              <div class="mb-6 p-4 bg-yellow-50 dark:bg-yellow-900 rounded-lg">
                <h4 class="text-sm font-medium text-yellow-800 dark:text-yellow-200 mb-2">
                  {{ $t('notes.file_actions') }}
                </h4>
                <div class="space-y-2">
                  <button
                    type="button"
                    @click="downloadNote"
                    class="flex items-center text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200"
                  >
                    <i class="fas fa-download mr-2"></i>
                    {{ $t('notes.download_current_file') }}
                  </button>
                  <p class="text-xs text-yellow-700 dark:text-yellow-300">
                    {{ $t('notes.replace_file_note') }}
                  </p>
                </div>
              </div>

              <!-- Кнопки действий -->
              <div class="flex justify-between">
                <div class="flex space-x-4">
                  <button
                    type="button"
                    @click="deleteNote"
                    class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200"
                  >
                    <i class="fas fa-trash mr-2"></i>
                    {{ $t('notes.delete') }}
                  </button>
                </div>
                
                <div class="flex space-x-4">
                  <Link
                    :href="route('notes.show', note.id)"
                    class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200"
                  >
                    {{ $t('common.cancel') }}
                  </Link>
                  <button
                    type="submit"
                    :disabled="processing"
                    class="bg-blue-500 hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200"
                  >
                    <i v-if="processing" class="fas fa-spinner fa-spin mr-2"></i>
                    <i v-else class="fas fa-save mr-2"></i>
                    {{ processing ? $t('notes.saving') : $t('notes.save_changes') }}
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'
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
  exercise_id?: number
  is_public: boolean
}

interface Exercise {
  id: number
  title: string
}

const props = defineProps<{
  note: Note
  exercises: Exercise[]
}>()

const { t: $t } = useSimpleI18n()

// Реактивные данные
const processing = ref(false)

// Форма
const form = useForm({
  title: props.note.title,
  description: props.note.description || '',
  exercise_id: props.note.exercise_id || '',
  is_public: props.note.is_public,
})

// Ошибки валидации
const errors = reactive({
  title: '',
  description: '',
  exercise_id: '',
})

// Методы
const getFileTypeIcon = (mimeType: string): string => {
  if (mimeType === 'application/pdf') return 'fas fa-file-pdf text-red-500'
  if (mimeType.startsWith('image/')) return 'fas fa-image text-green-500'
  if (mimeType.startsWith('audio/')) return 'fas fa-music text-purple-500'
  if (mimeType.includes('musicxml')) return 'fas fa-file-music text-blue-500'
  return 'fas fa-file text-gray-500'
}

const getFileTypeName = (mimeType: string): string => {
  if (mimeType === 'application/pdf') return 'PDF Document'
  if (mimeType.startsWith('image/')) return 'Image'
  if (mimeType.startsWith('audio/')) return 'Audio'
  if (mimeType.includes('musicxml')) return 'MusicXML'
  return 'File'
}

const submitForm = () => {
  processing.value = true
  
  form.put(route('notes.update', props.note.id), {
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

const downloadNote = () => {
  window.open(route('notes.download', props.note.id), '_blank')
}

const deleteNote = () => {
  if (confirm($t('notes.confirm_delete'))) {
    router.delete(route('notes.destroy', props.note.id), {
      onSuccess: () => {
        router.visit(route('notes.index'))
      }
    })
  }
}
</script>