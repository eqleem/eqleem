<script setup>
// Port of resources/views/ui/box.blade.php
defineProps({
    title: { type: String, default: null },
    subtitle: { type: String, default: null },
});
</script>

<template>
    <div class="w-full rounded-xl bg-white">
        <div
            v-if="title"
            class="flex items-center justify-between rounded-t-xl border-b-2 border-gray-100 bg-gray-50 p-4 text-gray-600"
        >
            <div>
                <div class="flex items-center gap-x-2">
                    <slot name="rightAction" />
                    <div>
                        <h2 class="text-sm font-semibold text-gray-500">{{ title }}</h2>
                        <small v-if="subtitle" class="text-xs opacity-50">{{ subtitle }}</small>
                    </div>
                </div>
            </div>
            <div v-if="$slots.action">
                <slot name="action" />
            </div>
        </div>

        <div class="w-full pt-0 [&>*:first-child]:rounded-ts-none">
            <div v-if="$slots.content" class="p-4">
                <slot name="content" />
            </div>

            <div>
                <slot />
            </div>
        </div>

        <div v-if="$slots.footer" class="flex justify-end p-4">
            <slot name="footer" />
        </div>
    </div>
</template>
