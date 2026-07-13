<script setup>
import { computed } from 'vue';

const props = defineProps({
    mark: { type: Object, default: null },
    url: { type: String, default: null },
    alt: { type: String, default: '' },
    sizeClass: { type: String, default: 'size-8' },
    iconClass: { type: String, default: 'text-2xl' },
    imgClass: { type: String, default: 'object-cover' },
    fallback: { type: String, default: '/assets/images/user.png' },
});

const resolved = computed(() => {
    const mark = props.mark;

    if (mark && typeof mark === 'object' && mark.type) {
        return {
            type: mark.type,
            value: mark.value ?? '',
            color: typeof mark.color === 'string' ? mark.color : '',
            url: mark.url ?? (mark.type === 'image' ? props.url : null),
        };
    }

    if (props.url) {
        return {
            type: 'image',
            value: '',
            color: '',
            url: props.url,
        };
    }

    return {
        type: 'image',
        value: '',
        color: '',
        url: props.fallback,
    };
});

const iconStyle = computed(() => {
    if (!resolved.value.color) {
        return {};
    }

    return { color: resolved.value.color };
});

const imageSrc = computed(() => resolved.value.url || props.fallback);
</script>

<template>
    <span
        v-if="resolved.type === 'emoji' && resolved.value"
        class="inline-flex shrink-0 items-center justify-center leading-none select-none"
        :class="sizeClass"
        role="img"
        :aria-label="alt || undefined"
    >
        <span :class="iconClass">{{ resolved.value }}</span>
    </span>
    <span
        v-else-if="resolved.type === 'icon' && resolved.value"
        class="inline-flex shrink-0 items-center justify-center"
        :class="sizeClass"
        role="img"
        :aria-label="alt || undefined"
    >
        <iconify-icon
            :icon="resolved.value"
            :class="iconClass"
            :style="iconStyle"
            stroke-width="1.5"
        />
    </span>
    <img
        v-else
        :src="imageSrc"
        :alt="alt"
        class="shrink-0"
        :class="[sizeClass, imgClass]"
    >
</template>
