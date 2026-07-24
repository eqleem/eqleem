<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import PageModuleShell from '../PageModuleShell.vue';
import { useBlogStore } from '../../../stores/blog.js';

const route = useRoute();
const store = useBlogStore();
const type = computed(() => store.type);

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
    <PageModuleShell :type="type" :section="section" :sub-tabs="subTabs">
        <slot />
    </PageModuleShell>
</template>
