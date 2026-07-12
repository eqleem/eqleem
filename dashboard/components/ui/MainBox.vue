<script setup>
// Port of resources/views/ui/mainbox.blade.php
// The blade `prime` gate checks a tenant feature flag; here it's a simple `locked`
// prop so the upgrade overlay can be showcased without a backend.
defineProps({
    title: { type: String, default: '' },
    subtitle: { type: String, default: '' },
    locked: { type: Boolean, default: false },
});
</script>

<template>
    <div class="relative w-full rounded-2xl bg-white shadow-sm">
        <div
            v-if="locked"
            class="absolute z-40 flex h-full w-full cursor-not-allowed items-center justify-center rounded-2xl bg-black/40 text-center"
        >
            <RouterLink to="/plan" class="cursor-pointer rounded-xl bg-white p-4 hover:text-blue-500">
                يتطلب ترقية الباقة 👑
            </RouterLink>
        </div>

        <div class="flex items-start justify-between border-stone-200 p-4">
            <div class="flex gap-x-4">
                <div
                    v-if="$slots.icon"
                    class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg bg-stone-200 p-1"
                >
                    <slot name="icon" />
                </div>

                <div v-if="title">
                    <h2 class="text-lg">{{ title }}</h2>
                    <p v-if="subtitle" class="text-sm opacity-50"><span class="opacity-50">/</span> {{ subtitle }}</p>
                </div>
            </div>
            <div class="flex-shrink-0">
                <slot name="actions" />
            </div>
        </div>

        <div class="flex flex-col [&>*:last-child]:rounded-b-xl [&>*:last-child]:border-b-0">
            <slot />
        </div>
    </div>
</template>
