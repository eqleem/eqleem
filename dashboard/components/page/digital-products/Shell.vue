<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import PageModuleShell from '../PageModuleShell.vue';
import { useDigitalProductsStore } from '../../../stores/digital-products.js';

const route = useRoute();
const catalog = useDigitalProductsStore();
const type = computed(() => catalog.type);

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
    <PageModuleShell :type="type" :section="section" :sub-tabs="subTabs">
        <slot />
    </PageModuleShell>
</template>
