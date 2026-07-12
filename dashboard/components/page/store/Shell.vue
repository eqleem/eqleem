<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import MainBox from '../../ui/MainBox.vue';
import { useStoreStore } from '../../../stores/store.js';

// Store-specific shell — labels match Livewire store/index.blade.php tabs.
const route = useRoute();
const store = useStoreStore();
const storeType = computed(() => store.type);

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

    // store-home + store-detail keep "المنتجات" active
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
    <MainBox :title="storeType.name" :subtitle="storeType.description">
        <template #icon>
            <img :src="`/${storeType.icon}`" class="h-7 w-7" alt="">
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
