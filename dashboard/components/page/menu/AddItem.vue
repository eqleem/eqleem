<script setup>
import AddContentModal from '../AddContentModal.vue';
import { useMenuStore } from '../../../stores/menu.js';

const store = useMenuStore();
</script>

<template>
    <AddContentModal
        :store="store"
        :create-fn="(title) => store.createItem(title)"
        modal-name="add-menu-item"
        :detail-path="item => `/manage/menu/detail/${item.uuid}`"
        label="اسم الطبق"
        placeholder="اكتب اسم الطبق"
        required-error="اسم الطبق مطلوب."
        fail-error="تعذر إنشاء الطبق."
    />
</template>
