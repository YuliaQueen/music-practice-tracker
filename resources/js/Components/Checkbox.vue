<script setup lang="ts">
import { computed } from 'vue';

const emit = defineEmits(['update:checked']);

const props = defineProps<{
    checked: boolean;
    value?: any;
}>();

const proxyChecked = computed({
    get() {
        return props.checked;
    },

    set(val) {
        emit('update:checked', val);
    },
});
</script>

<template>
    <input
        type="checkbox"
        :value="value"
        v-model="proxyChecked"
        class="rounded border-neutral-300 text-accent-600 shadow-sm focus:ring-accent-500 dark:border-neutral-600 dark:bg-neutral-700 dark:focus:ring-accent-400"
    />
</template>
