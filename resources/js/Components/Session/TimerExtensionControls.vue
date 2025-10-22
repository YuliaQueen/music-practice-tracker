<template>
    <div class="my-6 bg-accent-50/50 dark:bg-accent-900/10 rounded-xl p-4 sm:p-5 border border-accent-200 dark:border-accent-800/30">
        <!-- Заголовок -->
        <div class="flex items-center justify-center gap-2 mb-4">
            <svg class="w-5 h-5 text-accent-600 dark:text-accent-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3 class="text-sm font-semibold text-accent-900 dark:text-accent-200">
                Управление временем
            </h3>
        </div>

        <!-- Выбор блока для продления -->
        <div class="mb-4">
            <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-2 text-center">
                Выберите упражнение:
            </label>
            <select
                :value="selectedBlockId"
                @change="$emit('update:selectedBlockId', parseInt(($event.target as HTMLSelectElement).value))"
                class="w-full max-w-md mx-auto block border-neutral-300 focus:border-accent-500 focus:ring-accent-500 rounded-lg shadow-sm dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-100 dark:focus:border-accent-400 dark:focus:ring-accent-400 text-sm"
            >
                <option value="">Выберите упражнение</option>
                <option
                    v-for="block in blocks"
                    :key="block.id"
                    :value="block.id"
                >
                    {{ block.title }} ({{ getBlockStatusText(block.status) }})
                </option>
            </select>
        </div>

        <!-- Кнопки добавления времени -->
        <div class="flex flex-wrap justify-center gap-2 mb-3">
            <button
                @click="$emit('extend', 5)"
                :disabled="!selectedBlockId"
                class="px-4 py-2 bg-accent-500 text-white font-medium rounded-lg shadow hover:bg-accent-600 transition-colors text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                title="Добавить 5 минут"
            >
                +5 мин
            </button>
            <button
                @click="$emit('extend', 10)"
                :disabled="!selectedBlockId"
                class="px-4 py-2 bg-accent-500 text-white font-medium rounded-lg shadow hover:bg-accent-600 transition-colors text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                title="Добавить 10 минут"
            >
                +10 мин
            </button>
            <button
                @click="$emit('extend', 15)"
                :disabled="!selectedBlockId"
                class="px-4 py-2 bg-accent-500 text-white font-medium rounded-lg shadow hover:bg-accent-600 transition-colors text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                title="Добавить 15 минут"
            >
                +15 мин
            </button>
        </div>

        <!-- Кнопка перезапуска таймера -->
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 transform scale-95"
            enter-to-class="opacity-100 transform scale-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100 transform scale-100"
            leave-to-class="opacity-0 transform scale-95"
        >
            <div v-if="selectedBlockId" class="text-center pt-3 border-t border-accent-200 dark:border-accent-800/30">
                <button
                    @click="$emit('restart')"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-success-500 text-white font-medium rounded-lg shadow hover:bg-success-600 transition-colors text-sm"
                    title="Перезапустить таймер для выбранного упражнения"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Перезапустить таймер
                </button>
            </div>
        </Transition>
    </div>
</template>

<script setup lang="ts">
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

interface Props {
    blocks: SessionBlock[]
    selectedBlockId: number | null
}

defineProps<Props>();

defineEmits<{
    'update:selectedBlockId': [value: number]
    extend: [minutes: number]
    restart: []
}>();

function getBlockStatusText(status: string): string {
    const statusMap: Record<string, string> = {
        completed: 'завершено',
        active: 'активно',
        paused: 'приостановлено',
        planned: 'запланировано',
    };
    return statusMap[status] || status;
}
</script>
