<script setup lang="ts">
import { onMounted, ref } from 'vue';

const model = defineModel<string>({ required: true });

const input = ref<HTMLInputElement | null>(null);

onMounted(() => {
    if (input.value?.hasAttribute('autofocus')) {
        input.value?.focus();
    }
});

defineExpose({ focus: () => input.value?.focus() });
</script>

<template>
    <input
        class="rounded-md border-neutral-300 shadow-sm focus:border-accent-500 focus:ring-accent-500 dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-300 dark:focus:border-accent-400 dark:focus:ring-accent-400"
        v-model="model"
        ref="input"
    />
</template>
