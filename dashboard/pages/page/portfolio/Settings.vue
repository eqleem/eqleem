<script setup>
import ManageLayout from '../../../components/page/ManageLayout.vue';
import Shell from '../../../components/page/portfolio/Shell.vue';
import SectionSettingsForm from '../../../components/page/SectionSettingsForm.vue';
import { usePortfolioStore } from '../../../stores/portfolio.js';

const store = usePortfolioStore();
</script>

<template>
    <ManageLayout>
        <Shell>
            <SectionSettingsForm
                :store="store"
                title-label="عنوان قسم الأعمال"
                title-placeholder="معرض الأعمال"
                description-placeholder="عرض وإدارة مشاريعك وأعمالك السابقة"
            />
        </Shell>
    </ManageLayout>
</template>
