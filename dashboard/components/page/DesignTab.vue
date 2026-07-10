<script setup>
import { ref } from 'vue';
import MainBox from '../ui/MainBox.vue';
import Icon from '../ui/Icon.vue';
import { themes } from '../../data/page.js';

const selectedThemeId = ref(themes.find((theme) => theme.is_active)?.id ?? themes[0]?.id);
</script>

<template>
    <MainBox title="تصميم الصفحة" subtitle="تخصيص الألوان والخطوط والمظهر العام للصفحة.">
        <template #icon>
            <img :src="'/assets/icons/tabler/color-swatch.svg'" class="h-7 w-7" alt="">
        </template>

        <div class="p-4">
            <div class="rounded-xl bg-stone-300/30 p-2">
                <div class="flex gap-2 overflow-x-auto">
                    <button
                        v-for="theme in themes"
                        :key="theme.id"
                        type="button"
                        class="group relative w-24 shrink-0 rounded-lg border-2 text-start transition sm:w-40"
                        :class="selectedThemeId === theme.id ? 'border-primary-500 shadow-md' : 'border-transparent bg-white hover:border-stone-200 hover:shadow-sm'"
                        @click="selectedThemeId = theme.id"
                    >
                        <span v-if="theme.is_active" class="absolute start-1.5 top-1.5 z-10 inline-flex items-center gap-0.5 rounded-full bg-green-500 px-1.5 py-0.5 text-[9px] font-semibold text-white shadow-sm">
                            <Icon name="check" class="h-2.5 w-2.5" /> نشط
                        </span>
                        <div class="flex aspect-square w-full items-center justify-center overflow-hidden rounded-t-md bg-stone-100 text-stone-300">
                            <Icon name="invoice" class="h-8 w-8" />
                        </div>
                        <div class="rounded-b-lg bg-white px-2 py-1.5">
                            <div class="flex items-center justify-between gap-2">
                                <span class="min-w-0 truncate text-[11px] font-medium text-stone-700">{{ theme.name }}</span>
                                <span class="shrink-0 text-[11px] font-semibold text-green-600">{{ theme.price_label }}</span>
                            </div>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </MainBox>
</template>
