<script setup>
import { computed, inject, onMounted } from 'vue';
import Heading from './Heading.vue';
import Text from './Text.vue';

// Port of resources/views/ui/accordion/item.blade.php
const props = defineProps({
    heading: { type: String, default: null },
    id: { type: [String, Number], default: () => Math.random().toString(36).slice(2, 10) },
});

const accordion = inject('accordion', null);

const open = computed(() => accordion?.selected?.value === props.id);

onMounted(() => {
    if (accordion && accordion.selected.value == null) {
        accordion.select(props.id);
    }
});

function toggle() {
    accordion?.select(props.id);
}
</script>

<template>
    <div class="accordion-item p-3 py-3">
        <button
            type="button"
            class="flex w-full items-center justify-between gap-4 font-medium"
            :class="open ? 'font-bold' : ''"
            :aria-expanded="open ? 'true' : 'false'"
            @click="toggle"
        >
            <Heading level="3">{{ heading }}</Heading>
            <svg
                class="size-5 shrink-0 text-black/30 transition"
                :class="{ 'rotate-180': open }"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.5"
            >
                <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
            </svg>
        </button>
        <div v-show="open" role="region">
            <Text>
                <slot />
            </Text>
        </div>
    </div>
</template>
