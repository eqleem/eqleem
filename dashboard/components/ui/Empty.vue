<script setup>
// Port of resources/views/ui/empty.blade.php
defineProps({
    subtitle: { type: String, default: null },
});
</script>

<template>
    <div>
        <div class="rounded-xl bg-gray-100 py-8 text-center">
            <div v-if="$slots.icon" class="mx-auto h-12 w-12 text-gray-500">
                <slot name="icon" />
            </div>
            <svg
                v-else
                class="mx-auto h-12 w-12 text-gray-400"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                aria-hidden="true"
            >
                <path
                    vector-effect="non-scaling-stroke"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"
                />
            </svg>

            <h3 class="mt-3 text-sm font-semibold text-gray-600">
                <slot />
            </h3>
            <p v-if="subtitle" class="mt-3 text-sm text-gray-500">{{ subtitle }}</p>
            <div v-if="$slots.button" class="mx-auto mt-6 w-full text-center">
                <slot name="button" />
            </div>
        </div>
    </div>
</template>
