<script setup>
import SharedCategoriesTable from '../SharedCategoriesTable.vue';
import { useCoursesStore } from '../../../stores/courses.js';

const store = useCoursesStore();
</script>

<template>
    <SharedCategoriesTable
        :store="store"
        add-modal-name="add-course-category"
        edit-modal-name="edit-course-category"
        slug-info="يُستخدم في فلترة الدورات عبر الرابط."
    />
</template>
