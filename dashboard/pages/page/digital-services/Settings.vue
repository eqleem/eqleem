<script setup>
import ManageLayout from '../../../components/page/ManageLayout.vue';
import Shell from '../../../components/page/digital-services/Shell.vue';
import SectionSettingsForm from '../../../components/page/SectionSettingsForm.vue';
import { useDigitalServicesStore } from '../../../stores/digital-services.js';

const store = useDigitalServicesStore();
</script>

<template>
    <ManageLayout>
        <Shell>
            <SectionSettingsForm
                :store="store"
                title-label="عنوان قسم الخدمات الرقمية"
                title-placeholder="الخدمات الرقمية"
                description-placeholder="خدمات رقمية احترافية مع مدة تسليم واضحة"
            />
        </Shell>
    </ManageLayout>
</template>
