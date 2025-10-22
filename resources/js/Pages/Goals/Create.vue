<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-neutral-800 dark:text-neutral-200 leading-tight">
                    Создать цель
                </h2>
                <Link
                    :href="route('goals.index')"
                    class="bg-neutral-500 hover:bg-neutral-700 text-white font-bold py-2 px-4 rounded"
                >
                    Назад к целям
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-neutral-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-neutral-900 dark:text-neutral-100">
                        <form @submit.prevent="submit">
                            <div class="mb-6">
                                <label for="title" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                    Название цели *
                                </label>
                                <input
                                    id="title"
                                    v-model="form.title"
                                    type="text"
                                    class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-accent-500 focus:border-accent-500 dark:bg-neutral-700 dark:text-white"
                                    :class="{ 'border-danger-500': form.errors.title }"
                                    required
                                />
                                <div v-if="form.errors.title" class="mt-1 text-sm text-danger-600 dark:text-danger-400">
                                    {{ form.errors.title }}
                                </div>
                            </div>

                            <div class="mb-6">
                                <label for="description" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                    Описание
                                </label>
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    rows="3"
                                    class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-accent-500 focus:border-accent-500 dark:bg-neutral-700 dark:text-white"
                                    :class="{ 'border-danger-500': form.errors.description }"
                                ></textarea>
                                <div v-if="form.errors.description" class="mt-1 text-sm text-danger-600 dark:text-danger-400">
                                    {{ form.errors.description }}
                                </div>
                            </div>

                            <div class="mb-6">
                                <label for="type" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                    Тип цели *
                                </label>
                                <select
                                    id="type"
                                    v-model="form.type"
                                    class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-accent-500 focus:border-accent-500 dark:bg-neutral-700 dark:text-white"
                                    :class="{ 'border-danger-500': form.errors.type }"
                                    required
                                >
                                    <option value="">Выберите тип цели</option>
                                    <option value="daily_minutes">Ежедневные минуты</option>
                                    <option value="weekly_sessions">Еженедельные сессии</option>
                                    <option value="streak_days">Серия дней</option>
                                    <option value="exercise_type">Тип упражнений</option>
                                    <option value="monthly_minutes">Ежемесячные минуты</option>
                                    <option value="yearly_sessions">Годовые сессии</option>
                                </select>
                                <div v-if="form.errors.type" class="mt-1 text-sm text-danger-600 dark:text-danger-400">
                                    {{ form.errors.type }}
                                </div>
                            </div>

                            <div class="mb-6">
                                <label for="target_value" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                    Целевое значение *
                                </label>
                                <input
                                    id="target_value"
                                    v-model.number="form.target.value"
                                    type="number"
                                    min="1"
                                    class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-accent-500 focus:border-accent-500 dark:bg-neutral-700 dark:text-white"
                                    :class="{ 'border-danger-500': form.errors['target.value'] }"
                                    required
                                />
                                <div v-if="form.errors['target.value']" class="mt-1 text-sm text-danger-600 dark:text-danger-400">
                                    {{ form.errors['target.value'] }}
                                </div>
                                <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                                    {{ getTargetDescription() }}
                                </p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label for="start_date" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                        Дата начала *
                                    </label>
                                    <input
                                        id="start_date"
                                        v-model="form.start_date"
                                        type="date"
                                        class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-accent-500 focus:border-accent-500 dark:bg-neutral-700 dark:text-white"
                                        :class="{ 'border-danger-500': form.errors.start_date }"
                                        required
                                    />
                                    <div v-if="form.errors.start_date" class="mt-1 text-sm text-danger-600 dark:text-danger-400">
                                        {{ form.errors.start_date }}
                                    </div>
                                </div>

                                <div>
                                    <label for="end_date" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                                        Дата окончания
                                    </label>
                                    <input
                                        id="end_date"
                                        v-model="form.end_date"
                                        type="date"
                                        class="w-full px-3 py-2 border border-neutral-300 dark:border-neutral-600 rounded-md shadow-sm focus:outline-none focus:ring-accent-500 focus:border-accent-500 dark:bg-neutral-700 dark:text-white"
                                        :class="{ 'border-danger-500': form.errors.end_date }"
                                    />
                                    <div v-if="form.errors.end_date" class="mt-1 text-sm text-danger-600 dark:text-danger-400">
                                        {{ form.errors.end_date }}
                                    </div>
                                    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-400">
                                        Оставьте пустым для бессрочной цели
                                    </p>
                                </div>
                            </div>

                            <div class="flex justify-end space-x-4">
                                <Link
                                    :href="route('goals.index')"
                                    class="bg-neutral-500 hover:bg-neutral-700 text-white font-bold py-2 px-4 rounded"
                                >
                                    Отмена
                                </Link>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="bg-accent-500 hover:bg-accent-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50"
                                >
                                    {{ form.processing ? 'Создание...' : 'Создать цель' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const form = useForm({
    title: '',
    description: '',
    type: '',
    target: {
        value: 1,
    },
    start_date: new Date().toISOString().split('T')[0],
    end_date: '',
})

const submit = () => {
    form.post(route('goals.store'))
}

const getTargetDescription = () => {
    const descriptions = {
        daily_minutes: 'минут в день',
        weekly_sessions: 'сессий в неделю',
        streak_days: 'дней подряд',
        exercise_type: 'минут определенного типа упражнений',
        monthly_minutes: 'минут в месяц',
        yearly_sessions: 'сессий в год',
    }
    
    return descriptions[form.type] || 'единиц'
}
</script>