<script setup>
import SharedCategoriesTable from '../SharedCategoriesTable.vue';
import { useDigitalProductsStore } from '../../../stores/digital-products.js';

const store = useDigitalProductsStore();
</script>

<template>
    <SharedCategoriesTable
        :store="store"
        add-modal-name="add-digital-product-category"
        edit-modal-name="edit-digital-product-category"
        slug-info="يُستخدم في فلترة المنتجات عبر الرابط."
    />
</template>
