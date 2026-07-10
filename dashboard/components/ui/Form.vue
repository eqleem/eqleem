<script setup>
// Port of resources/views/ui/form.blade.php — wire:submit -> @submit emit.
defineProps({
    title: { type: String, default: null },
    subtitle: { type: String, default: null },
    formClass: { type: String, default: '' },
});

defineEmits(['submit']);
</script>

<template>
    <form class="flex w-full flex-col gap-y-4 rounded-xl bg-white p-5 py-8" @submit.prevent="$emit('submit')">
        <div v-if="title" class="mb-6 text-gray-600">
            <h2 class="text-sm font-semibold">{{ title }}</h2>
            <small v-if="subtitle" class="opacity-50">{{ subtitle }}</small>
        </div>

        <div class="space-y-2" :class="formClass">
            <slot />

            <div v-if="$slots.inputs" class="mt-4 flex flex-col gap-1">
                <slot name="inputs" />
            </div>
        </div>

        <div v-if="$slots.footer" class="mt-5 flex justify-end">
            <slot name="footer" />
        </div>
    </form>
</template>
