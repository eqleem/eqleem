<script setup>
import { computed } from 'vue';
import PageModuleShell from '../PageModuleShell.vue';
import { useFormsStore } from '../../../stores/forms.js';

const store = useFormsStore();
const type = computed(() => store.type);
</script>

<template>
    <PageModuleShell :type="type">
        <slot />
    </PageModuleShell>
</template>
