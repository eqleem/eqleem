<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';

defineProps({
    width: { type: String, default: 'w-48' },
});

const open = ref(false);
const root = ref(null);

function toggle() {
    open.value = !open.value;
}

function handleOutside(event) {
    if (root.value && !root.value.contains(event.target)) {
        open.value = false;
    }
}

onMounted(() => document.addEventListener('click', handleOutside));
onBeforeUnmount(() => document.removeEventListener('click', handleOutside));
</script>

<template>
    <div ref="root" class="relative">
        <div @click="toggle">
            <slot name="trigger" />
        </div>

        <div
            v-show="open"
            :class="width"
            class="absolute z-50 mt-2 space-y-1 rounded-lg bg-white p-1 text-gray-800 shadow-lg ring-1 ring-black/5 ltr:right-0 rtl:left-0"
            @click="open = false"
        >
            <slot />
        </div>
    </div>
</template>
