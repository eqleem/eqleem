<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import PageModuleShell from '../PageModuleShell.vue';
import { useNewsletterStore } from '../../../stores/newsletter.js';

const route = useRoute();
const store = useNewsletterStore();
const type = computed(() => store.type);

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
    <PageModuleShell :type="type" :section="section" :sub-tabs="subTabs">
        <slot />
    </PageModuleShell>
</template>
