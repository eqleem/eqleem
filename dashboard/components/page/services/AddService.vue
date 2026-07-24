<script setup>
import AddContentModal from '../AddContentModal.vue';
import { useServicesStore } from '../../../stores/services.js';

const store = useServicesStore();
</script>

<template>
    <AddContentModal
        :store="store"
        :create-fn="(title) => store.createService(title)"
        modal-name="add-service"
        :detail-path="item => `/manage/services/detail/${item.uuid}`"
        label="اسم الخدمة"
        placeholder="اكتب اسم الخدمة"
        required-error="اسم الخدمة مطلوب."
        fail-error="تعذر إنشاء الخدمة."
    />
</template>
