<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import PageModuleShell from '../PageModuleShell.vue';
import { useDigitalServicesStore } from '../../../stores/digital-services.js';

const route = useRoute();
const catalog = useDigitalServicesStore();
const type = computed(() => catalog.type);

const section = computed(() => {
    if (route.name === 'digital-services-categories') {
        return 'categories';
    }

    if (route.name === 'digital-services-settings') {
        return 'customize';
    }

    return 'services';
});

const subTabs = [
    { key: 'services', label: 'الخدمات', to: '/manage/digital-services', icon: 'hugeicons:laptop' },
    { key: 'categories', label: 'التصنيفات', to: '/manage/digital-services/categories', icon: 'hugeicons:folder-02' },
    { key: 'customize', label: 'تخصيص القسم', to: '/manage/digital-services/settings', icon: 'hugeicons:paint-board' },
];
</script>

<template>
    <PageModuleShell :type="type" :section="section" :sub-tabs="subTabs">
        <slot />
    </PageModuleShell>
</template>
