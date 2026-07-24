<script setup>
import AddContentModal from '../AddContentModal.vue';
import { usePagesStore } from '../../../stores/pages.js';
import { pageEditPath } from '../../../lib/pagePaths.js';

const store = usePagesStore();
</script>

<template>
    <AddContentModal
        :store="store"
        :create-fn="(title) => store.createPage({ title })"
        modal-name="add-page"
        :detail-path="pageEditPath"
        label="عنوان الصفحة"
        placeholder="اكتب عنوان الصفحة"
        required-error="عنوان الصفحة مطلوب."
        fail-error="تعذر إنشاء الصفحة."
    />
</template>
