<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
                <h2 class="font-semibold text-xl text-primary-800 dark:text-neutral-200 leading-tight">
                    Мои занятия
                </h2>
                <PrimaryButton @click="$inertia.visit(route('sessions.create'))" class="w-full sm:w-auto">
                    <span class="hidden sm:inline">+ Создать занятие</span>
                    <span class="sm:hidden">+ Новое занятие</span>
                </PrimaryButton>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Фильтры -->
                <div class="mb-6 bg-white dark:bg-neutral-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Поиск -->
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                    Поиск
                                </label>
                                <input
                                    v-model="filters.search"
                                    type="text"
                                    placeholder="Название занятия..."
                                    class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-accent-500 focus:border-accent-500 dark:bg-neutral-700 dark:text-white"
                                    @input="applyFilters"
                                />
                            </div>

                            <!-- Упражнение -->
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                    Упражнение
                                </label>
                                <select
                                    v-model="filters.exercise"
                                    class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-accent-500 focus:border-accent-500 dark:bg-neutral-700 dark:text-white"
                                    @change="applyFilters"
                                >
                                    <option value="">Все упражнения</option>
                                    <option v-for="exercise in exercises" :key="exercise" :value="exercise">
                                        {{ exercise }}
                                    </option>
                                </select>
                            </div>

                            <!-- Статус -->
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                    Статус
                                </label>
                                <select
                                    v-model="filters.status"
                                    class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-accent-500 focus:border-accent-500 dark:bg-neutral-700 dark:text-white"
                                    @change="applyFilters"
                                >
                                    <option value="">Все статусы</option>
                                    <option value="planned">Запланировано</option>
                                    <option value="active">Активно</option>
                                    <option value="paused">Приостановлено</option>
                                    <option value="completed">Завершено</option>
                                    <option value="cancelled">Отменено</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Кнопка сброса фильтров -->
                        <div v-if="hasActiveFilters" class="mt-4">
                            <button
                                @click="resetFilters"
                                class="text-sm text-accent-600 dark:text-accent-400 hover:text-accent-800 dark:hover:text-accent-300"
                            >
                                ✕ Сбросить фильтры
                            </button>
                        </div>
                    </div>
                </div>

                <div class="bg-primary-50 overflow-hidden shadow-sm sm:rounded-lg dark:bg-neutral-800 dark:shadow-neutral-900/20">
                    <div class="p-6 text-primary-900 dark:text-neutral-100">
                        <div v-if="sessions.data.length === 0" class="text-center py-12">
                            <div class="text-primary-500 mb-4">
                                <svg class="mx-auto h-12 w-12 text-primary-400 dark:text-neutral-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-primary-900 dark:text-neutral-100 mb-2">У вас пока нет занятий</h3>
                            <p class="text-primary-600 dark:text-neutral-400 mb-4">Создайте ваше первое занятие, чтобы начать отслеживать прогресс</p>
                            <PrimaryButton @click="$inertia.visit(route('sessions.create'))">
                                Создать первое занятие
                            </PrimaryButton>
                        </div>

                        <div v-else class="space-y-4">
                            <div
                                v-for="session in sessions.data"
                                :key="session.id"
                                class="border border-primary-200 dark:border-neutral-700 rounded-lg p-4 sm:p-6 hover:shadow-md dark:hover:shadow-neutral-900/20 transition-shadow"
                            >
                                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex flex-wrap items-center gap-2 mb-2">
                                            <h3 class="text-lg font-medium text-primary-900 dark:text-neutral-100">
                                                {{ session.title }}
                                            </h3>
                                            <span :class="getStatusBadgeClass(session.status)">
                                                {{ getStatusLabel(session.status) }}
                                            </span>
                                        </div>

                                        <p v-if="session.description" class="text-primary-700 dark:text-neutral-300 mb-3 text-sm sm:text-base">
                                            {{ session.description }}
                                        </p>

                                        <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-neutral-500 dark:text-neutral-400">
                                            <div class="flex items-center space-x-1">
                                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <span>{{ session.planned_duration }} мин</span>
                                            </div>

                                            <div v-if="session.actual_duration" class="flex items-center space-x-1">
                                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <span>{{ session.actual_duration }} мин</span>
                                            </div>

                                            <div class="flex items-center space-x-1">
                                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                                </svg>
                                                <span>{{ session.blocks_count }} упр.</span>
                                            </div>

                                            <div v-if="session.template" class="flex items-center space-x-1">
                                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                <span class="truncate">{{ session.template.name }}</span>
                                            </div>
                                        </div>

                                        <div class="mt-3">
                                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                                                <div class="flex items-center space-x-2">
                                                    <span class="text-sm text-neutral-500 dark:text-neutral-400">Прогресс:</span>
                                                    <div class="w-24 sm:w-32 bg-neutral-200 dark:bg-neutral-600 rounded-full h-2">
                                                        <div
                                                            class="bg-accent-600 dark:bg-accent-400 h-2 rounded-full"
                                                            :style="{ width: getProgressPercentage(session) + '%' }"
                                                        ></div>
                                                    </div>
                                                    <span class="text-sm text-neutral-500 dark:text-neutral-400">{{ getProgressPercentage(session) }}%</span>
                                                </div>

                                                <div class="text-xs sm:text-sm text-neutral-500 dark:text-neutral-400">
                                                    {{ formatDate(session.created_at) }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex flex-wrap sm:flex-nowrap gap-2 sm:flex-shrink-0">
                                        <PrimaryButton
                                            @click="$inertia.visit(route('sessions.show', session.id))"
                                            size="sm"
                                            class="flex-1 sm:flex-initial"
                                        >
                                            Открыть
                                        </PrimaryButton>

                                        <SecondaryButton
                                            v-if="session.status === 'planned'"
                                            @click="startSession(session)"
                                            size="sm"
                                            class="flex-1 sm:flex-initial"
                                        >
                                            Начать
                                        </SecondaryButton>

                                        <DangerButton
                                            @click="deleteSession(session)"
                                            size="sm"
                                            class="text-xs sm:flex-initial"
                                        >
                                            🗑️
                                        </DangerButton>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Пагинация -->
                        <div v-if="sessions.data.length > 0 && sessions.links" class="mt-6">
                            <nav class="flex items-center justify-between">
                                <div class="flex-1 flex justify-between sm:hidden">
                                    <Link
                                        v-if="sessions.prev_page_url"
                                        :href="sessions.prev_page_url"
                                        class="relative inline-flex items-center px-4 py-2 border border-neutral-300 dark:border-neutral-600 text-sm font-medium rounded-md text-neutral-700 dark:text-neutral-300 bg-white dark:bg-neutral-800 hover:bg-neutral-50 dark:hover:bg-neutral-700"
                                    >
                                        Предыдущая
                                    </Link>
                                    <Link
                                        v-if="sessions.next_page_url"
                                        :href="sessions.next_page_url"
                                        class="ml-3 relative inline-flex items-center px-4 py-2 border border-neutral-300 dark:border-neutral-600 text-sm font-medium rounded-md text-neutral-700 dark:text-neutral-300 bg-white dark:bg-neutral-800 hover:bg-neutral-50 dark:hover:bg-neutral-700"
                                    >
                                        Следующая
                                    </Link>
                                </div>
                                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                    <div>
                                        <p class="text-sm text-neutral-700 dark:text-neutral-300">
                                            Показано
                                            <span class="font-medium">{{ sessions.from }}</span>
                                            -
                                            <span class="font-medium">{{ sessions.to }}</span>
                                            из
                                            <span class="font-medium">{{ sessions.total }}</span>
                                            результатов
                                        </p>
                                    </div>
                                    <div>
                                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                            <Link
                                                v-for="link in sessions.links"
                                                :key="link.label"
                                                :href="link.url || '#'"
                                                :class="[
                                                    'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                                                    link.active
                                                        ? 'z-10 bg-accent-50 dark:bg-accent-900 border-accent-500 dark:border-accent-400 text-accent-600 dark:text-accent-300'
                                                        : 'bg-white dark:bg-neutral-800 border-neutral-300 dark:border-neutral-600 text-neutral-500 dark:text-neutral-400 hover:bg-neutral-50 dark:hover:bg-neutral-700',
                                                    link.url === null ? 'cursor-not-allowed opacity-50' : ''
                                                ]"
                                                v-html="link.label"
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
import { ref, computed } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import DangerButton from '@/Components/DangerButton.vue'

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

