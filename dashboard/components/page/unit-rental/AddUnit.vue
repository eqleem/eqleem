<script setup>
import AddContentModal from '../AddContentModal.vue';
import { useUnitRentalStore } from '../../../stores/unit-rental.js';

const store = useUnitRentalStore();
</script>

<template>
    <AddContentModal
        :store="store"
        :create-fn="(title) => store.createUnit(title)"
        modal-name="add-unit"
        :detail-path="item => `/manage/unit-rental/detail/${item.uuid}`"
        label="اسم نوع الوحدة"
        placeholder="اكتب اسم نوع الوحدة"
        required-error="اسم نوع الوحدة مطلوب."
        fail-error="تعذر إنشاء نوع الوحدة."
    />
</template>
