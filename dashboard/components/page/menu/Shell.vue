<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import MainBox from '../../ui/MainBox.vue';
import { useMenuStore } from '../../../stores/menu.js';

const route = useRoute();
const menu = useMenuStore();
const menuType = computed(() => menu.type);

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
    <MainBox :title="menuType.name" :subtitle="menuType.description">
        <template #icon>
            <img :src="`/${menuType.icon}`" class="h-7 w-7" alt="">
        </template>

        <div>
            <div class="flex border-b border-stone-200 px-px flex items-center overflow-x-auto no-scrollbar">
                <RouterLink
                    v-for="tab in subTabs"
                    :key="tab.key"
                    :to="tab.to"
                    class="inline-flex items-center gap-1.5 px-4 py-3 text-sm transition shrink-0"
                    :class="section === tab.key ? 'border-b-2 border-primary-500 text-stone-900' : 'text-gray-500 hover:text-gray-800'"
                >
                    <iconify-icon :icon="tab.icon" class="text-base"></iconify-icon>
                    {{ tab.label }}
                </RouterLink>
            </div>

            <slot />
        </div>
    </MainBox>
</template>
