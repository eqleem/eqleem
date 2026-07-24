<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import PageModuleShell from '../PageModuleShell.vue';
import { usePortfolioStore } from '../../../stores/portfolio.js';

const route = useRoute();
const store = usePortfolioStore();
const type = computed(() => store.type);

const section = computed(() => {
    if (route.name === 'portfolio-categories') {
        return 'categories';
    }

    if (route.name === 'portfolio-settings') {
        return 'settings';
    }

    return 'projects';
});

const subTabs = [
    { key: 'projects', label: 'المشاريع', to: '/manage/portfolio', icon: 'hugeicons:folder-library' },
    { key: 'categories', label: 'التصنيفات', to: '/manage/portfolio/categories', icon: 'hugeicons:folder-02' },
    { key: 'settings', label: 'تخصيص الأعمال', to: '/manage/portfolio/settings', icon: 'hugeicons:paint-board' },
];
</script>

<template>
    <PageModuleShell :type="type" :section="section" :sub-tabs="subTabs">
        <slot />
    </PageModuleShell>
</template>
