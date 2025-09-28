<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps<{
    href: string;
    active?: boolean;
}>();

const classes = computed(() =>
    props.active
        ? 'inline-flex items-center px-1 pt-1 border-b-2 border-orange-400 text-sm font-medium leading-5 text-amber-900 focus:outline-none focus:border-orange-700 transition duration-150 ease-in-out dark:text-gray-100 dark:focus:border-indigo-300'
        : 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-amber-600 hover:text-amber-800 hover:border-orange-300 focus:outline-none focus:text-amber-800 focus:border-orange-300 transition duration-150 ease-in-out dark:text-gray-400 dark:hover:text-gray-200 dark:hover:border-gray-600 dark:focus:text-gray-200 dark:focus:border-gray-600',
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
