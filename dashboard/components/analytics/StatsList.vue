<script setup>
import { itemIconSrc } from '../../lib/analyticsIcons.js';

defineProps({
    title: { type: String, required: true },
    secondaryLabel: { type: String, default: '' },
    items: { type: Array, default: () => [] },
    labelKey: { type: String, default: 'label' },
    countKey: { type: String, default: 'count' },
    emptyText: { type: String, default: 'لا توجد بيانات' },
    loading: { type: Boolean, default: false },
    barClass: { type: String, default: 'bg-primary-500' },
    /** Section header icon (business SVG path). */
    icon: { type: String, default: null },
    /** Resolve per-row icons: browser | os | device | country */
    itemIconKind: {
        type: String,
        default: null,
        validator: (value) => value == null || ['browser', 'os', 'device', 'country'].includes(value),
    },
});

function itemLabel(item, key) {
    return item?.[key] ?? item?.name ?? item?.path ?? item?.domain ?? item?.browser ?? '—';
}

function itemCount(item, key) {
    return item?.[key] ?? item?.views ?? item?.visits ?? item?.count ?? 0;
}

function itemPercentage(item) {
    return Number(item?.percentage ?? 0);
}
</script>

<template>
    <div
        class="rounded-xl border border-stone-200/80 bg-white shadow-sm"
        :class="{ 'animate-pulse opacity-70': loading }"
    >
        <div class="flex items-center justify-between gap-3 border-b border-stone-100 px-4 py-3">
            <div class="flex min-w-0 items-center gap-2.5">
                <div
                    v-if="icon"
                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-stone-100 p-1"
                >
                    <img :src="icon" alt="" class="h-6 w-6 object-contain">
                </div>
                <h3 class="truncate text-sm font-semibold text-stone-800">{{ title }}</h3>
            </div>
            <span v-if="secondaryLabel" class="shrink-0 text-xs text-stone-400">{{ secondaryLabel }}</span>
        </div>

        <div v-if="!items.length" class="px-4 py-8 text-center text-sm text-stone-400">
            {{ emptyText }}
        </div>

        <ul v-else class="divide-y divide-stone-100">
            <li
                v-for="(item, index) in items"
                :key="`${itemLabel(item, labelKey)}-${index}`"
                class="px-4 py-3"
            >
                <div class="mb-1.5 flex items-center justify-between gap-3 text-sm">
                    <div class="flex min-w-0 items-center gap-2.5">
                        <img
                            v-if="itemIconKind"
                            :src="itemIconSrc(itemIconKind, item, labelKey)"
                            alt=""
                            class="h-5 w-5 shrink-0 rounded-sm object-contain"
                            loading="lazy"
                        >
                        <span class="truncate font-medium text-stone-700" :title="itemLabel(item, labelKey)">
                            {{ itemLabel(item, labelKey) }}
                        </span>
                    </div>
                    <span class="shrink-0 tabular-nums text-stone-500" dir="ltr">
                        {{ itemCount(item, countKey) }}
                    </span>
                </div>
                <div class="h-1.5 overflow-hidden rounded-full bg-stone-100">
                    <div
                        class="h-full rounded-full transition-all"
                        :class="barClass"
                        :style="{ width: `${Math.min(100, itemPercentage(item))}%` }"
                    />
                </div>
            </li>
        </ul>
    </div>
</template>
