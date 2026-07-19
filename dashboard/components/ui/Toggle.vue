<script setup>
import Field from './Field.vue';

// Port of resources/views/ui/toggle.blade.php — wire:model -> v-model.
defineProps({
    modelValue: { type: Boolean, default: false },
    name: { type: String, default: null },
    label: { type: String, default: null },
    info: { type: String, default: null },
    labelWidth: { type: String, default: 'w-36' },
    error: { type: String, default: null },
});

defineEmits(['update:modelValue']);
</script>

<template>
    <Field
        :name="name"
        :label="label"
        :info="info"
        :label-width="labelWidth"
        :error="error"
        class="flex justify-between [&>div]:w-auto"
    >
        <label :for="`toggle-${name}`" class="inline-flex cursor-pointer items-center">
            <input
                :id="`toggle-${name}`"
                type="checkbox"
                role="switch"
                class="peer sr-only"
                :checked="modelValue"
                @change="$emit('update:modelValue', $event.target.checked)"
            >
            <div
                class="relative h-6 w-11 rounded-full bg-stone-200 after:absolute after:bottom-0 after:left-[0.1rem] after:top-0 after:my-auto after:h-5 after:w-5 after:rounded-full after:bg-white after:transition-all after:content-[''] peer-checked:bg-blue-500 peer-checked:after:translate-x-[1.3rem] peer-checked:after:bg-stone-100"
                aria-hidden="true"
            ></div>
        </label>
    </Field>
</template>
