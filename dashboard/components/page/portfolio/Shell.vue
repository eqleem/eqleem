<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import MainBox from '../../ui/MainBox.vue';
import { usePortfolioStore } from '../../../stores/portfolio.js';

// Portfolio-specific shell — labels match Livewire portfolio/index.blade.php tabs.
const route = useRoute();
const store = usePortfolioStore();
const portfolioType = computed(() => store.type);

const section = computed(() => {
    if (route.name === 'portfolio-categories') {
        return 'categories';
    }

    if (route.name === 'portfolio-settings') {
        return 'settings';
    }

    // portfolio-home + portfolio-detail keep "المشاريع" active
    return 'projects';
});

const subTabs = [
    { key: 'projects', label: 'المشاريع', to: '/manage/portfolio' },
    { key: 'categories', label: 'التصنيفات', to: '/manage/portfolio/categories' },
    { key: 'settings', label: 'تخصيص الأعمال', to: '/manage/portfolio/settings' },
];
</script>

<template>
    <MainBox :title="portfolioType.name" :subtitle="portfolioType.description">
        <template #icon>
            <img :src="`/${portfolioType.icon}`" class="h-7 w-7" alt="">
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
