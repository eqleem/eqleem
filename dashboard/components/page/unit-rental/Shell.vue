<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import PageModuleShell from '../PageModuleShell.vue';
import { useUnitRentalStore } from '../../../stores/unit-rental.js';

const route = useRoute();
const catalog = useUnitRentalStore();
const type = computed(() => catalog.type);

const section = computed(() => {
    if (route.name === 'unit-rental-categories') {
        return 'categories';
    }

    if (route.name === 'unit-rental-calendars') {
        return 'calendars';
    }

    if (route.name === 'unit-rental-settings') {
        return 'customize';
    }

    return 'units';
});

const subTabs = [
    { key: 'units', label: 'أنواع الوحدات', to: '/manage/unit-rental', icon: 'hugeicons:building-03' },
    { key: 'categories', label: 'التصنيفات', to: '/manage/unit-rental/categories', icon: 'hugeicons:folder-02' },
    { key: 'calendars', label: 'مخزون الوحدات', to: '/manage/unit-rental/calendars', icon: 'hugeicons:calendar-03' },
    { key: 'customize', label: 'تخصيص القسم', to: '/manage/unit-rental/settings', icon: 'hugeicons:paint-board' },
];
</script>

<template>
    <PageModuleShell :type="type" :section="section" :sub-tabs="subTabs" nowrap>
        <slot />
    </PageModuleShell>
</template>
