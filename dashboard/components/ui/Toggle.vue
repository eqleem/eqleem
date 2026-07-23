<script setup>
import Field from './Field.vue';

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    name: { type: String, default: null },
    label: { type: String, default: null },
    info: { type: String, default: null },
    labelWidth: { type: String, default: 'w-36' },
    error: { type: String, default: null },
});

const emit = defineEmits(['update:modelValue']);

function toggle() {
    emit('update:modelValue', !props.modelValue);
}
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
        <button
            type="button"
            role="switch"
            class="inline-flex cursor-pointer items-center rounded-full focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-400 focus-visible:ring-offset-2"
            :aria-checked="modelValue ? 'true' : 'false'"
            :aria-label="label || name || 'toggle'"
            @click="toggle"
        >
            <span
                class="relative h-6 w-11 rounded-full transition-colors"
                :class="modelValue ? 'bg-blue-500' : 'bg-stone-300'"
                aria-hidden="true"
            >
                <span
                    class="absolute top-0.5 size-5 rounded-full bg-white shadow-sm transition-all"
                    :class="modelValue ? 'start-[1.35rem]' : 'start-0.5'"
                />
            </span>
        </button>
    </Field>
</template>
