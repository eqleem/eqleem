<script setup>
import { computed } from 'vue';

// Port of resources/views/ui/heading.blade.php
const props = defineProps({
    size: { type: String, default: 'base' },
    level: { type: [String, Number], default: 2 },
});

const tag = computed(() => `h${Math.min(Math.max(Number(props.level) || 2, 1), 6)}`);

const sizes = {
    '2xl': 'text-2xl',
    xl: 'text-xl',
    lg: 'text-lg',
    base: 'text-base',
    sm: 'text-sm',
};
</script>

<template>
    <component
        :is="tag"
        class="font-medium [*+&]:mt-4 [&:has(+[*])]:mb-2"
        :class="sizes[size] ?? sizes.base"
    >
        <slot />
    </component>
</template>
