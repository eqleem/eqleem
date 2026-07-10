<script setup>
import Field from './Field.vue';

// Port of resources/views/ui/textarea.blade.php — wire:model -> v-model.
defineProps({
    modelValue: { type: String, default: '' },
    name: { type: String, default: null },
    label: { type: String, default: null },
    placeholder: { type: String, default: '' },
    info: { type: String, default: '' },
    rows: { type: [String, Number], default: 4 },
    dir: { type: String, default: 'rtl' },
    error: { type: String, default: null },
    block: { type: Boolean, default: false },
    labelWidth: { type: String, default: 'w-36' },
    width: { type: String, default: 'w-full' },
});

defineEmits(['update:modelValue']);
</script>

<template>
    <Field
        :name="name"
        :label="label"
        :info="info"
        :dir="dir"
        :error="error"
        :block="block"
        :label-width="labelWidth"
        :width="width"
    >
        <textarea
            :id="name"
            :value="modelValue"
            :placeholder="placeholder"
            :rows="rows"
            class="block w-full rounded-md border-2 border-transparent bg-white px-3 py-1.5 text-sm text-gray-600 placeholder:text-sm focus:border-primary-500 focus:bg-gray-100/50 focus:text-gray-700 focus:outline-none"
            :class="{ 'border-red-300': error }"
            @input="$emit('update:modelValue', $event.target.value)"
        />
    </Field>
</template>
