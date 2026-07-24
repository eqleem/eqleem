<script setup>
import AddContentModal from '../AddContentModal.vue';
import { useBlogStore } from '../../../stores/blog.js';

const store = useBlogStore();
</script>

<template>
    <AddContentModal
        :store="store"
        :create-fn="(title) => store.createPost(title)"
        modal-name="add-blog-post"
        :detail-path="item => `/manage/blog/detail/${item.uuid}`"
        label="عنوان التدوينة"
        placeholder="اكتب عنوان التدوينة"
        required-error="عنوان التدوينة مطلوب."
        fail-error="تعذر إنشاء التدوينة."
    />
</template>
