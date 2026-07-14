<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';

// Port of resources/views/ui/tailwindcss-colorpicker-light.blade.php
const props = defineProps({
    modelValue: { type: String, default: null },
    name: { type: String, default: 'bgColor' },
    label: { type: String, default: null },
});

const emit = defineEmits(['update:modelValue']);

const open = ref(false);
const root = ref(null);
const trigger = ref(null);
const panel = ref(null);
const colorInput = ref(null);
const panelStyle = ref({});

const colors = [
    'gray', 'stone', 'slate', 'zinc', 'red', 'pink', 'fuchsia', 'violet',
    'blue', 'indigo', 'purple', 'yellow', 'orange', 'amber', 'cyan', 'green',
    'teal', 'sky', 'emerald', 'lime', 'rose',
];
const variants = [50, 100, 200, 300, 400, 500, 600, 700, 800, 900];

// Explicit class map so Tailwind keeps the utilities.
const lightSwatches = {
    'gray-50': 'bg-stone-50', 'gray-100': 'bg-stone-100', 'gray-200': 'bg-stone-200', 'gray-300': 'bg-stone-300', 'gray-400': 'bg-stone-400', 'gray-500': 'bg-stone-500', 'gray-600': 'bg-stone-600', 'gray-700': 'bg-stone-700', 'gray-800': 'bg-stone-800', 'gray-900': 'bg-stone-900',
    'stone-50': 'bg-stone-50', 'stone-100': 'bg-stone-100', 'stone-200': 'bg-stone-200', 'stone-300': 'bg-stone-300', 'stone-400': 'bg-stone-400', 'stone-500': 'bg-stone-500', 'stone-600': 'bg-stone-600', 'stone-700': 'bg-stone-700', 'stone-800': 'bg-stone-800', 'stone-900': 'bg-stone-900',
    'slate-50': 'bg-slate-50', 'slate-100': 'bg-slate-100', 'slate-200': 'bg-slate-200', 'slate-300': 'bg-slate-300', 'slate-400': 'bg-slate-400', 'slate-500': 'bg-slate-500', 'slate-600': 'bg-slate-600', 'slate-700': 'bg-slate-700', 'slate-800': 'bg-slate-800', 'slate-900': 'bg-slate-900',
    'zinc-50': 'bg-zinc-50', 'zinc-100': 'bg-zinc-100', 'zinc-200': 'bg-zinc-200', 'zinc-300': 'bg-zinc-300', 'zinc-400': 'bg-zinc-400', 'zinc-500': 'bg-zinc-500', 'zinc-600': 'bg-zinc-600', 'zinc-700': 'bg-zinc-700', 'zinc-800': 'bg-zinc-800', 'zinc-900': 'bg-zinc-900',
    'red-50': 'bg-red-50', 'red-100': 'bg-red-100', 'red-200': 'bg-red-200', 'red-300': 'bg-red-300', 'red-400': 'bg-red-400', 'red-500': 'bg-red-500', 'red-600': 'bg-red-600', 'red-700': 'bg-red-700', 'red-800': 'bg-red-800', 'red-900': 'bg-red-900',
    'pink-50': 'bg-pink-50', 'pink-100': 'bg-pink-100', 'pink-200': 'bg-pink-200', 'pink-300': 'bg-pink-300', 'pink-400': 'bg-pink-400', 'pink-500': 'bg-pink-500', 'pink-600': 'bg-pink-600', 'pink-700': 'bg-pink-700', 'pink-800': 'bg-pink-800', 'pink-900': 'bg-pink-900',
    'fuchsia-50': 'bg-fuchsia-50', 'fuchsia-100': 'bg-fuchsia-100', 'fuchsia-200': 'bg-fuchsia-200', 'fuchsia-300': 'bg-fuchsia-300', 'fuchsia-400': 'bg-fuchsia-400', 'fuchsia-500': 'bg-fuchsia-500', 'fuchsia-600': 'bg-fuchsia-600', 'fuchsia-700': 'bg-fuchsia-700', 'fuchsia-800': 'bg-fuchsia-800', 'fuchsia-900': 'bg-fuchsia-900',
    'violet-50': 'bg-violet-50', 'violet-100': 'bg-violet-100', 'violet-200': 'bg-violet-200', 'violet-300': 'bg-violet-300', 'violet-400': 'bg-violet-400', 'violet-500': 'bg-violet-500', 'violet-600': 'bg-violet-600', 'violet-700': 'bg-violet-700', 'violet-800': 'bg-violet-800', 'violet-900': 'bg-violet-900',
    'blue-50': 'bg-blue-50', 'blue-100': 'bg-blue-100', 'blue-200': 'bg-blue-200', 'blue-300': 'bg-blue-300', 'blue-400': 'bg-blue-400', 'blue-500': 'bg-blue-500', 'blue-600': 'bg-blue-600', 'blue-700': 'bg-blue-700', 'blue-800': 'bg-blue-800', 'blue-900': 'bg-blue-900',
    'indigo-50': 'bg-indigo-50', 'indigo-100': 'bg-indigo-100', 'indigo-200': 'bg-indigo-200', 'indigo-300': 'bg-indigo-300', 'indigo-400': 'bg-indigo-400', 'indigo-500': 'bg-indigo-500', 'indigo-600': 'bg-indigo-600', 'indigo-700': 'bg-indigo-700', 'indigo-800': 'bg-indigo-800', 'indigo-900': 'bg-indigo-900',
    'purple-50': 'bg-purple-50', 'purple-100': 'bg-purple-100', 'purple-200': 'bg-purple-200', 'purple-300': 'bg-purple-300', 'purple-400': 'bg-purple-400', 'purple-500': 'bg-purple-500', 'purple-600': 'bg-purple-600', 'purple-700': 'bg-purple-700', 'purple-800': 'bg-purple-800', 'purple-900': 'bg-purple-900',
    'yellow-50': 'bg-yellow-50', 'yellow-100': 'bg-yellow-100', 'yellow-200': 'bg-yellow-200', 'yellow-300': 'bg-yellow-300', 'yellow-400': 'bg-yellow-400', 'yellow-500': 'bg-yellow-500', 'yellow-600': 'bg-yellow-600', 'yellow-700': 'bg-yellow-700', 'yellow-800': 'bg-yellow-800', 'yellow-900': 'bg-yellow-900',
    'orange-50': 'bg-orange-50', 'orange-100': 'bg-orange-100', 'orange-200': 'bg-orange-200', 'orange-300': 'bg-orange-300', 'orange-400': 'bg-orange-400', 'orange-500': 'bg-orange-500', 'orange-600': 'bg-orange-600', 'orange-700': 'bg-orange-700', 'orange-800': 'bg-orange-800', 'orange-900': 'bg-orange-900',
    'amber-50': 'bg-amber-50', 'amber-100': 'bg-amber-100', 'amber-200': 'bg-amber-200', 'amber-300': 'bg-amber-300', 'amber-400': 'bg-amber-400', 'amber-500': 'bg-amber-500', 'amber-600': 'bg-amber-600', 'amber-700': 'bg-amber-700', 'amber-800': 'bg-amber-800', 'amber-900': 'bg-amber-900',
    'cyan-50': 'bg-cyan-50', 'cyan-100': 'bg-cyan-100', 'cyan-200': 'bg-cyan-200', 'cyan-300': 'bg-cyan-300', 'cyan-400': 'bg-cyan-400', 'cyan-500': 'bg-cyan-500', 'cyan-600': 'bg-cyan-600', 'cyan-700': 'bg-cyan-700', 'cyan-800': 'bg-cyan-800', 'cyan-900': 'bg-cyan-900',
    'green-50': 'bg-green-50', 'green-100': 'bg-green-100', 'green-200': 'bg-green-200', 'green-300': 'bg-green-300', 'green-400': 'bg-green-400', 'green-500': 'bg-green-500', 'green-600': 'bg-green-600', 'green-700': 'bg-green-700', 'green-800': 'bg-green-800', 'green-900': 'bg-green-900',
    'teal-50': 'bg-teal-50', 'teal-100': 'bg-teal-100', 'teal-200': 'bg-teal-200', 'teal-300': 'bg-teal-300', 'teal-400': 'bg-teal-400', 'teal-500': 'bg-teal-500', 'teal-600': 'bg-teal-600', 'teal-700': 'bg-teal-700', 'teal-800': 'bg-teal-800', 'teal-900': 'bg-teal-900',
    'sky-50': 'bg-sky-50', 'sky-100': 'bg-sky-100', 'sky-200': 'bg-sky-200', 'sky-300': 'bg-sky-300', 'sky-400': 'bg-sky-400', 'sky-500': 'bg-sky-500', 'sky-600': 'bg-sky-600', 'sky-700': 'bg-sky-700', 'sky-800': 'bg-sky-800', 'sky-900': 'bg-sky-900',
    'emerald-50': 'bg-emerald-50', 'emerald-100': 'bg-emerald-100', 'emerald-200': 'bg-emerald-200', 'emerald-300': 'bg-emerald-300', 'emerald-400': 'bg-emerald-400', 'emerald-500': 'bg-emerald-500', 'emerald-600': 'bg-emerald-600', 'emerald-700': 'bg-emerald-700', 'emerald-800': 'bg-emerald-800', 'emerald-900': 'bg-emerald-900',
    'lime-50': 'bg-lime-50', 'lime-100': 'bg-lime-100', 'lime-200': 'bg-lime-200', 'lime-300': 'bg-lime-300', 'lime-400': 'bg-lime-400', 'lime-500': 'bg-lime-500', 'lime-600': 'bg-lime-600', 'lime-700': 'bg-lime-700', 'lime-800': 'bg-lime-800', 'lime-900': 'bg-lime-900',
    'rose-50': 'bg-rose-50', 'rose-100': 'bg-rose-100', 'rose-200': 'bg-rose-200', 'rose-300': 'bg-rose-300', 'rose-400': 'bg-rose-400', 'rose-500': 'bg-rose-500', 'rose-600': 'bg-rose-600', 'rose-700': 'bg-rose-700', 'rose-800': 'bg-rose-800', 'rose-900': 'bg-rose-900',
    white: 'bg-white',
    'bg-tranparent': 'bg-stone-100',
    transparent: 'bg-stone-100',
};

