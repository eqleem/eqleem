<script setup>
import { computed } from 'vue';

// Port of resources/views/ui/alert.blade.php — literal color classes for Tailwind.
const props = defineProps({
    color: { type: String, default: 'gray' },
    heading: { type: String, default: null },
    text: { type: String, default: null },
});

const tones = {
    gray: { wrap: 'bg-stone-100', heading: 'text-stone-700', text: 'text-stone-600' },
    blue: { wrap: 'bg-blue-100', heading: 'text-blue-700', text: 'text-blue-600' },
    green: { wrap: 'bg-green-100', heading: 'text-green-700', text: 'text-green-600' },
    red: { wrap: 'bg-red-100', heading: 'text-red-700', text: 'text-red-600' },
    yellow: { wrap: 'bg-yellow-100', heading: 'text-yellow-700', text: 'text-yellow-600' },
    orange: { wrap: 'bg-orange-100', heading: 'text-orange-700', text: 'text-orange-600' },
};

const tone = computed(() => tones[props.color] ?? tones.gray);
</script>

<template>
    <div class="rounded-lg p-2 px-1 [*+&]:mt-4" :class="tone.wrap">
        <div class="flex w-full items-center justify-between gap-2 px-2 text-sm">
            <div>
                <h3 v-if="heading" class="text-base font-medium" :class="tone.heading">{{ heading }}</h3>
                <p v-if="text" class="text-xs" :class="tone.text">{{ text }}</p>
                <slot />
            </div>
            <div v-if="$slots.action">
                <slot name="action" />
            </div>
        </div>
    </div>
</template>
