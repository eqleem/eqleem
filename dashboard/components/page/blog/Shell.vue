<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import MainBox from '../../ui/MainBox.vue';
import { useBlogStore } from '../../../stores/blog.js';

const route = useRoute();
const store = useBlogStore();
const blogType = computed(() => store.type);

const section = computed(() => {
    if (route.name === 'blog-categories') {
        return 'categories';
    }

    if (route.name === 'blog-settings') {
        return 'settings';
    }

    return 'posts';
});

const subTabs = [
    { key: 'posts', label: 'التدوينات', to: '/manage/blog', icon: 'hugeicons:note-edit' },
    { key: 'categories', label: 'تصنيفات المدونة', to: '/manage/blog/categories', icon: 'hugeicons:folder-02' },
    { key: 'settings', label: 'تخصيص المدونة', to: '/manage/blog/settings', icon: 'hugeicons:paint-board' },
];
</script>

<template>
    <MainBox :title="blogType.name" :subtitle="blogType.description">
        <template #icon>
            <img :src="`/${blogType.icon}`" class="h-7 w-7" alt="">
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