interface Template {
    id: number
    name: string
}

interface Session {
    id: number
    title: string
    description: string
    planned_duration: number
    actual_duration: number | null
    status: string
    created_at: string
    blocks: SessionBlock[]
    blocks_count: number
    template: Template | null
}

interface PaginatedSessions {
    data: Session[]
    current_page: number
    last_page: number
    per_page: number
    total: number
    from: number
    to: number
    prev_page_url: string | null
    next_page_url: string | null
    links: Array<{
        url: string | null
        label: string
        active: boolean
    }>
}

interface Props {
    sessions: PaginatedSessions
    exercises?: string[]
    filters?: {
        search?: string
        exercise?: string
        status?: string
    }
}

const props = defineProps<Props>()

const form = useForm({})

// Фильтры
const filters = ref({
    search: props.filters?.search || '',
    exercise: props.filters?.exercise || '',
    status: props.filters?.status || '',
})

const hasActiveFilters = computed(() => {
    return filters.value.search !== '' || filters.value.exercise !== '' || filters.value.status !== ''
})

let filterTimeout: ReturnType<typeof setTimeout> | null = null

const applyFilters = () => {
    if (filterTimeout) {
        clearTimeout(filterTimeout)
    }
    
    filterTimeout = setTimeout(() => {
        router.get(route('sessions.index'), filters.value, {
            preserveState: true,
            preserveScroll: true,
        })
    }, 300)
}

