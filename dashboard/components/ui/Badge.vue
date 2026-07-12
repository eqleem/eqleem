<script setup>
// Stand-in for ui:badge.
defineProps({
    color: { type: String, default: 'gray' },
    size: { type: String, default: 'sm' },
});

const colors = {
    gray: 'bg-gray-50 text-gray-500',
    green: 'bg-green-50 text-green-500',
    yellow: 'bg-yellow-50 text-yellow-500',
    blue: 'bg-blue-50 text-blue-500',
    teal: 'bg-teal-50 text-teal-600',
    red: 'bg-red-50 text-red-500',
    purple: 'bg-purple-50 text-purple-500',
    pink: 'bg-pink-50 text-pink-500',
    white: 'bg-white text-gray-500',
};
</script>

<template>
    <span
        class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-xs font-medium"
        :class="colors[color] ?? colors.gray"
    >
        <slot />
    </span>
</template>
