<script setup>
defineProps({
    size: { type: String, default: 'md' },
});

const sizes = {
    sm: 'h-5 w-5',
    md: 'h-8 w-8',
    lg: 'h-10 w-10',
};
</script>

<template>
    <svg
        class="animate-spin text-stone-300"
        :class="sizes[size] ?? sizes.md"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="1.5"
        aria-hidden="true"
        role="status"
        aria-label="جاري التحميل"
    >
        <path stroke-linecap="round" d="M12 3a9 9 0 1 0 9 9" />
    </svg>
</template>
