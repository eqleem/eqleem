<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import PageModuleShell from '../PageModuleShell.vue';
import { useStoreStore } from '../../../stores/store.js';

const route = useRoute();
const store = useStoreStore();
const type = computed(() => store.type);

const section = computed(() => {
    if (route.name === 'store-categories') {
        return 'categories';
    }

    if (route.name === 'store-settings') {
        return 'customize';
    }

    if (route.name === 'store-payment-options') {
        return 'payment-options';
    }

    if (route.name === 'store-shipping-options') {
        return 'shipping-options';
    }

    return 'products';
});

const subTabs = [
    { key: 'products', label: 'المنتجات', to: '/manage/store', icon: 'hugeicons:shopping-bag-01' },
    { key: 'categories', label: 'تصنيفات المتجر', to: '/manage/store/categories', icon: 'hugeicons:folder-02' },
    { key: 'customize', label: 'تخصيص المتجر', to: '/manage/store/settings', icon: 'hugeicons:paint-board' },
    { key: 'payment-options', label: 'وسائل الدفع', to: '/manage/store/payment-options', icon: 'hugeicons:credit-card' },
    { key: 'shipping-options', label: 'وسائل الشحن', to: '/manage/store/shipping-options', icon: 'hugeicons:delivery-truck-01' },
];
</script>

<template>
    <PageModuleShell :type="type" :section="section" :sub-tabs="subTabs">
        <slot />
    </PageModuleShell>
</template>
