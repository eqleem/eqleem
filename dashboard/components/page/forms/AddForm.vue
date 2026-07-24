<script setup>
import AddContentModal from '../AddContentModal.vue';
import { useFormsStore } from '../../../stores/forms.js';

const store = useFormsStore();
</script>

<template>
    <AddContentModal
        :store="store"
        :create-fn="(title) => store.createForm(title)"
        modal-name="add-form"
        :detail-path="item => `/manage/forms/detail/${item.uuid}`"
        label="اسم النموذج"
        placeholder="اكتب اسم النموذج"
        required-error="اسم النموذج مطلوب."
        fail-error="تعذر إنشاء النموذج."
    />
</template>
