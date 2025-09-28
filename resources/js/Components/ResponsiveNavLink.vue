<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps<{
    href: string;
    active?: boolean;
}>();

const classes = computed(() =>
    props.active
        ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-orange-400 text-start text-base font-medium text-orange-700 bg-orange-50 focus:outline-none focus:text-orange-800 focus:bg-orange-100 focus:border-orange-700 transition duration-150 ease-in-out dark:text-indigo-300 dark:bg-indigo-900/50 dark:focus:text-indigo-200 dark:focus:bg-indigo-900/70 dark:focus:border-indigo-300'
        : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-amber-600 hover:text-amber-800 hover:bg-orange-50 hover:border-orange-300 focus:outline-none focus:text-amber-800 focus:bg-orange-50 focus:border-orange-300 transition duration-150 ease-in-out dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:text-gray-200 dark:focus:bg-gray-700 dark:focus:border-gray-600',
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
