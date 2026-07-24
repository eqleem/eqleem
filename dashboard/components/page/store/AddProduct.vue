<script setup>
import AddContentModal from '../AddContentModal.vue';
import { useStoreStore } from '../../../stores/store.js';

const store = useStoreStore();
</script>

<template>
    <AddContentModal
        :store="store"
        :create-fn="(title) => store.createProduct(title)"
        modal-name="add-store-product"
        :detail-path="item => `/manage/store/detail/${item.uuid}`"
        label="اسم المنتج"
        placeholder="مثال : تيشيرت النصر، عود كمبودي فاخر، ساعة Poma .. الخ"
        info="اكتب اسم المنتج، يمكن إكمال البيانات ورفع الصور بعد إضافة المنتج"
        required-error="اسم المنتج مطلوب."
        fail-error="تعذر إنشاء المنتج."
    />
</template>
