<template>
    <Modal :show="show" @close="$emit('close')" max-width="2xl">
        <div class="p-6">
            <h2 class="text-xl font-semibold text-primary-800 dark:text-neutral-100 mb-4">
                🍅 Настройки Pomodoro
            </h2>

            <div class="space-y-4">
                <!-- Количество циклов -->
                <div>
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                        Количество рабочих циклов
                    </label>
                    <input
                        v-model.number="localSettings.totalCycles"
                        type="number"
                        min="1"
                        class="w-full px-3 py-2 border border-neutral-300 rounded-md shadow-sm focus:ring-accent-500 focus:border-accent-500 dark:bg-neutral-700 dark:border-neutral-600 dark:text-neutral-100"
                        @input="updatePreview"
                    />
                    <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                        Сколько Pomodoro-циклов вы хотите выполнить
                    </p>
                </div>

                <!-- Вычисленное общее время -->
                <div class="p-3 bg-accent-50 dark:bg-accent-900/20 border border-accent-200 dark:border-accent-800 rounded-lg">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-neutral-700 dark:text-neutral-300">
                            ⏱️ Общее время занятия:
                        </span>
                        <span class="text-lg font-bold text-accent-600 dark:text-accent-400">
                            {{ totalMinutes }} минут
                        </span>
                    </div>
                </div>

                <!-- Длительность работы -->
                <div>
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                        🔥 Длительность работы (минут)
                    </label>
                    <input
                        v-model.number="localSettings.workDuration"
                        type="number"
                        min="1"
                        class="w-full px-3 py-2 border border-neutral-300 rounded-md shadow-sm focus:ring-accent-500 focus:border-accent-500 dark:bg-neutral-700 dark:border-neutral-600 dark:text-neutral-100"
                        @input="updatePreview"
                    />
                    <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                        Стандартное значение: 25 минут
                    </p>
                </div>

                <!-- Короткий перерыв -->
                <div>
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                        ☕ Короткий перерыв (минут)
                    </label>
                    <input
                        v-model.number="localSettings.shortBreak"
                        type="number"
                        min="0"
                        class="w-full px-3 py-2 border border-neutral-300 rounded-md shadow-sm focus:ring-accent-500 focus:border-accent-500 dark:bg-neutral-700 dark:border-neutral-600 dark:text-neutral-100"
                        @input="updatePreview"
                    />
                    <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                        Стандартное значение: 5 минут
                    </p>
                </div>

                <!-- Длинный перерыв -->
                <div>
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                        🌟 Длинный перерыв (минут)
                    </label>
                    <input
                        v-model.number="localSettings.longBreak"
                        type="number"
                        min="0"
                        class="w-full px-3 py-2 border border-neutral-300 rounded-md shadow-sm focus:ring-accent-500 focus:border-accent-500 dark:bg-neutral-700 dark:border-neutral-600 dark:text-neutral-100"
                        @input="updatePreview"
                    />
                    <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                        Стандартное значение: 15 минут
                    </p>
                </div>

                <!-- Циклов до длинного перерыва -->
                <div>
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-2">
                        Циклов до длинного перерыва
                    </label>
                    <input
                        v-model.number="localSettings.cyclesBeforeLongBreak"
                        type="number"
                        min="1"
                        class="w-full px-3 py-2 border border-neutral-300 rounded-md shadow-sm focus:ring-accent-500 focus:border-accent-500 dark:bg-neutral-700 dark:border-neutral-600 dark:text-neutral-100"
                        @input="updatePreview"
                    />
                    <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
                        Стандартное значение: 4 цикла
                    </p>
                </div>

                <!-- Превью расписания -->
                <div v-if="previewSlots.length > 0" class="mt-6 p-4 bg-neutral-50 rounded-lg dark:bg-neutral-700">
                    <h3 class="text-sm font-medium text-neutral-900 dark:text-neutral-100 mb-3">
                        📋 Превью расписания
                    </h3>
                    <div class="space-y-2 max-h-60 overflow-y-auto">
                        <div
                            v-for="(slot, index) in previewSlots"
                            :key="index"
                            :class="[
                                'flex items-center justify-between p-2 rounded border',
                                pomodoro.getSlotColorClass(slot.type)
                            ]"
                        >
                            <div class="flex items-center space-x-2">
                                <span class="text-lg">{{ pomodoro.getSlotIcon(slot.type) }}</span>
                                <span class="text-sm font-medium">{{ slot.title }}</span>
                            </div>
                            <span class="text-sm">{{ slot.duration }} мин</span>
                        </div>
                    </div>
                    <div class="mt-3 text-sm text-neutral-600 dark:text-neutral-400">
                        <strong>Всего рабочих циклов:</strong> {{ totalCycles }}
                    </div>
                </div>

                <!-- Ошибки валидации -->
                <div v-if="validationErrors.length > 0" class="mt-4 p-3 bg-danger-50 border border-danger-200 rounded-lg dark:bg-danger-900/20 dark:border-danger-800">
                    <ul class="list-disc list-inside text-sm text-danger-600 dark:text-danger-300 space-y-1">
                        <li v-for="(error, index) in validationErrors" :key="index">{{ error }}</li>
                    </ul>
                </div>
            </div>

            <!-- Кнопки -->
            <div class="mt-6 flex justify-end space-x-3">
                <SecondaryButton @click="$emit('close')">
                    Отмена
                </SecondaryButton>
                <PrimaryButton @click="save" :disabled="validationErrors.length > 0">
                    Применить настройки
                </PrimaryButton>
            </div>
        </div>
    </Modal>
</template>

<script setup lang="ts">
import { ref, watch, computed } from 'vue'
import Modal from '@/Components/Modal.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import { usePomodoro, type PomodoroSettings } from '@/composables/usePomodoro'

interface Props {
    show: boolean
    settings: PomodoroSettings
}

const props = defineProps<Props>()
const emit = defineEmits<{
    (e: 'close'): void
    (e: 'save', settings: PomodoroSettings): void
}>()

const pomodoro = usePomodoro()
const localSettings = ref<PomodoroSettings>({ ...props.settings })
const previewSlots = ref(pomodoro.calculateSlots(localSettings.value))

const totalCycles = computed(() =>
    previewSlots.value.filter(slot => slot.type === 'custom').length
)

const totalMinutes = computed(() =>
    pomodoro.calculateTotalMinutes(localSettings.value)
)

const validation = computed(() =>
    pomodoro.validateSettings(localSettings.value)
)

const validationErrors = computed(() => validation.value.errors)

const updatePreview = () => {
    previewSlots.value = pomodoro.calculateSlots(localSettings.value)
}

const save = () => {
    if (validation.value.valid) {
        emit('save', localSettings.value)
        emit('close')
    }
}

watch(() => props.show, (show) => {
    if (show) {
        localSettings.value = { ...props.settings }
        updatePreview()
    }
})

watch(() => props.settings, (newSettings) => {
    localSettings.value = { ...newSettings }
    updatePreview()
}, { deep: true })
</script>