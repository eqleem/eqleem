<script setup>
import ManageLayout from '../../../components/page/ManageLayout.vue';
import Shell from '../../../components/page/services/Shell.vue';
import SectionSettingsForm from '../../../components/page/SectionSettingsForm.vue';
import { useServicesStore } from '../../../stores/services.js';

const store = useServicesStore();
</script>

<template>
    <ManageLayout>
        <Shell>
            <SectionSettingsForm
                :store="store"
                title-label="عنوان قسم الخدمات"
                title-placeholder="الخدمات"
                description-placeholder="احجز خدماتنا المتخصصة بسهولة"
            />
        </Shell>
    </ManageLayout>
</template>
