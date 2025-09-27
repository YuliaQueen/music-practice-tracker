<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Создать упражнение
                </h2>
                <PrimaryButton @click="router.visit('/exercises')">
                    ← Назад к упражнениям
                </PrimaryButton>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg dark:bg-gray-800 dark:shadow-gray-900/20">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <form @submit.prevent="submit">
                            <div class="space-y-6">
                                <!-- Название -->
                                <div>
                                    <InputLabel for="title" value="Название упражнения" />
                                    <TextInput
                                        id="title"
                                        v-model="form.title"
                                        type="text"
                                        class="mt-1 block w-full"
                                        required
                                        autofocus
                                        placeholder="Например: Гаммы до мажор"
                                    />
                                    <InputError class="mt-2" :message="form.errors.title" />
                                </div>

                                <!-- Описание -->
                                <div>
                                    <InputLabel for="description" value="Описание (опционально)" />
                                    <textarea
                                        id="description"
                                        v-model="form.description"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:focus:border-indigo-400 dark:focus:ring-indigo-400"
                                        rows="3"
                                        placeholder="Дополнительные детали или цели упражнения"
                                    ></textarea>
                                    <InputError class="mt-2" :message="form.errors.description" />
                                </div>

                                <!-- Тип упражнения -->
                                <div>
                                    <InputLabel for="type" value="Тип упражнения" />
                                    <select
                                        id="type"
                                        v-model="form.type"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 dark:focus:border-indigo-400 dark:focus:ring-indigo-400"
                                        required
                                    >
                                        <option value="warmup">🔥 Разминка</option>
                                        <option value="technique">⚡ Техника</option>
                                        <option value="repertoire">🎵 Репертуар</option>
                                        <option value="improvisation">🎨 Импровизация</option>
                                        <option value="sight_reading">👀 Чтение с листа</option>
                                        <option value="theory">📚 Теория</option>
                                        <option value="break">☕ Перерыв</option>
                                        <option value="custom">⭐ Пользовательский</option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.type" />
                                </div>

                                <!-- Длительность -->
                                <div>
                                    <InputLabel for="planned_duration" value="Запланированная длительность (минуты)" />
                                    <TextInput
                                        id="planned_duration"
                                        v-model="form.planned_duration"
                                        type="number"
                                        min="1"
                                        max="480"
                                        class="mt-1 block w-full"
                                        required
                                        placeholder="5"
                                    />
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">От 1 до 480 минут (8 часов)</p>
                                    <InputError class="mt-2" :message="form.errors.planned_duration" />
                                </div>

                                <!-- Запланированное время -->
                                <div>
                                    <InputLabel for="scheduled_for" value="Запланированное время (опционально)" />
                                    <TextInput
                                        id="scheduled_for"
                                        v-model="form.scheduled_for"
                                        type="datetime-local"
                                        class="mt-1 block w-full"
                                    />
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Если не указано, упражнение можно начать в любое время</p>
                                    <InputError class="mt-2" :message="form.errors.scheduled_for" />
                                </div>
                            </div>

                            <!-- Кнопки -->
                            <div class="flex items-center justify-end mt-6 space-x-3">
                                <SecondaryButton @click="router.visit('/exercises')" type="button">
                                    Отмена
                                </SecondaryButton>
                                <PrimaryButton :disabled="form.processing">
                                    {{ form.processing ? 'Создание...' : 'Создать упражнение' }}
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup lang="ts">
import { useForm, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import TextInput from '@/Components/TextInput.vue'

const form = useForm({
    title: '',
    description: '',
    type: 'custom',
    planned_duration: '5',
    scheduled_for: '',
})

const submit = () => {
    form.post(route('exercises.store'), {
        onSuccess: () => {
            // Успешно создано
        },
    })
}
</script>