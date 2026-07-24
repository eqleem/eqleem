<script setup>
import ManageLayout from '../../../components/page/ManageLayout.vue';
import Shell from '../../../components/page/on-demand-services/Shell.vue';
import SectionSettingsForm from '../../../components/page/SectionSettingsForm.vue';
import { useOnDemandServicesStore } from '../../../stores/on-demand-services.js';

const store = useOnDemandServicesStore();
</script>

<template>
    <ManageLayout>
        <Shell>
            <SectionSettingsForm
                :store="store"
                title-label="عنوان قسم خدمات حسب الطلب"
                title-placeholder="خدمات حسب الطلب"
                description-placeholder="خدمات مسعّرة حسب الوحدة مثل المتر والقطعة والساعة"
            />
        </Shell>
    </ManageLayout>
</template>
