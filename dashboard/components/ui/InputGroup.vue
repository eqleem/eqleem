<script setup>
import Field from './Field.vue';

// Port of resources/views/ui/input-group.blade.php — Field wrapper around a custom slot.
defineProps({
    name: { type: String, default: null },
    label: { type: String, default: null },
    info: { type: String, default: '' },
    prefix: { type: String, default: null },
    suffix: { type: String, default: null },
    dir: { type: String, default: null },
    block: { type: Boolean, default: false },
    labelWidth: { type: String, default: 'w-36' },
    error: { type: String, default: null },
});
</script>

<template>
    <Field
        :name="name"
        :label="label"
        :info="info"
        :prefix="prefix"
        :suffix="suffix"
        :dir="dir"
        :block="block"
        :label-width="labelWidth"
        :error="error"
    >
        <slot />
    </Field>
</template>
