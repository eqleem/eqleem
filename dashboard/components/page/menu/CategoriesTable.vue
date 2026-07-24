<script setup>
import SharedCategoriesTable from '../SharedCategoriesTable.vue';
import { useMenuStore } from '../../../stores/menu.js';

const store = useMenuStore();
</script>

<template>
    <SharedCategoriesTable
        :store="store"
        add-modal-name="add-store-category"
        edit-modal-name="edit-store-category"
        slug-info="يُستخدم في فلترة الطبقات عبر الرابط."
    />
</template>
