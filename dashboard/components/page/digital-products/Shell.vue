<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import MainBox from '../../ui/MainBox.vue';
import { useDigitalProductsStore } from '../../../stores/digital-products.js';

const route = useRoute();
const catalog = useDigitalProductsStore();
const contentType = computed(() => catalog.type);

const section = computed(() => {
    if (route.name === 'digital-products-categories') {
        return 'categories';
    }

    if (route.name === 'digital-products-settings') {
        return 'customize';
    }

    return 'products';
});

const subTabs = [
    { key: 'products', label: 'المنتجات', to: '/manage/digital-products', icon: 'hugeicons:package-01' },
    { key: 'categories', label: 'التصنيفات', to: '/manage/digital-products/categories', icon: 'hugeicons:folder-02' },
    { key: 'customize', label: 'تخصيص القسم', to: '/manage/digital-products/settings', icon: 'hugeicons:paint-board' },
];
</script>

<template>
    <MainBox :title="contentType.name" :subtitle="contentType.description">
        <template #icon>
            <img :src="`/${contentType.icon}`" class="h-7 w-7" alt="">
        </template>

        <div>
            <div class="flex border-b border-stone-200 px-px flex items-center overflow-x-auto no-scrollbar">
                <RouterLink
                    v-for="tab in subTabs"
                    :key="tab.key"
                    :to="tab.to"
                    class="inline-flex items-center gap-1.5 px-4 py-3 text-sm transition shrink-0"
                    :class="section === tab.key ? 'border-b-2 border-primary-500 text-stone-900' : 'text-stone-500 hover:text-stone-800'"
                >
                    <iconify-icon :icon="tab.icon" class="text-base"></iconify-icon>
                    {{ tab.label }}
                </RouterLink>
            </div>

            <slot />
        </div>
    </MainBox>
</template>