function isHexColor(value) {
    return typeof value === 'string' && /^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/.test(value);
}

function normalizeHex(value) {
    const raw = String(value).replace('#', '');

    if (raw.length === 3) {
        return `#${raw[0]}${raw[0]}${raw[1]}${raw[1]}${raw[2]}${raw[2]}`.toLowerCase();
    }

    return `#${raw.toLowerCase()}`;
}

function normalizeValue(value) {
    if (!value) {
        return 'gray-300';
    }

    const raw = String(value).trim();

    if (isHexColor(raw)) {
        return normalizeHex(raw);
    }

    return raw
        .replace(/^bg-/, '')
        .replace(/^text-/, '')
        .trim() || 'gray-300';
}

const currentValue = computed(() => normalizeValue(props.modelValue));
const isCustom = computed(() => isHexColor(currentValue.value));
const customHex = ref(isCustom.value ? currentValue.value : '#3d5ccc');

watch(currentValue, (value) => {
    if (isHexColor(value)) {
        customHex.value = normalizeHex(value);
    }
});

const triggerClass = computed(() => {
    if (isCustom.value) {
        return 'border border-stone-300';
    }

    return lightSwatches[currentValue.value] ?? 'bg-stone-300';
});

const triggerStyle = computed(() => {
    if (! isCustom.value) {
        return null;
    }

    return { backgroundColor: currentValue.value };
});

