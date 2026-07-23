<script setup>
import Input from '../../ui/Input.vue';
import Toggle from '../../ui/Toggle.vue';
import { usePageAdvancedOpen } from '../../../composables/usePageAdvancedOpen.js';

const published = defineModel('published', { type: Boolean, default: false });
const slug = defineModel('slug', { type: String, default: '' });

defineProps({
    slugPrefix: { type: String, default: '/' },
    slugError: { type: String, default: null },
});

const { open, toggle: toggleAdvanced } = usePageAdvancedOpen();

function onSlugInput(value) {
    slug.value = String(value ?? '').replace(/\s/g, '');
}
</script>

<template>
    <div class="space-y-4">
        <div class="border-t border-stone-200 pt-5">
            <Toggle v-model="published" name="published" label="مفعّل" />
        </div>

        <div class="rounded-xl bg-stone-100/70 p-3">
            <button
                type="button"
                class="flex w-full items-center gap-1.5 rounded-lg px-1 py-1.5 text-sm font-medium text-stone-600 transition hover:text-stone-900"
                :aria-expanded="open"
                @click="toggleAdvanced"
            >
                <span>متقدم</span>
                <svg
                    class="h-4 w-4 transition-transform"
                    :class="open ? 'rotate-180' : ''"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.5"
                    aria-hidden="true"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
                </svg>
            </button>

            <div v-show="open" class="mt-3 space-y-2 px-1 pb-1">
                <Input
                    :model-value="slug"
                    name="slug"
                    label="نص الرابط"
                    dir="ltr"
                    info-dir="rtl"
                    :prefix="slugPrefix"
                    :error="slugError"
                    @update:model-value="onSlugInput"
                />
                <slot />
            </div>
        </div>
    </div>
</template>
