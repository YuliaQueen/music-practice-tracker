<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-primary-800 dark:text-neutral-200 leading-tight">
                    Мои цели
                </h2>
                <Link
                    :href="route('goals.create')"
                    class="bg-primary-500 hover:bg-primary-700 text-white font-bold py-2 px-4 rounded"
                >
                    Создать цель
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-primary-50 dark:bg-neutral-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-primary-900 dark:text-neutral-100">
                        <div v-if="goals.length === 0" class="text-center py-8">
                            <div class="text-primary-500 dark:text-neutral-400 mb-4">
                                <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-primary-900 dark:text-neutral-100 mb-2">
                                У вас пока нет целей
                            </h3>
                            <p class="text-primary-600 dark:text-neutral-400 mb-4">
                                Создайте свою первую цель для отслеживания прогресса в музыке
                            </p>
                            <Link
                                :href="route('goals.create')"
                                class="bg-primary-500 hover:bg-primary-700 text-white font-bold py-2 px-4 rounded"
                            >
                                Создать цель
                            </Link>
                        </div>

                        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div
                                v-for="goal in goals"
                                :key="goal.id"
                                class="bg-primary-50 dark:bg-neutral-700 rounded-lg shadow-md p-6 border border-primary-200 dark:border-neutral-600"
                            >
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center">
                                        <span class="text-2xl mr-3">{{ goal.type_icon }}</span>
                                        <div>
                                            <h3 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">
                                                {{ goal.title }}
                                            </h3>
                                            <p class="text-sm text-neutral-500 dark:text-neutral-400">
                                                {{ goal.type_label }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex space-x-2">
                                        <button
                                            @click="toggleGoal(goal)"
                                            class="text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-300"
                                            :title="goal.is_active ? 'Деактивировать' : 'Активировать'"
                                        >
                                            <svg v-if="goal.is_active" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                            <svg v-else class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                        <Link
                                            :href="route('goals.show', goal.id)"
                                            class="text-accent-500 hover:text-accent-700 mr-2"
                                            title="Просмотреть"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </Link>
                                        <Link
                                            :href="route('goals.edit', goal.id)"
                                            class="text-success-500 hover:text-success-700 mr-2"
                                            title="Редактировать"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </Link>
                                        <button
                                            @click="deleteGoal(goal)"
                                            class="text-danger-500 hover:text-danger-700"
                                            title="Удалить"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <div v-if="goal.description" class="mb-4">
                                    <p class="text-sm text-neutral-600 dark:text-neutral-300">
                                        {{ goal.description }}
                                    </p>
                                </div>

                                <div class="mb-4">
                                    <div class="flex justify-between text-sm text-neutral-600 dark:text-neutral-300 mb-1">
                                        <span>Прогресс</span>
                                        <span>{{ goal.progress_percentage }}%</span>
                                    </div>
                                    <div class="w-full bg-neutral-200 dark:bg-neutral-600 rounded-full h-2">
                                        <div
                                            class="bg-accent-500 h-2 rounded-full transition-all duration-300"
                                            :style="{ width: goal.progress_percentage + '%' }"
                                        ></div>
                                    </div>
                                    <div class="flex justify-between text-xs text-neutral-500 dark:text-neutral-400 mt-1">
                                        <span>{{ goal.progress?.current || 0 }} / {{ goal.target.value }}</span>
                                        <span v-if="goal.remaining > 0">Осталось: {{ goal.remaining }}</span>
                                    </div>
                                </div>

                                <div class="flex justify-between items-center text-sm text-neutral-500 dark:text-neutral-400">
                                    <span>{{ formatDate(goal.start_date) }}</span>
                                    <span v-if="goal.end_date">до {{ formatDate(goal.end_date) }}</span>
                                    <span v-else class="text-success-500">Бессрочная</span>
                                </div>

                                <div v-if="goal.is_completed" class="mt-3 p-2 bg-success-100 dark:bg-success-900 rounded text-success-800 dark:text-success-200 text-sm">
                                    ✅ Цель выполнена {{ formatDate(goal.completed_at) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Модальное окно подтверждения удаления -->
        <div v-if="showDeleteModal" class="fixed inset-0 bg-neutral-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-neutral-800">
                <div class="mt-3 text-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-danger-100 dark:bg-danger-900">
                        <svg class="h-6 w-6 text-danger-600 dark:text-danger-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100 mt-4">
                        Удалить цель?
                    </h3>
                    <div class="mt-2 px-7 py-3">
                        <p class="text-sm text-neutral-500 dark:text-neutral-400">
                            Вы уверены, что хотите удалить цель "{{ goalToDelete?.title }}"? 
                            Это действие нельзя отменить.
                        </p>
                    </div>
                    <div class="flex justify-center space-x-4 mt-4">
                        <button
                            @click="cancelDelete"
                            class="bg-neutral-300 hover:bg-neutral-400 text-neutral-800 font-bold py-2 px-4 rounded"
                        >
                            Отмена
                        </button>
                        <button
                            @click="confirmDelete"
                            class="bg-danger-500 hover:bg-danger-700 text-white font-bold py-2 px-4 rounded"
                        >
                            Удалить
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { ref } from 'vue'

const props = defineProps({
    goals: Array,
})

const showDeleteModal = ref(false)
const goalToDelete = ref(null)

const toggleGoal = (goal) => {
    // Простая функция для переключения статуса цели
    // В реальном приложении здесь будет AJAX запрос
    goal.is_active = !goal.is_active
}

const deleteGoal = (goal) => {
    goalToDelete.value = goal
    showDeleteModal.value = true
}

const confirmDelete = () => {
    if (goalToDelete.value) {
        router.delete(route('goals.destroy', goalToDelete.value.id), {
            onSuccess: () => {
                showDeleteModal.value = false
                goalToDelete.value = null
            }
        })
    }
}

const cancelDelete = () => {
    showDeleteModal.value = false
    goalToDelete.value = null
}

const formatDate = (dateString) => {
    if (!dateString) return ''
    return new Date(dateString).toLocaleDateString('ru-RU')
}
</script>