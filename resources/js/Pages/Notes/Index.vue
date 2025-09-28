<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
          {{ $t('notes.title') }}
        </h2>
        <Link
          :href="route('notes.create')"
          class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200"
        >
          <i class="fas fa-plus mr-2"></i>
          {{ $t('notes.upload_new') }}
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Фильтры -->
        <div class="mb-6 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
              <!-- Поиск -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  {{ $t('notes.search') }}
                </label>
                <input
                  v-model="searchQuery"
                  type="text"
                  :placeholder="$t('notes.search_placeholder')"
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                />
              </div>

              <!-- Тип файла -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  {{ $t('notes.file_type') }}
                </label>
                <select
                  v-model="selectedFileType"
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                >
                  <option value="">{{ $t('notes.all_types') }}</option>
                  <option value="application/pdf">PDF</option>
                  <option value="image/">{{ $t('notes.images') }}</option>
                  <option value="audio/">{{ $t('notes.audio') }}</option>
                  <option value="application/vnd.recordare.musicxml">MusicXML</option>
                </select>
              </div>

              <!-- Упражнение -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  {{ $t('notes.exercise') }}
                </label>
                <select
                  v-model="selectedExercise"
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                >
                  <option value="">{{ $t('notes.all_exercises') }}</option>
                  <option v-for="exercise in exercises" :key="exercise.id" :value="exercise.id">
                    {{ exercise.title }}
                  </option>
                </select>
              </div>

              <!-- Видимость -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  {{ $t('notes.visibility') }}
                </label>
                <select
                  v-model="selectedVisibility"
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                >
                  <option value="">{{ $t('notes.all_notes') }}</option>
                  <option value="true">{{ $t('notes.public') }}</option>
                  <option value="false">{{ $t('notes.private') }}</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- Список нот -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6">
            <div v-if="filteredNotes.length === 0" class="text-center py-8">
              <i class="fas fa-music text-6xl text-gray-400 mb-4"></i>
              <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                {{ $t('notes.no_notes') }}
              </h3>
              <p class="text-gray-500 dark:text-gray-400 mb-4">
                {{ $t('notes.no_notes_description') }}
              </p>
              <Link
                :href="route('notes.create')"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-200"
              >
                {{ $t('notes.upload_first') }}
              </Link>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              <div
                v-for="note in filteredNotes"
                :key="note.id"
                class="bg-white dark:bg-gray-700 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200 border border-gray-200 dark:border-gray-600"
              >
                <!-- Заголовок карточки -->
                <div class="p-4 border-b border-gray-200 dark:border-gray-600">
                  <div class="flex items-start justify-between">
                    <div class="flex-1">
                      <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">
                        {{ note.title }}
                      </h3>
                      <p v-if="note.exercise" class="text-sm text-blue-600 dark:text-blue-400">
                        <i class="fas fa-dumbbell mr-1"></i>
                        {{ note.exercise.title }}
                      </p>
                    </div>
                    <div class="flex items-center space-x-2">
                      <!-- Иконка типа файла -->
                      <div class="flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-600" :title="getFileTypeName(note.mime_type)">
                        <i :class="getFileTypeIcon(note.mime_type)" class="text-gray-600 dark:text-gray-300"></i>
                      </div>
                      <!-- Публичность -->
                      <div v-if="note.is_public" class="flex items-center justify-center w-8 h-8 rounded-full bg-green-100 dark:bg-green-900" :title="$t('notes.public')">
                        <i class="fas fa-globe text-green-600 dark:text-green-400 text-sm"></i>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Описание -->
                <div v-if="note.description" class="p-4">
                  <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2">
                    {{ note.description }}
                  </p>
                </div>

                <!-- Метаданные -->
                <div class="px-4 pb-4">
                  <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                    <span>{{ note.formatted_file_size }}</span>
                    <span>{{ formatDate(note.created_at) }}</span>
                  </div>
                </div>

                <!-- Действия -->
                <div class="px-4 pb-4">
                  <div class="flex space-x-2">
                    <Link
                      :href="route('notes.show', note.id)"
                      class="flex-1 bg-blue-500 hover:bg-blue-700 text-white text-sm font-medium py-2 px-3 rounded-md transition-colors duration-200 text-center"
                    >
                      <i class="fas fa-eye mr-1"></i>
                      {{ $t('notes.view') }}
                    </Link>
                    <button
                      @click="downloadNote(note)"
                      class="bg-gray-500 hover:bg-gray-700 text-white text-sm font-medium py-2 px-3 rounded-md transition-colors duration-200"
                      :title="$t('notes.download')"
                    >
                      <i class="fas fa-download mr-1"></i>
                      {{ $t('notes.download') }}
                    </button>
                    <button
                      @click="deleteNote(note)"
                      class="bg-red-500 hover:bg-red-700 text-white text-sm font-medium py-2 px-3 rounded-md transition-colors duration-200"
                      :title="$t('notes.delete')"
                    >
                      <i class="fas fa-trash mr-1"></i>
                      {{ $t('notes.delete') }}
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Пагинация -->
            <div v-if="notes.links && notes.links.length > 3" class="mt-6">
              <nav class="flex items-center justify-between">
                <div class="flex-1 flex justify-between sm:hidden">
                  <Link
                    v-if="notes.prev_page_url"
                    :href="notes.prev_page_url"
                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                  >
                    {{ $t('pagination.previous') }}
                  </Link>
                  <Link
                    v-if="notes.next_page_url"
                    :href="notes.next_page_url"
                    class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                  >
                    {{ $t('pagination.next') }}
                  </Link>
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                  <div>
                    <p class="text-sm text-gray-700 dark:text-gray-300">
                      {{ $t('pagination.showing') }}
                      <span class="font-medium">{{ notes.from }}</span>
                      {{ $t('pagination.to') }}
                      <span class="font-medium">{{ notes.to }}</span>
                      {{ $t('pagination.of') }}
                      <span class="font-medium">{{ notes.total }}</span>
                      {{ $t('pagination.results') }}
                    </p>
                  </div>
                  <div>
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                      <Link
                        v-for="link in notes.links"
                        :key="link.label"
                        :href="link.url"
                        v-html="link.label"
                        :class="[
                          'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                          link.active
                            ? 'z-10 bg-blue-50 border-blue-500 text-blue-600 dark:bg-blue-900 dark:text-blue-200'
                            : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-600',
                          link.url === null ? 'cursor-not-allowed opacity-50' : 'cursor-pointer'
                        ]"
                      />
                    </nav>
                  </div>
                </div>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
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