const displayLabel = computed(() => {
    if (currentValue.value === 'bg-tranparent' || currentValue.value === 'transparent') {
        return 'transparent';
    }

    return currentValue.value;
});

function isActive(value) {
    return currentValue.value === normalizeValue(value);
}

function isDarkVariant(value) {
    const match = String(value).match(/-(\d{2,3})$/);

    if (! match) {
        return false;
    }

    return Number(match[1]) >= 500;
}

function swatchClass(value) {
    const key = normalizeValue(value);

    return [
        lightSwatches[key] ?? 'bg-stone-200',
        'mx-0.5 my-0.5 flex h-5 w-5 cursor-pointer items-center justify-center rounded border border-stone-300 transition hover:opacity-80',
        isActive(key)
            ? 'scale-110 ring-2 ring-black ring-offset-1'
            : '',
    ];
}

function selectColor(color, variant = null) {
    const next = variant != null ? `${color}-${variant}` : color;
    emit('update:modelValue', next);
}

function selectCustom(event) {
    const hex = normalizeHex(event.target.value);
    customHex.value = hex;
    emit('update:modelValue', hex);
}

function openCustomPicker() {
    colorInput.value?.click();
}

function updatePanelPosition() {
    if (! open.value || ! trigger.value) {
        return;
    }

    const rect = trigger.value.getBoundingClientRect();
    const margin = 8;
    const gap = 6;
    const preferredWidth = Math.min(560, window.innerWidth - margin * 2);
    const isRtl = document.documentElement.dir === 'rtl';

    let left = isRtl
        ? rect.right - preferredWidth
        : rect.left;

    left = Math.max(margin, Math.min(left, window.innerWidth - preferredWidth - margin));

    const spaceBelow = window.innerHeight - rect.bottom - margin;
    const spaceAbove = rect.top - margin;
    const placeBelow = spaceBelow >= 220 || spaceBelow >= spaceAbove;
    const available = placeBelow ? spaceBelow : spaceAbove;
    const maxHeight = Math.max(160, Math.min(360, available - gap));

    panelStyle.value = {
        position: 'fixed',
        top: placeBelow
            ? `${Math.round(rect.bottom + gap)}px`
            : 'auto',
        bottom: placeBelow
            ? 'auto'
            : `${Math.round(window.innerHeight - rect.top + gap)}px`,
        left: `${Math.round(left)}px`,
        width: `${Math.round(preferredWidth)}px`,
        maxHeight: `${Math.round(maxHeight)}px`,
        zIndex: 9999,
    };
}

