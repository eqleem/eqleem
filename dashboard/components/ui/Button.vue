<script setup>
// Port of resources/views/ui/button.blade.php (backend-only wire:target spinner dropped).
defineProps({
    label: { type: String, default: null },
    href: { type: String, default: null },
    target: { type: String, default: null },
    type: { type: String, default: 'button' },
    variant: { type: String, default: 'primary' },
    disabled: { type: Boolean, default: false },
    loading: { type: Boolean, default: false },
    iconPosition: { type: String, default: 'start' }, // 'start' | 'end'
});

const variants = {
    primary: 'bg-primary-600 text-white hover:bg-primary-700',
    secondary: 'bg-black/10 text-black/70 hover:bg-black/20 shadow-none',
    outline: 'bg-white text-black/70 hover:bg-black/5 border border-black/10 shadow-none',
    ghost: 'bg-transparent text-black/70 hover:bg-black/10 shadow-none',
    light: 'bg-white text-primary-700 hover:bg-primary-50 shadow-none',
    green: 'bg-green-600 text-white hover:bg-green-700',
    danger: 'bg-red-700 text-white hover:bg-red-800',
    warning: 'bg-yellow-500 text-white hover:bg-yellow-600',
    link: 'bg-transparent text-black/70 underline hover:bg-transparent shadow-none hover:text-black/30',
};
</script>

<template>
    <component
        :is="href ? 'a' : 'button'"
        :href="href"
        :target="target"
        :type="href ? null : type"
        :role="href ? null : 'button'"
        :disabled="!href && (disabled || loading) ? true : null"
        class="inline-flex h-9 cursor-pointer items-center justify-center gap-2 whitespace-nowrap rounded-md px-4 py-2 text-sm transition-all duration-300 focus-visible:outline-none focus-visible:ring-1 disabled:pointer-events-none disabled:opacity-50"
        :class="variants[variant] ?? 'bg-stone-300 text-black/50'"
    >
        <svg
            v-if="loading"
            class="h-5 w-5 animate-spin"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="1.5"
            aria-hidden="true"
        >
            <path stroke-linecap="round" d="M12 3a9 9 0 1 0 9 9" />
        </svg>
        <slot v-else-if="iconPosition !== 'end'" name="icon" />
        <span v-if="label">{{ label }}</span>
        <slot />
        <slot v-if="!loading && iconPosition === 'end'" name="icon" />
    </component>
</template>
