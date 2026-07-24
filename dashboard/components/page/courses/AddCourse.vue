<script setup>
import AddContentModal from '../AddContentModal.vue';
import { useCoursesStore } from '../../../stores/courses.js';

const store = useCoursesStore();
</script>

<template>
    <AddContentModal
        :store="store"
        :create-fn="(title) => store.createCourse(title)"
        modal-name="add-course"
        :detail-path="item => `/manage/courses/detail/${item.uuid}`"
        label="اسم الدورة"
        placeholder="اكتب اسم الدورة"
        required-error="اسم الدورة مطلوب."
        fail-error="تعذر إنشاء الدورة."
    />
</template>
