<script setup>
import SharedCategoriesTable from '../SharedCategoriesTable.vue';
import { useBlogStore } from '../../../stores/blog.js';

const store = useBlogStore();
</script>

<template>
    <SharedCategoriesTable
        :store="store"
        add-modal-name="add-blog-category"
        edit-modal-name="edit-blog-category"
        slug-info="يُستخدم في فلترة التدوينات عبر الرابط."
    />
</template>
