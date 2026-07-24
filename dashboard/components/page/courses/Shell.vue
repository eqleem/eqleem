<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import PageModuleShell from '../PageModuleShell.vue';
import { useCoursesStore } from '../../../stores/courses.js';

const route = useRoute();
const catalog = useCoursesStore();
const type = computed(() => catalog.type);

const section = computed(() => {
    if (route.name === 'courses-categories') {
        return 'categories';
    }

    if (route.name === 'courses-settings') {
        return 'customize';
    }

    return 'courses';
});

const subTabs = [
    { key: 'courses', label: 'الدورات', to: '/manage/courses', icon: 'hugeicons:online-learning-01' },
    { key: 'categories', label: 'التصنيفات', to: '/manage/courses/categories', icon: 'hugeicons:folder-02' },
    { key: 'customize', label: 'تخصيص القسم', to: '/manage/courses/settings', icon: 'hugeicons:paint-board' },
];
</script>

<template>
    <PageModuleShell :type="type" :section="section" :sub-tabs="subTabs">
        <slot />
    </PageModuleShell>
</template>
