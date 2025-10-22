<script setup lang="ts">
withDefaults(
    defineProps<{
        type?: 'button' | 'submit' | 'reset';
    }>(),
    {
        type: 'button',
    },
);
</script>

<template>
    <button
        :type="type"
        class="inline-flex items-center rounded-md border border-neutral-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-neutral-700 shadow-sm transition duration-150 ease-in-out hover:bg-neutral-50 focus:outline-none focus:ring-2 focus:ring-accent-400 focus:ring-offset-2 disabled:opacity-25 dark:border-neutral-600 dark:bg-neutral-800 dark:text-neutral-300 dark:hover:bg-neutral-700 dark:focus:ring-offset-neutral-900"
    >
        <slot />
    </button>
</template>
