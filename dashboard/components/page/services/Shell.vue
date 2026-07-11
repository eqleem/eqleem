<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import MainBox from '../../ui/MainBox.vue';
import { useServicesStore } from '../../../stores/services.js';

const route = useRoute();
const catalog = useServicesStore();
const contentType = computed(() => catalog.type);

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
    { key: 'services', label: 'الخدمات', to: '/manage/services' },
    { key: 'categories', label: 'التصنيفات', to: '/manage/services/categories' },
    { key: 'calendars', label: 'مقدمو الخدمات', to: '/manage/services/calendars' },
    { key: 'customize', label: 'تخصيص القسم', to: '/manage/services/settings' },
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
