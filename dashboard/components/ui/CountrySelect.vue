<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import Field from './Field.vue';
import { countries as defaultCountries, countryByCode, defaultCountryCode } from '../../data/countries.js';

const props = defineProps({
    modelValue: { type: String, default: defaultCountryCode },
    name: { type: String, default: 'country' },
    label: { type: String, default: null },
    error: { type: String, default: null },
    placeholder: { type: String, default: 'ابحث عن الدولة…' },
    countries: { type: Array, default: null },
});

const emit = defineEmits(['update:modelValue']);

const open = ref(false);
const query = ref('');
const root = ref(null);
const searchInput = ref(null);

const list = computed(() => (
    Array.isArray(props.countries) && props.countries.length
        ? props.countries
        : defaultCountries
));

const selected = computed(() => (
    countryByCode(props.modelValue)
    || list.value.find((country) => country.code === props.modelValue)
    || list.value.find((country) => country.code === defaultCountryCode)
    || null
));

const filtered = computed(() => {
    const q = query.value.trim().toLowerCase();

    if (!q) {
        return list.value;
    }

    return list.value.filter((country) => {
        const haystack = [
            country.code,
            country.name,
            country.nameEn,
            country.emoji,
        ]
            .filter(Boolean)
            .join(' ')
            .toLowerCase();

        return haystack.includes(q);
    });
});

watch(
    () => props.modelValue,
    (value) => {
        if (!value) {
            emit('update:modelValue', defaultCountryCode);
        }
    },
    { immediate: true },
);

watch(open, async (isOpen) => {
    if (!isOpen) {
        query.value = '';
        return;
    }

    await nextTick();
    searchInput.value?.focus();
});

function toggle() {
    open.value = !open.value;
}

function select(country) {
    emit('update:modelValue', country.code);
    open.value = false;
    query.value = '';
}

function onDocumentClick(event) {
    if (!root.value?.contains(event.target)) {
        open.value = false;
    }
}

function onKeydown(event) {
    if (event.key === 'Escape') {
        open.value = false;
    }
}

onMounted(() => {
    document.addEventListener('click', onDocumentClick);
    document.addEventListener('keydown', onKeydown);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', onDocumentClick);
    document.removeEventListener('keydown', onKeydown);
});
</script>

<template>
    <Field :name="name" :label="label" :error="error" width="w-full">
        <div ref="root" class="relative w-full">
            <button
                :id="name"
                type="button"
                class="flex w-full items-center gap-2 rounded-md border border-transparent bg-white p-2 px-3 py-2 text-start text-sm text-stone-700 focus:border-primary-400 focus:outline-none"
                :aria-expanded="open"
                aria-haspopup="listbox"
                @click="toggle"
            >
                <span v-if="selected" class="text-base leading-none">{{ selected.emoji }}</span>
                <span class="min-w-0 flex-1 truncate">
                    {{ selected?.name || placeholder }}
                </span>
                <span class="shrink-0 text-xs text-stone-400" dir="ltr">{{ selected?.code }}</span>
                <iconify-icon icon="hugeicons:arrow-down-01" class="shrink-0 text-lg text-stone-400"></iconify-icon>
            </button>

            <div
                v-if="open"
                class="absolute inset-x-0 top-full z-30 mt-1 overflow-hidden rounded-lg border border-stone-200 bg-white shadow-lg"
            >
                <div class="border-b border-stone-100 p-2">
                    <input
                        ref="searchInput"
                        v-model="query"
                        type="search"
                        class="w-full rounded-md border border-stone-200 bg-stone-50 px-3 py-2 text-sm text-stone-700 outline-none focus:border-primary-400"
                        :placeholder="placeholder"
                        dir="rtl"
                        @click.stop
                    >
                </div>

                <ul class="max-h-56 overflow-y-auto py-1" role="listbox">
                    <li v-if="filtered.length === 0" class="px-3 py-2 text-sm text-stone-400">
                        لا توجد نتائج
                    </li>
                    <li
                        v-for="country in filtered"
                        :key="country.code"
                        role="option"
                        class="flex cursor-pointer items-center gap-2 px-3 py-2 text-sm transition hover:bg-stone-50"
                        :class="country.code === modelValue ? 'bg-primary-50 text-primary-800' : 'text-stone-700'"
                        @click="select(country)"
                    >
                        <span class="text-base leading-none">{{ country.emoji }}</span>
                        <span class="min-w-0 flex-1 truncate">{{ country.name }}</span>
                        <span class="shrink-0 text-xs text-stone-400" dir="ltr">{{ country.code }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </Field>
</template>
