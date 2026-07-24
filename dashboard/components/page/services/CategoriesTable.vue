<script setup>
import SharedCategoriesTable from '../SharedCategoriesTable.vue';
import { useServicesStore } from '../../../stores/services.js';

const store = useServicesStore();
</script>

<template>
    <SharedCategoriesTable
        :store="store"
        add-modal-name="add-service-category"
        edit-modal-name="edit-service-category"
        slug-info="يُستخدم في فلترة الخدمةات عبر الرابط."
    />
</template>
