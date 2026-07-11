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
    { key: 'products', label: 'المنتجات', to: '/manage/store' },
    { key: 'categories', label: 'تصنيفات المتجر', to: '/manage/store/categories' },
    { key: 'customize', label: 'تخصيص المتجر', to: '/manage/store/settings' },
    { key: 'payment-options', label: 'وسائل الدفع', to: '/manage/store/payment-options' },
    { key: 'shipping-options', label: 'وسائل الشحن', to: '/manage/store/shipping-options' },
];
</script>

<template>
    <MainBox :title="storeType.name" :subtitle="storeType.description">
        <template #icon>
            <img :src="`/${storeType.icon}`" class="h-7 w-7" alt="">
        </template>

        <div>
            <div class="flex border-b border-stone-200 px-px">
                <RouterLink
                    v-for="tab in subTabs"
                    :key="tab.key"
                    :to="tab.to"
                    class="px-4 py-3 text-sm transition"
                    :class="section === tab.key ? 'border-b-2 border-primary-500 text-stone-900' : 'text-gray-500 hover:text-gray-800'"
                >
                    {{ tab.label }}
                </RouterLink>
            </div>

            <slot />
        </div>
    </MainBox>
</template>
