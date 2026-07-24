<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import PageModuleShell from '../PageModuleShell.vue';
import { useServicesStore } from '../../../stores/services.js';

const route = useRoute();
const catalog = useServicesStore();
const type = computed(() => catalog.type);

const section = computed(() => {
    if (route.name === 'services-categories') {
        return 'categories';
    }

    if (route.name === 'services-calendars') {
        return 'calendars';
    }

    if (route.name === 'services-settings') {
        return 'customize';
    }

    return 'services';
});

const subTabs = [
    { key: 'services', label: 'الخدمات', to: '/manage/services', icon: 'hugeicons:customer-service-01' },
    { key: 'categories', label: 'التصنيفات', to: '/manage/services/categories', icon: 'hugeicons:folder-02' },
    { key: 'calendars', label: 'مقدمو الخدمات', to: '/manage/services/calendars', icon: 'hugeicons:calendar-03' },
    { key: 'customize', label: 'تخصيص القسم', to: '/manage/services/settings', icon: 'hugeicons:paint-board' },
];
</script>

<template>
    <PageModuleShell :type="type" :section="section" :sub-tabs="subTabs" nowrap>
        <slot />
    </PageModuleShell>
</template>