const resetFilters = () => {
    filters.value = {
        search: '',
        exercise: '',
        status: '',
    }
    router.get(route('sessions.index'), {}, {
        preserveState: true,
        preserveScroll: true,
    })
}

const getStatusLabel = (status: string) => {
    const statusMap = {
        planned: 'Запланировано',
        active: 'Активно',
        paused: 'Приостановлено',
        completed: 'Завершено',
        cancelled: 'Отменено',
    }
    return statusMap[status as keyof typeof statusMap] || status
}

const getStatusBadgeClass = (status: string) => {
    const baseClass = 'px-2 py-1 rounded-full text-xs font-medium'
    const statusClasses = {
        planned: 'bg-primary-100 text-primary-800 dark:bg-neutral-600 dark:text-neutral-200',
        active: 'bg-primary-100 text-primary-800 dark:bg-success-900 dark:text-success-200',
        paused: 'bg-warning-100 text-warning-800 dark:bg-warning-900 dark:text-warning-200',
        completed: 'bg-danger-100 text-danger-800 dark:bg-accent-900 dark:text-accent-200',
        cancelled: 'bg-danger-200 text-danger-800 dark:bg-danger-900 dark:text-danger-200',
    }
    return `${baseClass} ${statusClasses[status as keyof typeof statusClasses] || 'bg-primary-100 text-primary-800 dark:bg-neutral-600 dark:text-neutral-200'}`
}

const getProgressPercentage = (session: Session) => {
    if (session.blocks.length === 0) return 0
    const completedBlocks = session.blocks.filter(block => block.status === 'completed').length
    return Math.round((completedBlocks / session.blocks.length) * 100)
}

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('ru-RU', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    })
}

const startSession = (session: Session) => {
    form.post(route('sessions.start', session.id), {
        preserveScroll: true,
    })
}

const deleteSession = (session: Session) => {
    if (confirm(`Вы уверены, что хотите удалить сессию "${session.title}"? Это действие нельзя отменить.`)) {
        router.delete(route('sessions.destroy', session.id), {
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