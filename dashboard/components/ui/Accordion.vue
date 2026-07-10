<script setup>
import { provide, ref, toRef } from 'vue';

// Port of resources/views/ui/accordion/accordion.blade.php (Alpine -> Vue).
const props = defineProps({
    active: { type: [String, Number], default: null },
});

const selected = ref(props.active);

provide('accordion', {
    selected,
    active: toRef(props, 'active'),
    select(id) {
        selected.value = id;
    },
});
</script>

<template>
    <div class="w-full divide-y divide-black/5 overflow-hidden">
        <slot />
    </div>
</template>
