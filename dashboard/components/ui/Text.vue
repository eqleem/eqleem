<script setup>
// Port of resources/views/ui/text.blade.php
defineProps({
    size: { type: String, default: null },
    text: { type: String, default: null },
});

const sizes = {
    xl: 'text-lg',
    lg: 'text-base',
    sm: 'text-xs',
};
</script>

<template>
    <div class="text-stone-500 [*+&]:mt-2" :class="sizes[size] ?? 'text-sm'">
        <slot>{{ text }}</slot>
    </div>
</template>
