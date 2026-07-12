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
    { key: 'projects', label: 'المشاريع', to: '/manage/portfolio', icon: 'hugeicons:folder-library' },
    { key: 'categories', label: 'التصنيفات', to: '/manage/portfolio/categories', icon: 'hugeicons:folder-02' },
    { key: 'settings', label: 'تخصيص الأعمال', to: '/manage/portfolio/settings', icon: 'hugeicons:paint-board' },
];
</script>

<template>
    <MainBox :title="portfolioType.name" :subtitle="portfolioType.description">
        <template #icon>
            <img :src="`/${portfolioType.icon}`" class="h-7 w-7" alt="">
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
