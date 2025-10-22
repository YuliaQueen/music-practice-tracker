<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps<{
    href: string;
    active?: boolean;
}>();

const classes = computed(() =>
    props.active
        ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-primary-400 text-start text-base font-medium text-primary-700 bg-primary-50 focus:outline-none focus:text-primary-800 focus:bg-primary-100 focus:border-primary-600 transition duration-150 ease-in-out dark:text-accent-300 dark:bg-accent-900/40 dark:border-accent-400 dark:focus:text-accent-200 dark:focus:bg-accent-900/60 dark:focus:border-accent-500'
        : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-primary-600 hover:text-primary-700 hover:bg-primary-50 hover:border-primary-300 focus:outline-none focus:text-primary-700 focus:bg-primary-50 focus:border-primary-300 transition duration-150 ease-in-out dark:text-neutral-400 dark:hover:text-neutral-200 dark:hover:bg-neutral-700 dark:hover:border-neutral-600 dark:focus:text-neutral-200 dark:focus:bg-neutral-700 dark:focus:border-neutral-600',
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