function toggleOpen() {
    open.value = ! open.value;

    if (open.value) {
        nextTick(() => updatePanelPosition());
    }
}

function onDocumentClick(event) {
    const target = event.target;

    if (
        open.value
        && ! root.value?.contains(target)
        && ! panel.value?.contains(target)
    ) {
        open.value = false;
    }
}

function onViewportChange() {
    if (open.value) {
        updatePanelPosition();
    }
}

watch(open, (isOpen) => {
    if (isOpen) {
        nextTick(() => updatePanelPosition());
    }
});

onMounted(() => {
    document.addEventListener('mousedown', onDocumentClick);
    window.addEventListener('resize', onViewportChange);
    window.addEventListener('scroll', onViewportChange, true);
});

onBeforeUnmount(() => {
    document.removeEventListener('mousedown', onDocumentClick);
    window.removeEventListener('resize', onViewportChange);
    window.removeEventListener('scroll', onViewportChange, true);
});
</script>

<template>
    <div ref="root" class="rounded-lg bg-stone-100/75 p-1">
        <div class="items-center sm:flex">
            <label
                v-if="label"
                :for="name"
                class="inline-block w-36 shrink-0 p-2 text-sm font-semibold text-stone-500"
            >
                {{ label }}
            </label>

            <div class="relative flex flex-row items-center gap-2">
                <button
                    ref="trigger"
                    type="button"
                    class="my-auto flex items-center gap-2 rounded-lg border border-stone-200 bg-white px-2 py-1.5 hover:bg-stone-50"
                    :aria-expanded="open"
                    aria-haspopup="dialog"
                    @click.stop="toggleOpen"
                >
                    <span
                        class="flex h-7 w-7 items-center justify-center rounded-md border border-stone-300 shadow-sm"
                        :class="triggerClass"
                        :style="triggerStyle"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-4 w-4 drop-shadow-sm"
                            :class="isCustom || isDarkVariant(currentValue) ? 'text-white' : 'text-stone-700'"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                        </svg>
                    </span>
                    <span
                        class="text-xs font-medium text-stone-600"
                        :class="isCustom ? 'uppercase' : ''"
                    >
                        {{ displayLabel }}
                    </span>
                </button>

                <Teleport to="body">
                    <div
                        v-if="open"
                        ref="panel"
                        class="flex flex-col overflow-hidden rounded-xl border border-stone-200 bg-white shadow-2xl ring-1 ring-black/5"
                        :style="panelStyle"
                        role="dialog"
                        aria-label="اختيار اللون"
                        @mousedown.stop
                        @click.stop
                    >
                        <div class="flex shrink-0 items-center justify-between gap-2 border-b border-stone-100 bg-white px-3 py-2">
                            <div class="flex min-w-0 items-center gap-2">
                                <span
                                    class="h-5 w-5 shrink-0 rounded border border-stone-300 shadow-sm"
                                    :class="isCustom ? '' : (lightSwatches[currentValue] ?? 'bg-stone-300')"
                                    :style="triggerStyle"
                                />
                                <span
                                    class="truncate text-xs font-medium text-stone-600"
                                    :class="isCustom ? 'uppercase' : ''"
                                >
                                    {{ displayLabel }}
                                </span>
                            </div>
                            <button
                                type="button"
                                class="rounded-md px-2 py-1 text-xs text-stone-500 transition hover:bg-stone-100 hover:text-stone-700"
                                @click="open = false"
                            >
                                إغلاق
                            </button>
                        </div>

                        <div class="min-h-0 flex-1 overflow-auto overscroll-contain p-2 [scrollbar-gutter:stable]">
                            <div class="flex w-max min-w-full gap-0.5 pb-1">
                                <div
                                    v-for="color in colors"
                                    :key="color"
                                    class="flex shrink-0 flex-col"
                                >
                                    <button
                                        v-for="variant in variants"
                                        :key="`${color}-${variant}`"
                                        type="button"
                                        :class="swatchClass(`${color}-${variant}`)"
                                        :title="`${color}-${variant}`"
                                        :aria-pressed="isActive(`${color}-${variant}`)"
                                        @click="selectColor(color, variant)"
                                    >
                                        <span
                                            v-if="isActive(`${color}-${variant}`)"
                                            class="h-1.5 w-1.5 rounded-full shadow-sm"
                                            :class="isDarkVariant(`${color}-${variant}`) ? 'bg-white' : 'bg-black'"
                                        />
                                    </button>
                                </div>

                                <div class="flex shrink-0 flex-col border-s border-stone-100 ps-0.5">
                                    <button
                                        type="button"
                                        :class="swatchClass('white')"
                                        title="white"
                                        :aria-pressed="isActive('white')"
                                        @click="selectColor('white')"
                                    >
                                        <span v-if="isActive('white')" class="h-1.5 w-1.5 rounded-full bg-black" />
                                    </button>
                                    <button
                                        type="button"
                                        :class="swatchClass('bg-tranparent')"
                                        title="transparent"
                                        :aria-pressed="isActive('bg-tranparent')"
                                        @click="selectColor('bg-tranparent')"
                                    >
                                        <span
                                            v-if="isActive('bg-tranparent')"
                                            class="h-1.5 w-1.5 rounded-full bg-black"
                                        />
                                    </button>
                                    <button
                                        type="button"
                                        class="relative mx-0.5 my-0.5 flex h-5 w-5 cursor-pointer items-center justify-center rounded border border-stone-300 transition hover:opacity-80"
                                        :class="isCustom ? 'scale-110 ring-2 ring-black ring-offset-1' : ''"
                                        title="لون مخصص"
                                        :aria-pressed="isCustom"
                                        @click="openCustomPicker"
                                    >
                                        <span
                                            class="absolute inset-0 rounded"
                                            :style="{ backgroundColor: customHex }"
                                        />
                                        <span
                                            v-if="isCustom"
                                            class="relative h-1.5 w-1.5 rounded-full bg-white shadow-sm"
                                        />
                                        <svg
                                            v-else
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="relative h-3 w-3 text-white drop-shadow"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                            stroke-width="2"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" />
                                        </svg>
                                        <input
                                            ref="colorInput"
                                            type="color"
                                            class="pointer-events-none absolute inset-0 h-0 w-0 opacity-0"
                                            :value="customHex"
                                            @input="selectCustom"
                                        >
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </Teleport>
            </div>
        </div>
    </div>
</template>
