<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

// Port of resources/views/ui/tailwindcss-colorpicker-light.blade.php
const props = defineProps({
    modelValue: { type: String, default: null },
    name: { type: String, default: 'bgColor' },
    label: { type: String, default: null },
});

const emit = defineEmits(['update:modelValue']);

const open = ref(false);
const root = ref(null);

const colors = [
    'gray', 'stone', 'slate', 'zinc', 'red', 'pink', 'fuchsia', 'violet',
    'blue', 'indigo', 'purple', 'yellow', 'orange', 'amber', 'cyan', 'green',
    'teal', 'sky', 'emerald', 'lime', 'rose',
];
const variants = [50, 100, 200, 300];

// Explicit class map so Tailwind keeps the utilities.
const lightSwatches = {
    'gray-50': 'bg-gray-50', 'gray-100': 'bg-gray-100', 'gray-200': 'bg-gray-200', 'gray-300': 'bg-gray-300',
    'stone-50': 'bg-stone-50', 'stone-100': 'bg-stone-100', 'stone-200': 'bg-stone-200', 'stone-300': 'bg-stone-300',
    'slate-50': 'bg-slate-50', 'slate-100': 'bg-slate-100', 'slate-200': 'bg-slate-200', 'slate-300': 'bg-slate-300',
    'zinc-50': 'bg-zinc-50', 'zinc-100': 'bg-zinc-100', 'zinc-200': 'bg-zinc-200', 'zinc-300': 'bg-zinc-300',
    'red-50': 'bg-red-50', 'red-100': 'bg-red-100', 'red-200': 'bg-red-200', 'red-300': 'bg-red-300',
    'pink-50': 'bg-pink-50', 'pink-100': 'bg-pink-100', 'pink-200': 'bg-pink-200', 'pink-300': 'bg-pink-300',
    'fuchsia-50': 'bg-fuchsia-50', 'fuchsia-100': 'bg-fuchsia-100', 'fuchsia-200': 'bg-fuchsia-200', 'fuchsia-300': 'bg-fuchsia-300',
    'violet-50': 'bg-violet-50', 'violet-100': 'bg-violet-100', 'violet-200': 'bg-violet-200', 'violet-300': 'bg-violet-300',
    'blue-50': 'bg-blue-50', 'blue-100': 'bg-blue-100', 'blue-200': 'bg-blue-200', 'blue-300': 'bg-blue-300',
    'indigo-50': 'bg-indigo-50', 'indigo-100': 'bg-indigo-100', 'indigo-200': 'bg-indigo-200', 'indigo-300': 'bg-indigo-300',
    'purple-50': 'bg-purple-50', 'purple-100': 'bg-purple-100', 'purple-200': 'bg-purple-200', 'purple-300': 'bg-purple-300',
    'yellow-50': 'bg-yellow-50', 'yellow-100': 'bg-yellow-100', 'yellow-200': 'bg-yellow-200', 'yellow-300': 'bg-yellow-300',
    'orange-50': 'bg-orange-50', 'orange-100': 'bg-orange-100', 'orange-200': 'bg-orange-200', 'orange-300': 'bg-orange-300',
    'amber-50': 'bg-amber-50', 'amber-100': 'bg-amber-100', 'amber-200': 'bg-amber-200', 'amber-300': 'bg-amber-300',
    'cyan-50': 'bg-cyan-50', 'cyan-100': 'bg-cyan-100', 'cyan-200': 'bg-cyan-200', 'cyan-300': 'bg-cyan-300',
    'green-50': 'bg-green-50', 'green-100': 'bg-green-100', 'green-200': 'bg-green-200', 'green-300': 'bg-green-300',
    'teal-50': 'bg-teal-50', 'teal-100': 'bg-teal-100', 'teal-200': 'bg-teal-200', 'teal-300': 'bg-teal-300',
    'sky-50': 'bg-sky-50', 'sky-100': 'bg-sky-100', 'sky-200': 'bg-sky-200', 'sky-300': 'bg-sky-300',
    'emerald-50': 'bg-emerald-50', 'emerald-100': 'bg-emerald-100', 'emerald-200': 'bg-emerald-200', 'emerald-300': 'bg-emerald-300',
    'lime-50': 'bg-lime-50', 'lime-100': 'bg-lime-100', 'lime-200': 'bg-lime-200', 'lime-300': 'bg-lime-300',
    'rose-50': 'bg-rose-50', 'rose-100': 'bg-rose-100', 'rose-200': 'bg-rose-200', 'rose-300': 'bg-rose-300',
    white: 'bg-white',
    'bg-tranparent': 'bg-gray-100',
    transparent: 'bg-gray-100',
};

function normalizeValue(value) {
    if (!value) {
        return 'gray-300';
    }

    return String(value)
        .replace(/^bg-/, '')
        .replace(/^text-/, '')
        .trim() || 'gray-300';
}

const currentValue = computed(() => normalizeValue(props.modelValue));

const triggerClass = computed(() => lightSwatches[currentValue.value] ?? 'bg-gray-300');

const displayLabel = computed(() => {
    if (currentValue.value === 'bg-tranparent' || currentValue.value === 'transparent') {
        return 'transparent';
    }

    return currentValue.value;
});

function isActive(value) {
    return currentValue.value === normalizeValue(value);
}

function swatchClass(value) {
    const key = normalizeValue(value);

    return [
        lightSwatches[key] ?? 'bg-gray-200',
        'mx-1 my-1 flex h-6 w-6 cursor-pointer items-center justify-center rounded border border-stone-300 transition hover:opacity-80',
        isActive(key)
            ? 'scale-110 ring-2 ring-black ring-offset-1'
            : '',
    ];
}

function selectColor(color, variant = null) {
    const next = variant != null ? `${color}-${variant}` : color;
    emit('update:modelValue', next);
}

function onDocumentClick(event) {
    if (!root.value?.contains(event.target)) {
        open.value = false;
    }
}

onMounted(() => document.addEventListener('click', onDocumentClick));
onBeforeUnmount(() => document.removeEventListener('click', onDocumentClick));
</script>

<template>
    <div ref="root" class="rounded-lg bg-gray-100/75 p-1">
        <div class="items-center sm:flex">
            <label
                v-if="label"
                :for="name"
                class="inline-block w-36 shrink-0 p-2 text-sm font-semibold text-gray-500"
            >
                {{ label }}
            </label>

            <div class="relative flex flex-row items-center gap-2">
                <button
                    type="button"
                    class="my-auto flex items-center gap-2 rounded-lg border border-stone-200 bg-white px-2 py-1.5 hover:bg-stone-50"
                    @click.stop="open = !open"
                >
                    <span
                        class="flex h-7 w-7 items-center justify-center rounded-md border border-stone-300 shadow-sm"
                        :class="triggerClass"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-stone-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                        </svg>
                    </span>
                    <span class="text-xs font-medium text-stone-600">{{ displayLabel }}</span>
                </button>

                <div
                    v-if="open"
                    class="absolute top-full z-10 mt-2 rounded-md border border-gray-300 shadow-lg ltr:left-0 ltr:origin-top-left rtl:right-0"
                    @click.stop
                >
                    <div class="rounded-md bg-white p-2 shadow-xs">
                        <div class="flex">
                            <div v-for="color in colors" :key="color">
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
                                        class="h-1.5 w-1.5 rounded-full bg-black"
                                    />
                                </button>
                            </div>

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
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
