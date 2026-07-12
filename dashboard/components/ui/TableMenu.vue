<script setup>
import Dropdown from '../Dropdown.vue';

// Port of resources/views/ui/table-menu.blade.php — reuses dashboard Dropdown.
defineProps({
    width: { type: String, default: 'w-48' },
});
</script>

<template>
    <Dropdown :width="width" class="relative shrink-0">
        <template #trigger>
            <button
                type="button"
                class="inline-block rounded-lg p-1 hover:bg-stone-200"
                aria-haspopup="menu"
            >
                <svg class="h-5 w-5 text-stone-400" viewBox="0 0 24 24" fill="currentColor">
                    <circle cx="12" cy="5" r="1.6" />
                    <circle cx="12" cy="12" r="1.6" />
                    <circle cx="12" cy="19" r="1.6" />
                </svg>
            </button>
        </template>
        <slot />
    </Dropdown>
</template>
