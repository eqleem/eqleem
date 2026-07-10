<script setup>
import { computed } from 'vue';
import Field from './Field.vue';

// Port of resources/views/ui/select.blade.php — wire:model -> v-model.
// options: { key: label } map, string[], or [{ id, label, selectable? }]
const props = defineProps({
    modelValue: { type: [String, Number], default: '' },
    name: { type: String, default: null },
    label: { type: String, default: null },
    info: { type: String, default: '' },
    options: { type: [Array, Object], default: () => [] },
    placeholder: { type: String, default: '' },
    labelWidth: { type: String, default: 'w-36' },
    error: { type: String, default: null },
    disabled: { type: Boolean, default: false },
});

defineEmits(['update:modelValue']);

const normalized = computed(() => {
    const options = props.options;

    if (Array.isArray(options)) {
        if (options.length && typeof options[0] === 'object' && options[0] !== null && 'id' in options[0]) {
            return options.map((option) => ({
                id: String(option.id),
                label: option.label,
                disabled: option.selectable === false,
            }));
        }

        return options.map((item) => ({ id: String(item), label: String(item), disabled: false }));
    }

    return Object.entries(options).map(([id, label]) => ({
        id: String(id),
        label: String(label),
        disabled: false,
    }));
});
</script>

<template>
    <Field :name="name" :label="label" :info="info" :label-width="labelWidth" :error="error">
        <select
            :id="name"
            :value="modelValue"
            :disabled="disabled"
            class="shrink-0 rounded-md border border-transparent bg-white p-2 px-3 py-2 text-sm text-gray-700 placeholder-gray-400 focus:border-primary-400 focus:outline-none"
            @change="$emit('update:modelValue', $event.target.value)"
        >
            <option v-if="placeholder !== ''" value="">{{ placeholder }}</option>
            <option
                v-for="option in normalized"
                :key="option.id"
                :value="option.id"
                :disabled="option.disabled"
            >
                {{ option.label }}
            </option>
        </select>
    </Field>
</template>
