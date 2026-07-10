<script setup>
import Alert from './Alert.vue';

// Port of resources/views/ui/callout.blade.php (same surface as alert).
defineProps({
    color: { type: String, default: 'gray' },
    heading: { type: String, default: null },
    text: { type: String, default: null },
});
</script>

<template>
    <Alert :color="color" :heading="heading" :text="text">
        <slot />
        <template v-if="$slots.action" #action>
            <slot name="action" />
        </template>
    </Alert>
</template>
