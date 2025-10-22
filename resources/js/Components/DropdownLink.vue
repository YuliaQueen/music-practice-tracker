<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    href: string;
}>();
</script>

<template>
    <Link
        :href="href"
        class="block w-full px-4 py-2 text-start text-sm leading-5 text-primary-700 transition duration-150 ease-in-out hover:bg-primary-100 focus:bg-primary-100 focus:outline-none dark:text-neutral-300 dark:hover:bg-neutral-700 dark:focus:bg-neutral-700"
    >
        <slot />
    </Link>
</template>
