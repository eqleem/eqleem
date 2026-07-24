<script setup>
import SharedCategoriesTable from '../SharedCategoriesTable.vue';
import { useDigitalServicesStore } from '../../../stores/digital-services.js';

const store = useDigitalServicesStore();
</script>

<template>
    <SharedCategoriesTable
        :store="store"
        add-modal-name="add-digital-service-category"
        edit-modal-name="edit-digital-service-category"
        slug-info="يُستخدم في فلترة الخدمةات عبر الرابط."
    />
</template>
