<script setup>
import SharedCategoriesTable from '../SharedCategoriesTable.vue';
import { useUnitRentalStore } from '../../../stores/unit-rental.js';

const store = useUnitRentalStore();
</script>

<template>
    <SharedCategoriesTable
        :store="store"
        add-modal-name="add-unit-category"
        edit-modal-name="edit-service-category"
        slug-info="يُستخدم في فلترة الوحدات عبر الرابط."
    />
</template>
