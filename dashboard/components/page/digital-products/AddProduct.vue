<script setup>
import AddContentModal from '../AddContentModal.vue';
import { useDigitalProductsStore } from '../../../stores/digital-products.js';

const store = useDigitalProductsStore();
</script>

<template>
    <AddContentModal
        :store="store"
        :create-fn="(title) => store.createDigitalProduct(title)"
        modal-name="add-digital-product"
        :detail-path="item => `/manage/digital-products/detail/${item.uuid}`"
        label="اسم المنتج"
        placeholder="اكتب اسم المنتج"
        required-error="اسم المنتج مطلوب."
        fail-error="تعذر إنشاء المنتج."
    />
</template>
