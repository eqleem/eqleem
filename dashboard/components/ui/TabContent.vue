<script setup>
import { computed, inject } from 'vue';

// Port of resources/views/ui/tab/content.blade.php
const props = defineProps({
    name: { type: String, required: true },
});

const tabs = inject('tabs', null);
const visible = computed(() => tabs?.activeTab?.value === props.name);
</script>

<template>
    <div v-show="visible" class="rounded-lg rounded-b-2xl bg-white p-3">
        <slot />
    </div>
</template>
