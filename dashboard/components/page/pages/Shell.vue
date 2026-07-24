<script setup>
import { computed } from 'vue';
import PageModuleShell from '../PageModuleShell.vue';
import { usePagesStore } from '../../../stores/pages.js';

const store = usePagesStore();
const type = computed(() => store.type);
</script>

<template>
    <PageModuleShell :type="type">
        <slot />
    </PageModuleShell>
</template>
