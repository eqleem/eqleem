<script setup>
import SharedCategoriesTable from '../SharedCategoriesTable.vue';
import { useStoreStore } from '../../../stores/store.js';

const store = useStoreStore();
</script>

<template>
    <SharedCategoriesTable
        :store="store"
        add-modal-name="add-store-category"
        edit-modal-name="edit-store-category"
        slug-info="يُستخدم في فلترة المنتجات عبر الرابط."
    />
</template>
