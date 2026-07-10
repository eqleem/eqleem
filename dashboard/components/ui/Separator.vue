<script setup>
// Port of resources/views/ui/separator.blade.php — fixed spacing/height (no dynamic Tailwind).
defineProps({
    text: { type: String, default: null },
});
</script>

<template>
    <div class="relative my-8">
        <div class="absolute inset-0 flex items-center" aria-hidden="true">
            <div class="w-full border-t-2 border-black/5"></div>
        </div>
        <div v-if="text" class="relative flex justify-center">
            <span class="bg-white px-2 text-sm text-black/40">{{ text }}</span>
        </div>
    </div>
</template>
