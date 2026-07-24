<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import PageModuleShell from '../PageModuleShell.vue';
import { useMenuStore } from '../../../stores/menu.js';

const route = useRoute();
const menu = useMenuStore();
const type = computed(() => menu.type);

const section = computed(() => {
    if (route.name === 'menu-categories') {
        return 'categories';
    }

    if (route.name === 'menu-settings') {
        return 'customize';
    }

    return 'items';
});

const subTabs = [
    { key: 'items', label: 'الأطباق', to: '/manage/menu', icon: 'hugeicons:restaurant-01' },
    { key: 'categories', label: 'تصنيفات القائمة', to: '/manage/menu/categories', icon: 'hugeicons:folder-02' },
    { key: 'customize', label: 'تخصيص القائمة', to: '/manage/menu/settings', icon: 'hugeicons:paint-board' },
];
</script>

<template>
    <PageModuleShell :type="type" :section="section" :sub-tabs="subTabs">
        <slot />
    </PageModuleShell>
</template>
