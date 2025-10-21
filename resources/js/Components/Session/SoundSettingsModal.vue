<template>
    <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md w-full mx-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Настройки звука</h3>
                <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    ✕
                </button>
            </div>

            <div class="space-y-4">
                <!-- Общее включение/выключение звуков -->
                <div class="flex items-center justify-between">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Включить звуки</label>
                    <input
                        type="checkbox"
                        :checked="settings.enabled"
                        @change="$emit('update:settings', { ...settings, enabled: ($event.target as HTMLInputElement).checked })"
                        class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 dark:text-indigo-400 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700"
                    />
                </div>

                <!-- Громкость -->
                <div class="flex items-center justify-between">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Громкость</label>
                    <input
                        type="range"
                        min="0"
                        max="1"
                        step="0.1"
                        :value="settings.volume"
                        @input="$emit('update:settings', { ...settings, volume: parseFloat(($event.target as HTMLInputElement).value) })"
                        :disabled="!settings.enabled"
                        class="w-24"
                    />
                    <span class="text-sm text-gray-500 dark:text-gray-400 w-8">{{ Math.round(settings.volume * 100) }}%</span>
                </div>

                <!-- Звук начала -->
                <div class="flex items-center justify-between">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Звук начала</label>
                    <input
                        type="checkbox"
                        :checked="settings.startSound"
                        @change="$emit('update:settings', { ...settings, startSound: ($event.target as HTMLInputElement).checked })"
                        :disabled="!settings.enabled"
                        class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 dark:text-indigo-400 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700"
                    />
                </div>

                <!-- Звук паузы -->
                <div class="flex items-center justify-between">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Звук паузы</label>
                    <input
                        type="checkbox"
                        :checked="settings.pauseSound"
                        @change="$emit('update:settings', { ...settings, pauseSound: ($event.target as HTMLInputElement).checked })"
                        :disabled="!settings.enabled"
                        class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 dark:text-indigo-400 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700"
                    />
                </div>

                <!-- Звук завершения -->
                <div class="flex items-center justify-between">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Звук завершения</label>
                    <input
                        type="checkbox"
                        :checked="settings.completeSound"
                        @change="$emit('update:settings', { ...settings, completeSound: ($event.target as HTMLInputElement).checked })"
                        :disabled="!settings.enabled"
                        class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 dark:text-indigo-400 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700"
                    />
                </div>

                <!-- Звук предупреждения -->
                <div class="flex items-center justify-between">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Звук предупреждения</label>
                    <input
                        type="checkbox"
                        :checked="settings.warningSound"
                        @change="$emit('update:settings', { ...settings, warningSound: ($event.target as HTMLInputElement).checked })"
                        :disabled="!settings.enabled"
                        class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 dark:text-indigo-400 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700"
                    />
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-6">
                <button
                    @click="$emit('close')"
                    class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 transition-colors"
                >
                    Отмена
                </button>
                <button
                    @click="$emit('save')"
                    class="px-4 py-2 bg-indigo-500 dark:bg-indigo-600 text-white rounded-lg hover:bg-indigo-600 dark:hover:bg-indigo-700 transition-colors"
                >
                    Сохранить
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
interface SoundSettings {
    enabled: boolean
    volume: number
    startSound: boolean
    pauseSound: boolean
    completeSound: boolean
    warningSound: boolean
}

interface Props {
    show: boolean
    settings: SoundSettings
}

defineProps<Props>();

defineEmits<{
    close: []
    save: []
    'update:settings': [value: SoundSettings]
}>();
</script>
