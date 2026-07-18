<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import Field from './Field.vue';

/**
 * Searchable dropdown (CountrySelect-style).
 * options: [{ id, label, description?, icon? }]
 */
const props = defineProps({
    modelValue: { type: [String, Number], default: '' },
    name: { type: String, default: 'searchable-select' },
    label: { type: String, default: null },
    info: { type: String, default: '' },
    error: { type: String, default: null },
    placeholder: { type: String, default: 'ابحث…' },
    emptyLabel: { type: String, default: 'لا توجد نتائج' },
    clearable: { type: Boolean, default: false },
    clearLabel: { type: String, default: 'بدون اختيار' },
    options: { type: Array, default: () => [] },
});

const emit = defineEmits(['update:modelValue']);

const open = ref(false);
const query = ref('');
const root = ref(null);
const searchInput = ref(null);

const list = computed(() => (
    Array.isArray(props.options)
        ? props.options.map((option) => ({
            id: String(option.id ?? option.type ?? option.value ?? ''),
            label: String(option.label ?? option.name ?? option.id ?? ''),
            description: String(option.description ?? ''),
            icon: option.icon ?? null,
        }))
        : []
));

const selected = computed(() => list.value.find((option) => option.id === String(props.modelValue)) ?? null);

const filtered = computed(() => {
    const q = query.value.trim().toLowerCase();

    if (!q) {
        return list.value;
    }

    return list.value.filter((option) => {
        const haystack = [option.id, option.label, option.description]
            .filter(Boolean)
            .join(' ')
            .toLowerCase();

        return haystack.includes(q);
    });
});

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

function select(option) {
    emit('update:modelValue', option.id);
    open.value = false;
    query.value = '';
}

function clear() {
    emit('update:modelValue', '');
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
    <Field :name="name" :label="label" :info="info" :error="error" width="w-full">
        <div ref="root" class="relative w-full">
            <button
                :id="name"
                type="button"
                class="flex w-full items-center gap-2 rounded-md border border-transparent bg-white p-2 px-3 py-2 text-start text-sm text-stone-700 focus:border-primary-400 focus:outline-none"
                :aria-expanded="open"
                aria-haspopup="listbox"
                @click="toggle"
            >
                <iconify-icon
                    v-if="selected?.icon"
                    :icon="selected.icon"
                    class="shrink-0 text-lg text-stone-500"
                ></iconify-icon>
                <span class="min-w-0 flex-1 truncate" :class="selected ? 'text-stone-700' : 'text-stone-400'">
                    {{ selected?.label || placeholder }}
                </span>
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
                    <li
                        v-if="clearable"
                        role="option"
                        class="cursor-pointer px-3 py-2 text-sm text-stone-500 transition hover:bg-stone-50"
                        @click="clear"
                    >
                        {{ clearLabel }}
                    </li>
                    <li v-if="filtered.length === 0" class="px-3 py-2 text-sm text-stone-400">
                        {{ emptyLabel }}
                    </li>
                    <li
                        v-for="option in filtered"
                        :key="option.id"
                        role="option"
                        class="flex cursor-pointer items-center gap-2 px-3 py-2 text-sm transition hover:bg-stone-50"
                        :class="option.id === String(modelValue) ? 'bg-primary-50 text-primary-800' : 'text-stone-700'"
                        @click="select(option)"
                    >
                        <iconify-icon
                            v-if="option.icon"
                            :icon="option.icon"
                            class="shrink-0 text-lg"
                        ></iconify-icon>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate font-medium">{{ option.label }}</span>
                            <span v-if="option.description" class="block truncate text-xs text-stone-400">{{ option.description }}</span>
                        </span>
                        <iconify-icon
                            v-if="option.id === String(modelValue)"
                            icon="hugeicons:tick-02"
                            class="shrink-0 text-lg text-primary-600"
                        ></iconify-icon>
                    </li>
                </ul>
            </div>
        </div>
    </Field>
</template>
