<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import MainBox from '../../ui/MainBox.vue';
import { useDigitalServicesStore } from '../../../stores/digital-services.js';

const route = useRoute();
const catalog = useDigitalServicesStore();
const contentType = computed(() => catalog.type);

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
    { key: 'services', label: 'الخدمات', to: '/manage/digital-services' },
    { key: 'categories', label: 'التصنيفات', to: '/manage/digital-services/categories' },
    { key: 'customize', label: 'تخصيص القسم', to: '/manage/digital-services/settings' },
];
</script>

<template>
    <MainBox :title="contentType.name" :subtitle="contentType.description">
        <template #icon>
            <img :src="`/${contentType.icon}`" class="h-7 w-7" alt="">
        </template>

        <div>
            <div class="flex border-b border-stone-200 px-px">
                <RouterLink
                    v-for="tab in subTabs"
                    :key="tab.key"
                    :to="tab.to"
                    class="px-4 py-3 text-sm transition"
                    :class="section === tab.key ? 'border-b-2 border-primary-500 text-stone-900' : 'text-gray-500 hover:text-gray-800'"
                >
                    {{ tab.label }}
                </RouterLink>
            </div>

            <slot />
        </div>
    </MainBox>
</template>
