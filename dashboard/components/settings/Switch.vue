<script setup>
// Simple switch used in payment/shipping lists (no Field wrapper).
defineProps({
    modelValue: { type: Boolean, default: false },
    label: { type: String, default: null },
    disabled: { type: Boolean, default: false },
});

defineEmits(['update:modelValue']);
</script>

<template>
    <button
        type="button"
        class="shrink-0 cursor-pointer rounded-lg p-1 transition hover:bg-stone-100 disabled:cursor-not-allowed disabled:opacity-50"
        role="switch"
        :aria-checked="modelValue ? 'true' : 'false'"
        :aria-label="label"
        :disabled="disabled"
        @click="$emit('update:modelValue', !modelValue)"
    >
        <span
            class="relative inline-block h-6 w-11 rounded-full transition-colors duration-200"
            :class="modelValue ? 'bg-primary-500' : 'bg-stone-200'"
        >
            <span
                class="absolute top-0.5 size-5 rounded-full bg-white shadow-sm transition-all duration-200"
                :class="modelValue ? 'end-0.5 start-auto' : 'start-0.5'"
            ></span>
        </span>
    </button>
</template>
