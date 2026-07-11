<script setup>
import { computed, useAttrs } from 'vue';
import { formatMoneyAmount, formatMoneyMinor, stripCurrencySuffix } from '../../lib/money.js';

defineOptions({ inheritAttrs: false });

const props = defineProps({
    formatted: { type: String, default: '' },
    amount: { type: [Number, String], default: null },
    minor: { type: Boolean, default: false },
    freeLabel: { type: String, default: '' },
});

const attrs = useAttrs();

const amountText = computed(() => {
    if (props.formatted) {
        return stripCurrencySuffix(props.formatted);
    }

    if (props.amount === null || props.amount === '') {
        return '';
    }

    return props.minor
        ? formatMoneyMinor(props.amount)
        : formatMoneyAmount(props.amount);
});

const isFree = computed(() => props.freeLabel && (amountText.value === '' || amountText.value === '0'));
</script>

<template>
    <span v-if="isFree" v-bind="attrs">{{ freeLabel }}</span>
    <span v-else v-bind="attrs" class="money">
        <span class="money-amount">{{ amountText }}</span>
        <span class="money-symbol icon-saudi_riyal_new shrink-0" aria-hidden="true"></span>
    </span>
</template>
