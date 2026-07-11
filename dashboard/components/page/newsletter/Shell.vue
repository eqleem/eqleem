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
    { key: 'issues', label: 'النشرات البريدية', to: '/manage/newsletter' },
    { key: 'customize', label: 'تخصيص النشرة', to: '/manage/newsletter/settings' },
];
</script>

<template>
    <MainBox :title="newsletterType.name" :subtitle="newsletterType.description">
        <template #icon>
            <img :src="`/${newsletterType.icon}`" class="h-7 w-7" alt="">
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
