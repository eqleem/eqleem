<script setup>
import Field from './Field.vue';

// Port of resources/views/ui/checkbox.blade.php — wire:model -> v-model.
defineProps({
    modelValue: { type: [Boolean, Array, String, Number], default: false },
    name: { type: String, default: null },
    label: { type: String, default: null },
    title: { type: String, default: null },
    info: { type: String, default: '' },
    value: { type: [String, Number, Boolean], default: true },
    block: { type: Boolean, default: false },
    labelWidth: { type: String, default: 'w-36' },
    error: { type: String, default: null },
});

defineEmits(['update:modelValue']);
</script>

<template>
    <Field :name="name" :label="label" :info="info" :block="block" :label-width="labelWidth" :error="error">
        <label class="flex items-center gap-x-2 p-1" :for="name">
            <input
                :id="name"
                type="checkbox"
                :value="value"
                :checked="Array.isArray(modelValue) ? modelValue.includes(value) : Boolean(modelValue)"
                class="w-auto shrink-0 rounded-md border border-transparent bg-white p-2 py-2 text-sm text-stone-700 focus:border-primary-400 focus:outline-none"
                @change="$emit('update:modelValue', Array.isArray(modelValue)
                    ? ($event.target.checked ? [...modelValue, value] : modelValue.filter((item) => item !== value))
                    : $event.target.checked)"
            >
            {{ title ?? label }}
        </label>
    </Field>
</template>
