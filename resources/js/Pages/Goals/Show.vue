<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-neutral-800 dark:text-neutral-200 leading-tight">
                    {{ goal.title }}
                </h2>
                <div class="flex space-x-2">
                    <Link
                        :href="route('goals.edit', goal.id)"
                        class="bg-accent-500 hover:bg-accent-700 text-white font-bold py-2 px-4 rounded"
                    >
                        Редактировать
                    </Link>
                    <button
                        @click="deleteGoal(goal)"
                        class="bg-danger-500 hover:bg-danger-700 text-white font-bold py-2 px-4 rounded"
                    >
                        Удалить
                    </button>
                    <Link
                        :href="route('goals.index')"
                        class="bg-neutral-500 hover:bg-neutral-700 text-white font-bold py-2 px-4 rounded"
                    >
                        Назад к целям
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Основная информация -->
                    <div class="lg:col-span-2">
                        <div class="bg-white dark:bg-neutral-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex items-start mb-6">
                                    <span class="text-4xl mr-4">{{ goal.type_icon }}</span>
                                    <div class="flex-1">
                                        <h3 class="text-2xl font-bold text-neutral-900 dark:text-neutral-100 mb-2">
                                            {{ goal.title }}
                                        </h3>
                                        <div class="flex items-center space-x-4 text-sm text-neutral-500 dark:text-neutral-400">
                                            <span class="flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                </svg>
                                                {{ goal.type_label }}
                                            </span>
                                            <span class="flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                                                </svg>
                                                {{ formatDate(goal.start_date) }} - {{ goal.end_date ? formatDate(goal.end_date) : 'Бессрочная' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="goal.description" class="mb-6">
                                    <h4 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-2">
                                        Описание
                                    </h4>
                                    <p class="text-neutral-600 dark:text-neutral-300">
                                        {{ goal.description }}
                                    </p>
                                </div>

                                <!-- Прогресс -->
                                <div class="mb-6">
                                    <h4 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-4">
                                        Прогресс
                                    </h4>
                                    <div class="bg-neutral-100 dark:bg-neutral-700 rounded-lg p-4">
                                        <div class="flex justify-between items-center mb-2">
                                            <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">
                                                Выполнено
                                            </span>
                                            <span class="text-lg font-bold text-neutral-900 dark:text-neutral-100">
                                                {{ goal.progress_percentage }}%
                                            </span>
                                        </div>
                                        <div class="w-full bg-neutral-200 dark:bg-neutral-600 rounded-full h-4 mb-2">
                                            <div
                                                class="bg-accent-500 h-4 rounded-full transition-all duration-500"
                                                :style="{ width: goal.progress_percentage + '%' }"
                                            ></div>
                                        </div>
                                        <div class="flex justify-between text-sm text-neutral-600 dark:text-neutral-400">
                                            <span>{{ goal.current_value }} / {{ goal.target_value }}</span>
                                            <span v-if="goal.remaining > 0">
                                                Осталось: {{ goal.remaining }}
                                            </span>
                                            <span v-else class="text-success-600 dark:text-success-400 font-semibold">
                                                Цель достигнута!
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Действия -->
                                <div class="flex space-x-4">
                                    <button
                                        @click="updateProgress"
                                        class="bg-success-500 hover:bg-success-700 text-white font-bold py-2 px-4 rounded"
                                    >
                                        Обновить прогресс
                                    </button>
                                    <button
                                        v-if="!goal.is_completed"
                                        @click="completeGoal"
                                        class="bg-secondary-500 hover:bg-secondary-700 text-white font-bold py-2 px-4 rounded"
                                    >
                                        Отметить как выполненную
                                    </button>
                                    <button
                                        @click="toggleGoal"
                                        :class="goal.is_active ? 'bg-primary-500 hover:bg-primary-700' : 'bg-accent-500 hover:bg-accent-700'"
                                        class="text-white font-bold py-2 px-4 rounded"
                                    >
                                        {{ goal.is_active ? 'Деактивировать' : 'Активировать' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Боковая панель -->
                    <div class="space-y-6">
                        <!-- Статус -->
                        <div class="bg-white dark:bg-neutral-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h4 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-4">
                                    Статус
                                </h4>
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-neutral-600 dark:text-neutral-400">Активна</span>
                                        <span :class="goal.is_active ? 'text-success-600 dark:text-success-400' : 'text-danger-600 dark:text-danger-400'">
                                            {{ goal.is_active ? 'Да' : 'Нет' }}
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-neutral-600 dark:text-neutral-400">Выполнена</span>
                                        <span :class="goal.is_completed ? 'text-success-600 dark:text-success-400' : 'text-neutral-600 dark:text-neutral-400'">
                                            {{ goal.is_completed ? 'Да' : 'Нет' }}
                                        </span>
                                    </div>
                                    <div v-if="goal.completed_at" class="flex items-center justify-between">
                                        <span class="text-sm text-neutral-600 dark:text-neutral-400">Завершена</span>
                                        <span class="text-sm text-neutral-900 dark:text-neutral-100">
                                            {{ formatDate(goal.completed_at) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Информация -->
                        <div class="bg-white dark:bg-neutral-800 overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h4 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100 mb-4">
                                    Информация
                                </h4>
                                <div class="space-y-3 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-neutral-600 dark:text-neutral-400">Создана</span>
                                        <span class="text-neutral-900 dark:text-neutral-100">
                                            {{ formatDate(goal.created_at) }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-neutral-600 dark:text-neutral-400">Тип</span>
                                        <span class="text-neutral-900 dark:text-neutral-100">
                                            {{ goal.type_label }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-neutral-600 dark:text-neutral-400">Цель</span>
                                        <span class="text-neutral-900 dark:text-neutral-100">
                                            {{ goal.target_value }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Модальное окно для обновления прогресса -->
        <div v-if="showProgressModal" class="fixed inset-0 bg-neutral-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-neutral-800">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-neutral-900 dark:text-neutral-100 mb-4">
                        Обновить прогресс
                    </h3>
                    <form @submit.prevent="submitProgress">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                Текущее значение
                            </label>
                            <input
                                v-model.number="progressForm.current"
                                type="number"
                                min="0"
                                class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-accent-500 focus:border-accent-500 dark:bg-neutral-700 dark:text-white"
                                required
                            />
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                Общее значение (опционально)
                            </label>
                            <input
                                v-model.number="progressForm.total"
                                type="number"
                                min="1"
                                class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-accent-500 focus:border-accent-500 dark:bg-neutral-700 dark:text-white"
                            />
                        </div>
                        <div class="flex justify-end space-x-3">
                            <button
                                type="button"
                                @click="showProgressModal = false"
                                class="bg-neutral-500 hover:bg-neutral-700 text-white font-bold py-2 px-4 rounded"
                            >
                                Отмена
                            </button>
                            <button
                                type="submit"
                                :disabled="progressForm.processing"
                                class="bg-accent-500 hover:bg-accent-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50"
                            >
                                {{ progressForm.processing ? 'Обновление...' : 'Обновить' }}
                            </button>
                        </div>
                    </form>
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
                            Вы уверены, что хотите удалить цель "{{ goal.title }}"? 
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
import { Link, useForm, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { ref } from 'vue'

const props = defineProps({
    goal: Object,
})

const showProgressModal = ref(false)
const showDeleteModal = ref(false)

const progressForm = useForm({
    current: props.goal.current_value,
    total: props.goal.target_value,
})

const updateProgress = () => {
    progressForm.current = props.goal.current_value
    progressForm.total = props.goal.target_value
    showProgressModal.value = true
}

const submitProgress = () => {
    progressForm.patch(route('goals.progress', props.goal.id), {
        onSuccess: () => {
            showProgressModal.value = false
        }
    })
}

const completeGoal = () => {
    if (confirm('Отметить цель как выполненную?')) {
        progressForm.post(route('goals.complete', props.goal.id))
    }
}

const toggleGoal = () => {
    progressForm.post(route('goals.toggle', props.goal.id))
}

const deleteGoal = (goal) => {
    showDeleteModal.value = true
}

const confirmDelete = () => {
    router.delete(route('goals.destroy', props.goal.id), {
        onSuccess: () => {
            showDeleteModal.value = false
        }
    })
}

const cancelDelete = () => {
    showDeleteModal.value = false
}

const formatDate = (dateString) => {
    if (!dateString) return ''
    return new Date(dateString).toLocaleDateString('ru-RU')
}
</script>