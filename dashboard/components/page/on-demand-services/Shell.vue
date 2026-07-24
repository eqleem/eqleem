<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import PageModuleShell from '../PageModuleShell.vue';
import { useOnDemandServicesStore } from '../../../stores/on-demand-services.js';

const route = useRoute();
const catalog = useOnDemandServicesStore();
const type = computed(() => catalog.type);

const section = computed(() => {
    if (route.name === 'on-demand-services-settings') {
        return 'customize';
    }

    return 'services';
});

const subTabs = [
    { key: 'services', label: 'الخدمات', to: '/manage/on-demand-services', icon: 'hugeicons:ruler' },
    { key: 'customize', label: 'تخصيص القسم', to: '/manage/on-demand-services/settings', icon: 'hugeicons:paint-board' },
];
</script>

<template>
    <PageModuleShell :type="type" :section="section" :sub-tabs="subTabs">
        <slot />
    </PageModuleShell>
</template>