interface Exercise {
  id: number
  title: string
}

interface NotesData {
  data: Note[]
  links: any[]
  from: number
  to: number
  total: number
  prev_page_url: string | null
  next_page_url: string | null
}

const props = defineProps<{
  notes: NotesData
  exercises: Exercise[]
}>()

const { t: $t } = useSimpleI18n()

// Реактивные данные
const searchQuery = ref('')
const selectedFileType = ref('')
const selectedExercise = ref('')
const selectedVisibility = ref('')

// Вычисляемые свойства
const filteredNotes = computed(() => {
  let filtered = props.notes.data

  // Поиск по названию и описанию
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(note => 
      note.title.toLowerCase().includes(query) ||
      (note.description && note.description.toLowerCase().includes(query))
    )
  }

  // Фильтр по типу файла
  if (selectedFileType.value) {
    filtered = filtered.filter(note => {
      if (selectedFileType.value === 'image/') {
        return note.mime_type.startsWith('image/')
      }
      if (selectedFileType.value === 'audio/') {
        return note.mime_type.startsWith('audio/')
      }
      return note.mime_type === selectedFileType.value
    })
  }

  // Фильтр по упражнению
  if (selectedExercise.value) {
    filtered = filtered.filter(note => 
      note.exercise?.id === parseInt(selectedExercise.value)
    )
  }

  // Фильтр по видимости
  if (selectedVisibility.value !== '') {
    const isPublic = selectedVisibility.value === 'true'
    filtered = filtered.filter(note => note.is_public === isPublic)
  }

  return filtered
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

const formatDate = (dateString: string): string => {
  return new Date(dateString).toLocaleDateString()
}

const downloadNote = (note: Note) => {
  window.open(route('notes.download', note.id), '_blank')
}

const deleteNote = (note: Note) => {
  if (confirm($t('notes.confirm_delete'))) {
    router.delete(route('notes.destroy', note.id), {
      onSuccess: () => {
        // Обновляем список после удаления
        router.reload()
      }
    })
  }
}

onMounted(() => {
  // Загружаем упражнения для фильтра
  // Они уже переданы через props
})
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>