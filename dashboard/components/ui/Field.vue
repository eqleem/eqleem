<script setup>
import { computed, useSlots } from 'vue';
import { SAR_SYMBOL } from '../../lib/money.js';

// Port of resources/views/ui/field.blade.php
const props = defineProps({
    name: { type: String, default: '' },
    label: { type: String, default: null },
    labelWidth: { type: String, default: 'w-36' },
    info: { type: String, default: '' },
    block: { type: Boolean, default: false },
    prefix: { type: String, default: null },
    suffix: { type: String, default: null },
    dir: { type: String, default: null },
    width: { type: String, default: 'w-full' },
    errormsg: { type: Boolean, default: true },
    error: { type: String, default: null },
    prime: { type: Boolean, default: false },
    infoDir: { type: String, default: null },
});

const slots = useSlots();
const hasPrefix = computed(() => Boolean(props.prefix) || Boolean(slots.prefix));
const hasSuffix = computed(() => Boolean(props.suffix) || Boolean(slots.suffix));
const isSarPrefix = computed(() => props.prefix === SAR_SYMBOL);
const isSarSuffix = computed(() => props.suffix === SAR_SYMBOL);
</script>

<template>
    <div
        class="relative items-center gap-x-2 rounded-md bg-stone-100/75 p-1 lg:flex"
        :class="[block ? 'w-full flex-col items-start' : width, { 'border border-dashed border-red-500 pt-10': prime }]"
    >
        <div v-if="prime" title="هذا الخيار يتطلب اشتراك برايم" class="absolute end-0 top-0 p-px text-end">
            <span class="ms-1 inline-block rounded-sm bg-orange-50 px-2 py-1 text-sm font-bold text-orange-600">برايم 👑</span>
        </div>

        <label
            v-if="label"
            :for="name"
            class="inline-block flex-shrink-0 p-2 text-sm font-semibold text-stone-500"
            :class="block ? 'w-full' : labelWidth"
        >
            {{ label }}
        </label>

        <div class="relative" :class="width" :dir="dir">
            <div class="flex w-full items-center text-stone-500">
                <div
                    v-if="hasPrefix"
                    class="shrink-0 px-2"
                    :class="slots.prefix ? 'flex items-center gap-1 text-sm' : 'text-xs opacity-70'"
                >
                    <slot name="prefix">
                        <span
                            v-if="isSarPrefix"
                            class="money-symbol icon-saudi_riyal_new"
                            aria-hidden="true"
                        />
                        <template v-else>{{ prefix }}</template>
                    </slot>
                </div>

                <div class="w-full">
                    <slot />
                </div>

                <div
                    v-if="hasSuffix"
                    class="shrink-0 px-2"
                    :class="slots.suffix ? 'flex items-center gap-1 text-sm' : 'text-xs opacity-70'"
                >
                    <slot name="suffix">
                        <span
                            v-if="isSarSuffix"
                            class="money-symbol icon-saudi_riyal_new"
                            aria-hidden="true"
                        />
                        <template v-else>{{ suffix }}</template>
                    </slot>
                </div>
            </div>

            <div>
                <small
                    v-if="info"
                    class="mt-1 flex items-center gap-x-1 rounded-md bg-stone-50 px-1 text-xs text-stone-400"
                    :dir="infoDir"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <circle cx="12" cy="12" r="9" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8h.01M11 12h1v4h1" />
                    </svg>
                    {{ info }}
                </small>
            </div>

            <small
                v-if="errormsg && error"
                class="mt-1 flex items-center gap-x-1 rounded-md bg-red-50 px-1 text-xs text-red-600"
                :dir="infoDir"
            >
                <svg class="h-4 w-4 text-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <circle cx="12" cy="12" r="9" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v5M12 16h.01" />
                </svg>
                {{ error }}
            </small>
        </div>
    </div>
</template>
