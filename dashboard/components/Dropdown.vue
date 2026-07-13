<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';

defineProps({
    width: { type: String, default: 'w-48' },
    // "bottom" opens below the trigger; "top" opens above (avoids being clipped by footers).
    placement: { type: String, default: 'bottom' },
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
    <div ref="root" class="relative" :class="open ? 'z-[200]' : 'z-10'">
        <div @click="toggle">
            <slot name="trigger" />
        </div>

        <div
            v-show="open"
            :class="[
                width,
                placement === 'top' ? 'bottom-full mb-2' : 'mt-2',
            ]"
            class="absolute z-[210] space-y-1 rounded-lg bg-white p-1 text-stone-800 shadow-lg ring-1 ring-black/5 ltr:right-0 rtl:left-0"
            @click="open = false"
        >
            <slot />
        </div>
    </div>
</template>
