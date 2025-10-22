<template>
    <div class="mt-4">
        <div class="text-center mb-3">
            <span class="text-sm text-neutral-600 dark:text-neutral-400">
                Добавить время к упражнению:
            </span>
        </div>

        <!-- Выбор блока для продления -->
        <div class="mb-3">
            <select
                :value="selectedBlockId"
                @change="$emit('update:selectedBlockId', parseInt(($event.target as HTMLSelectElement).value))"
                class="w-full max-w-xs mx-auto block border-neutral-300 focus:border-accent-500 focus:ring-accent-500 rounded-md shadow-sm dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-100 dark:focus:border-accent-400 dark:focus:ring-accent-400 text-sm"
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

        <div class="flex justify-center gap-2">
            <button
                @click="$emit('extend', 5)"
                :disabled="!selectedBlockId"
                class="px-3 py-2 bg-accent-500 text-white font-medium rounded-lg shadow hover:bg-accent-600 transition-colors text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                title="Добавить 5 минут"
            >
                +5 мин
            </button>
            <button
                @click="$emit('extend', 10)"
                :disabled="!selectedBlockId"
                class="px-3 py-2 bg-accent-500 text-white font-medium rounded-lg shadow hover:bg-accent-600 transition-colors text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                title="Добавить 10 минут"
            >
                +10 мин
            </button>
            <button
                @click="$emit('extend', 15)"
                :disabled="!selectedBlockId"
                class="px-3 py-2 bg-accent-500 text-white font-medium rounded-lg shadow hover:bg-accent-600 transition-colors text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                title="Добавить 15 минут"
            >
                +15 мин
            </button>
        </div>

        <!-- Кнопка перезапуска таймера для завершенных блоков -->
        <div v-if="selectedBlockId" class="mt-3 text-center">
            <button
                @click="$emit('restart')"
                class="px-4 py-2 bg-success-500 text-white font-medium rounded-lg shadow hover:bg-success-600 transition-colors text-sm"
                title="Перезапустить таймер для выбранного упражнения"
            >
                🔄 Перезапустить таймер
            </button>
        </div>
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
