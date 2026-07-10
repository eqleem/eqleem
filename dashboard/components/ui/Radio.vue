<script setup>
import { computed } from 'vue';
import Field from './Field.vue';

// Port of resources/views/ui/radio.blade.php — wire:model -> v-model.
// options: { key: label } map or string[]
const props = defineProps({
    modelValue: { type: [String, Number], default: null },
    name: { type: String, default: 'radio' },
    label: { type: String, default: null },
    info: { type: String, default: '' },
    options: { type: [Array, Object], default: () => [] },
    error: { type: String, default: null },
});

defineEmits(['update:modelValue']);

const normalized = computed(() => {
    if (Array.isArray(props.options)) {
        return props.options.map((item) => ({ id: String(item), label: String(item) }));
    }

    return Object.entries(props.options).map(([id, label]) => ({
        id: String(id),
        label: String(label),
    }));
});
</script>

<template>
    <Field :name="name" :label="label" :info="info" :error="error">
        <div class="flex flex-wrap items-center gap-1.5 text-sm">
            <label
                v-for="option in normalized"
                :key="option.id"
                :for="`radio-${name}-${option.id}`"
                class="flex cursor-pointer items-center gap-x-2 rounded bg-white p-2 px-3 capitalize hover:bg-primary-100"
            >
                <input
                    :id="`radio-${name}-${option.id}`"
                    type="radio"
                    :name="name"
                    :value="option.id"
                    :checked="String(modelValue) === option.id"
                    @change="$emit('update:modelValue', option.id)"
                >
                {{ option.label }}
            </label>
        </div>
    </Field>
</template>
