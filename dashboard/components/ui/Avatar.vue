<script setup>
// Port of resources/views/ui/avatar.blade.php
defineProps({
    src: { type: String, default: null },
    alt: { type: String, default: '' },
    size: { type: String, default: 'md' },
    rounded: { type: String, default: 'full' },
});

const sizes = {
    xl: 'size-16',
    lg: 'size-12',
    md: 'size-10',
    sm: 'size-8',
    xs: 'size-6',
};

const rounds = {
    full: 'rounded-full',
    lg: 'rounded-xl',
    md: 'rounded-lg',
    sm: 'rounded-md',
    xs: 'rounded-sm',
    none: 'rounded-none',
};
</script>

<template>
    <img
        :src="src"
        :alt="alt"
        class="object-cover"
        :class="[sizes[size] ?? sizes.md, rounds[rounded] ?? rounds.full]"
    >
</template>
