<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import MainBox from '../../ui/MainBox.vue';
import { useUnitRentalStore } from '../../../stores/unit-rental.js';

const route = useRoute();
const catalog = useUnitRentalStore();
const contentType = computed(() => catalog.type);

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
    { key: 'units', label: 'أنواع الوحدات', to: '/manage/unit-rental' },
    { key: 'categories', label: 'التصنيفات', to: '/manage/unit-rental/categories' },
    { key: 'calendars', label: 'مخزون الوحدات', to: '/manage/unit-rental/calendars' },
    { key: 'customize', label: 'تخصيص القسم', to: '/manage/unit-rental/settings' },
];
</script>

<template>
    <MainBox :title="contentType.name" :subtitle="contentType.description">
        <template #icon>
            <img :src="`/${contentType.icon}`" class="h-7 w-7" alt="">
        </template>

        <div>
            <div class="flex border-b border-stone-200 px-px overflow-x-auto">
                <RouterLink
                    v-for="tab in subTabs"
                    :key="tab.key"
                    :to="tab.to"
                    class="whitespace-nowrap px-4 py-3 text-sm transition"
                    :class="section === tab.key ? 'border-b-2 border-primary-500 text-stone-900' : 'text-gray-500 hover:text-gray-800'"
                >
                    {{ tab.label }}
                </RouterLink>
            </div>

            <slot />
        </div>
    </MainBox>
</template>
