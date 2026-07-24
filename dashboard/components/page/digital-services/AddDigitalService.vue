<script setup>
import AddContentModal from '../AddContentModal.vue';
import { useDigitalServicesStore } from '../../../stores/digital-services.js';

const store = useDigitalServicesStore();
</script>

<template>
    <AddContentModal
        :store="store"
        :create-fn="(title) => store.createDigitalService(title)"
        modal-name="add-digital-service"
        :detail-path="item => `/manage/digital-services/detail/${item.uuid}`"
        label="اسم الخدمة"
        placeholder="اكتب اسم الخدمة"
        required-error="اسم الخدمة مطلوب."
        fail-error="تعذر إنشاء الخدمة."
    />
</template>
