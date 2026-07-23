<script setup>
import { SAR_SYMBOL } from '../../lib/money.js';
import Field from './Field.vue';

/**
 * Price input with currency shown on the visual left (RTL suffix).
 * Mirrors resources/views/ui/price.blade.php + admin money_symbol() suffix usage.
 */
const props = defineProps({
    modelValue: { type: [String, Number], default: '' },
    name: { type: String, default: 'price' },
    label: { type: String, default: 'السعر' },
    currency: { type: String, default: SAR_SYMBOL },
    disabled: { type: Boolean, default: false },
    error: { type: String, default: null },
    info: { type: String, default: '' },
    infoDir: { type: String, default: 'rtl' },
    width: { type: String, default: 'w-full' },
    labelWidth: { type: String, default: 'w-36' },
    placeholder: { type: String, default: '0.00' },
    step: { type: [String, Number], default: '0.01' },
    min: { type: [String, Number], default: '0' },
    block: { type: Boolean, default: false },
    errormsg: { type: Boolean, default: true },
});

const emit = defineEmits(['update:modelValue']);

const arabicDigits = '٠١٢٣٤٥٦٧٨٩';
const englishDigits = '0123456789';

function toEnglishNumber(raw) {
    return String(raw ?? '')
        .replace(/[٠١٢٣٤٥٦٧٨٩]/g, (digit) => englishDigits[arabicDigits.indexOf(digit)] ?? digit)
        .replace(/[^\d.]/g, '');
}

function onInput(event) {
    const next = toEnglishNumber(event.target.value);

    if (event.target.value !== next) {
        event.target.value = next;
    }

    emit('update:modelValue', next);
}
</script>

<template>
    <Field
        :name="name"
        :label="label"
        :suffix="currency || null"
        :info="info"
        :info-dir="infoDir"
        :width="width"
        :label-width="labelWidth"
        :error="error"
        :errormsg="errormsg"
        :block="block"
    >
        <input
            :id="name"
            type="number"
            :value="modelValue"
            :disabled="disabled"
            :placeholder="placeholder"
            :step="step"
            :min="min"
            dir="ltr"
            class="block w-full rounded-md border-2 bg-white px-3 py-1.5 text-sm text-stone-600 placeholder:text-sm focus:border-primary-500 focus:bg-stone-100/50 focus:text-stone-700 focus:outline-none disabled:cursor-not-allowed disabled:text-stone-400/50"
            :class="error ? 'border-red-300' : 'border-transparent'"
            @input="onInput"
        >
    </Field>
</template>
