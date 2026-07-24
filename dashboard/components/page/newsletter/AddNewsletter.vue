<script setup>
import AddContentModal from '../AddContentModal.vue';
import { useNewsletterStore } from '../../../stores/newsletter.js';

const store = useNewsletterStore();
</script>

<template>
    <AddContentModal
        :store="store"
        :create-fn="(title) => store.createIssue(title)"
        modal-name="add-newsletter"
        :detail-path="item => `/manage/newsletter/detail/${item.uuid}`"
        label="عنوان النشرة"
        placeholder="اكتب عنوان النشرة البريدية"
        required-error="عنوان النشرة مطلوب."
        fail-error="تعذر إنشاء النشرة."
    />
</template>
