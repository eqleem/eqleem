<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import MainBox from '../../ui/MainBox.vue';
import { useNewsletterStore } from '../../../stores/newsletter.js';

const route = useRoute();
const store = useNewsletterStore();
const newsletterType = computed(() => store.type);

const section = computed(() => {
    if (route.name === 'newsletter-settings') {
        return 'customize';
    }

    return 'issues';
});

const subTabs = [
    { key: 'issues', label: 'النشرات البريدية', to: '/manage/newsletter', icon: 'hugeicons:mail-01' },
    { key: 'customize', label: 'تخصيص النشرة', to: '/manage/newsletter/settings', icon: 'hugeicons:paint-board' },
];
</script>

<template>
    <MainBox :title="newsletterType.name" :subtitle="newsletterType.description">
        <template #icon>
            <img :src="`/${newsletterType.icon}`" class="h-7 w-7" alt="">
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
