<script setup>
import SharedCategoriesTable from '../SharedCategoriesTable.vue';
import { usePortfolioStore } from '../../../stores/portfolio.js';

const store = usePortfolioStore();
</script>

<template>
    <SharedCategoriesTable
        :store="store"
        add-modal-name="add-portfolio-category"
        edit-modal-name="edit-portfolio-category"
        slug-info="يُستخدم في فلترة المشاريع عبر الرابط."
    />
</template>
