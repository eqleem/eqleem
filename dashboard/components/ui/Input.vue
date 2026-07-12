<script setup>
import { computed, useSlots } from 'vue';
import Field from './Field.vue';

// Port of resources/views/ui/input.blade.php — wire:model -> v-model.
const props = defineProps({
    modelValue: { type: [String, Number], default: '' },
    value: { type: [String, Number], default: null },
    name: { type: String, default: null },
    type: { type: String, default: 'text' },
    inputWidth: { type: String, default: 'w-full' },
    disabled: { type: Boolean, default: false },
    label: { type: String, default: null },
    errormsg: { type: Boolean, default: true },
    error: { type: String, default: null },
    dir: { type: String, default: 'rtl' },
    description: { type: String, default: '' },
    info: { type: String, default: '' },
    width: { type: String, default: 'w-full' },
    placeholder: { type: String, default: '' },
    prefix: { type: String, default: null },
    suffix: { type: String, default: null },
    labelWidth: { type: String, default: 'w-36' },
    block: { type: Boolean, default: false },
    prime: { type: Boolean, default: false },
    infoDir: { type: String, default: null },
    copyable: { type: Boolean, default: false },
});

defineEmits(['update:modelValue']);

const slots = useSlots();
const hasIcon = computed(() => !!slots.icon);
const hasIconTrailing = computed(() => !!slots.iconTrailing);

function copy() {
    navigator.clipboard?.writeText(String(props.value ?? props.modelValue ?? ''));
}
</script>

<template>
    <Field
        :name="name"
        :info="info"
        :label="label"
        :prefix="prefix"
        :suffix="suffix"
        :dir="dir"
        :info-dir="infoDir"
        :width="width"
        :label-width="labelWidth"
        :errormsg="errormsg"
        :error="error"
        :block="block"
        :prime="prime"
    >
        <div class="relative w-full">
            <span
                v-if="hasIcon"
                class="absolute top-2 opacity-50 ltr:left-3 rtl:right-3"
                :class="{ 'rtl:-mr-1 ltr:-ml-1': !$slots.default && !label }"
            >
                <slot name="icon" />
            </span>

            <input
                :id="name"
                :type="type"
                :value="modelValue"
                :disabled="disabled"
                :placeholder="placeholder"
                class="block rounded-md border-2 bg-white py-1.5 px-3 text-sm text-stone-600 placeholder:text-sm focus:bg-stone-100/50 focus:text-stone-700 focus:border-primary-500 focus:outline-none disabled:cursor-not-allowed disabled:text-stone-400/50"
                :class="[
                    inputWidth,
                    error ? 'border-red-300' : 'border-transparent',
                    { 'ps-8': hasIcon, 'pe-8': hasIconTrailing },
                ]"
                @input="$emit('update:modelValue', $event.target.value)"
            >

            <span v-if="hasIconTrailing" class="absolute top-2 opacity-50 ltr:end-2 rtl:start-2">
                <slot name="iconTrailing" />
            </span>

            <slot />

            <button
                v-if="copyable"
                type="button"
                class="absolute top-2 z-10 cursor-pointer bg-white opacity-100 hover:text-black ltr:right-2 rtl:left-2"
                title="نسخ"
                @click="copy"
            >
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <rect x="9" y="9" width="11" height="11" rx="2" />
                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" />
                </svg>
            </button>
        </div>
    </Field>
</template>
