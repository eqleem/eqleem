<script setup>
defineProps({
    label: { type: String, required: true },
    value: { type: [String, Number], required: true },
    icon: { type: String, default: null },
    loading: { type: Boolean, default: false },
});
</script>

<template>
    <div
        class="rounded-xl border border-stone-200/80 bg-white p-4 shadow-sm"
        :class="{ 'animate-pulse opacity-70': loading }"
    >
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="text-sm text-stone-500">{{ label }}</p>
                <p class="mt-2 text-2xl font-bold tracking-tight text-stone-900" dir="ltr">{{ value }}</p>
            </div>
            <div
                v-if="icon"
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-stone-100 p-1.5"
            >
                <img :src="icon" alt="" class="h-7 w-7 object-contain">
            </div>
        </div>
    </div>
</template>
