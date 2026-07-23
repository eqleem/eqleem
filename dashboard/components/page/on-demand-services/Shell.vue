<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import MainBox from '../../ui/MainBox.vue';
import { useOnDemandServicesStore } from '../../../stores/on-demand-services.js';

const route = useRoute();
const catalog = useOnDemandServicesStore();
const contentType = computed(() => catalog.type);

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
    <MainBox :title="contentType.name" :subtitle="contentType.description">
        <template #icon>
            <img :src="`/${contentType.icon}`" class="h-7 w-7" alt="">
        </template>

        <div>
            <div class="flex border-b border-stone-200 px-px flex items-center overflow-x-auto no-scrollbar">
                <RouterLink
                    v-for="tab in subTabs"
                    :key="tab.key"
                    :to="tab.to"
                    class="inline-flex items-center gap-1.5 px-4 py-3 text-sm transition shrink-0"
                    :class="section === tab.key ? 'border-b-2 border-primary-500 text-stone-900' : 'text-stone-500 hover:text-stone-800'"
                >
                    <iconify-icon :icon="tab.icon" class="text-base"></iconify-icon>
                    {{ tab.label }}
                </RouterLink>
            </div>

            <slot />
        </div>
    </MainBox>
</template>
