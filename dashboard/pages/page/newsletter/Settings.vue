<script setup>
import ManageLayout from '../../../components/page/ManageLayout.vue';
import Shell from '../../../components/page/newsletter/Shell.vue';
import SectionSettingsForm from '../../../components/page/SectionSettingsForm.vue';
import { useNewsletterStore } from '../../../stores/newsletter.js';

const store = useNewsletterStore();
</script>

<template>
    <ManageLayout>
        <Shell>
            <SectionSettingsForm
                :store="store"
                title-label="عنوان قسم النشرة البريدية"
                title-placeholder="النشرة البريدية"
                description-placeholder="أحدث مقالات النشرة الأسبوعية ونشراتنا المتخصصة"
            />
        </Shell>
    </ManageLayout>
</template>
