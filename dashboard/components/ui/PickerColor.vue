<script setup>
import { computed, ref, watch } from 'vue';

// Port of resources/views/ui/picker-color.blade.php — static class map for Tailwind.
const props = defineProps({
    modelValue: { type: String, default: null },
    name: { type: String, default: 'color' },
    label: { type: String, default: null },
    options: { type: Array, default: () => [] },
    allowCustom: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue']);

const swatchClass = {
    blue: 'bg-blue-500',
    cyan: 'bg-cyan-500',
    green: 'bg-green-500',
    teal: 'bg-teal-500',
    sky: 'bg-sky-500',
    purple: 'bg-purple-500',
    violet: 'bg-violet-500',
    indigo: 'bg-indigo-500',
    red: 'bg-red-500',
    pink: 'bg-pink-500',
    fuchsia: 'bg-fuchsia-500',
    orange: 'bg-orange-500',
    amber: 'bg-amber-500',
    yellow: 'bg-yellow-500',
    zinc: 'bg-zinc-500',
    emerald: 'bg-emerald-500',
    lime: 'bg-lime-500',
    rose: 'bg-rose-500',
    gray: 'bg-stone-500',
    stone: 'bg-stone-500',
    slate: 'bg-slate-500',
};

const colorInput = ref(null);

const colors = computed(() => props.options.map((item) => String(item)));

const currentValue = computed(() => (props.modelValue ? String(props.modelValue) : null));

const isCustom = computed(() => isHexColor(currentValue.value));

const customHex = ref(isCustom.value ? normalizeHex(currentValue.value) : '#3d5ccc');

watch(currentValue, (value) => {
    if (isHexColor(value)) {
        customHex.value = normalizeHex(value);
    }
});

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

function isActive(color) {
    return currentValue.value === color;
}

function selectNamed(color) {
    emit('update:modelValue', color);
}

function selectCustom(event) {
    const hex = normalizeHex(event.target.value);
    customHex.value = hex;
    emit('update:modelValue', hex);
}

function openCustomPicker() {
    colorInput.value?.click();
}
</script>

<template>
    <div class="rounded-lg bg-black/5 p-1">
        <div class="items-center sm:flex">
            <label
                v-if="label"
                :for="name"
                class="inline-block w-36 shrink-0 p-2 text-sm font-semibold text-stone-500"
            >
                {{ label }}
            </label>

            <div class="flex flex-wrap items-center gap-0.5">
                <label
                    v-for="color in colors"
                    :key="color"
                    :for="`pick-${name}-${color}`"
                    class="block cursor-pointer rounded-lg border-2 p-0.5 transition hover:bg-black/10"
                    :class="isActive(color) ? 'scale-105 border-black' : 'border-transparent'"
                    :title="color"
                    :aria-pressed="isActive(color)"
                >
                    <input
                        :id="`pick-${name}-${color}`"
                        type="radio"
                        :name="name"
                        :value="color"
                        class="peer hidden"
                        :checked="isActive(color)"
                        @change="selectNamed(color)"
                    >
                    <div
                        class="flex h-7 w-7 items-center justify-center rounded-md ring-offset-1 transition"
                        :class="[
                            swatchClass[color] ?? 'bg-stone-400',
                            isActive(color) ? 'ring-2 ring-black ring-offset-1' : '',
                        ]"
                    >
                        <span
                            v-if="isActive(color)"
                            class="h-1.5 w-1.5 rounded-full bg-white shadow-sm"
                        />
                    </div>
                </label>

                <button
                    v-if="allowCustom"
                    type="button"
                    class="relative block cursor-pointer rounded-lg border-2 p-0.5 transition hover:bg-black/10"
                    :class="isCustom ? 'scale-105 border-black' : 'border-transparent'"
                    title="لون مخصص"
                    :aria-pressed="isCustom"
                    @click="openCustomPicker"
                >
                    <span
                        class="flex h-7 w-7 items-center justify-center rounded-md border border-stone-300 ring-offset-1 transition"
                        :class="isCustom ? 'ring-2 ring-black ring-offset-1' : ''"
                        :style="{ backgroundColor: isCustom ? currentValue : customHex }"
                    >
                        <span
                            v-if="isCustom"
                            class="h-1.5 w-1.5 rounded-full bg-white shadow-sm"
                        />
                        <svg
                            v-else
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-3.5 w-3.5 text-stone-700 drop-shadow-sm"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" />
                        </svg>
                    </span>
                    <input
                        ref="colorInput"
                        type="color"
                        class="pointer-events-none absolute inset-0 h-0 w-0 opacity-0"
                        :value="customHex"
                        @input="selectCustom"
                    >
                </button>

                <span
                    v-if="currentValue"
                    class="ms-1 text-xs font-medium text-stone-600"
                    :class="isCustom ? 'uppercase' : 'capitalize'"
                >
                    {{ currentValue }}
                </span>
            </div>
        </div>
    </div>
</template>
