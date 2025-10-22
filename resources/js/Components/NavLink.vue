<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps<{
    href: string;
    active?: boolean;
}>();

const classes = computed(() =>
    props.active
        ? 'inline-flex items-center px-1 pt-1 border-b-2 border-primary-400 text-sm font-medium leading-5 text-primary-800 focus:outline-none focus:border-primary-600 transition duration-150 ease-in-out dark:text-neutral-100 dark:border-accent-400 dark:focus:border-accent-500'
        : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-primary-600 hover:text-primary-700 hover:border-primary-300 focus:outline-none focus:text-primary-700 focus:border-primary-300 transition duration-150 ease-in-out dark:text-neutral-400 dark:hover:text-neutral-200 dark:hover:border-neutral-600 dark:focus:text-neutral-200 dark:focus:border-neutral-600',
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
