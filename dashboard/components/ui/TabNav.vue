<script setup>
import { computed, inject } from 'vue';

// Port of resources/views/ui/tab/nav.blade.php
const props = defineProps({
    name: { type: String, required: true },
    label: { type: String, default: '' },
    activeClass: { type: String, default: 'bg-white rounded-t-md' },
    badge: { type: [String, Number], default: null },
});

const tabs = inject('tabs', null);
const isActive = computed(() => tabs?.activeTab?.value === props.name);

function activate() {
    tabs?.setTab(props.name);
}
</script>

<template>
    <button
        type="button"
        class="inline-flex items-center gap-1.5 px-3 py-2.5 text-sm"
        :class="isActive ? activeClass : ''"
        @click="activate"
    >
        <slot name="icon" />
        {{ label }}
        <span
            v-if="badge != null && badge !== ''"
            class="inline-flex min-w-5 items-center justify-center rounded-full bg-primary-500 px-1.5 py-0.5 text-[10px] font-bold leading-none text-white"
        >
            {{ badge }}
        </span>
    </button>
</template>
