<script setup>
import AddContentModal from '../AddContentModal.vue';
import { usePortfolioStore } from '../../../stores/portfolio.js';

const store = usePortfolioStore();
</script>

<template>
    <AddContentModal
        :store="store"
        :create-fn="(title) => store.createProject(title)"
        modal-name="add-portfolio-project"
        :detail-path="item => `/manage/portfolio/detail/${item.uuid}`"
        label="عنوان المشروع"
        placeholder="اكتب عنوان المشروع"
        required-error="عنوان المشروع مطلوب."
        fail-error="تعذر إنشاء المشروع."
    />
</template>
