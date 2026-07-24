<script setup>
import CategoryForm from '../CategoryForm.vue';
import { useCoursesStore } from '../../../stores/courses.js';

defineProps({
    categoryId: { type: [Number, String], default: null },
    defaultParentId: { type: [Number, String], default: null },
    modalName: { type: String, required: true },
});

const store = useCoursesStore();
</script>

<template>
    <CategoryForm
        :store="store"
        :category-id="categoryId"
        :default-parent-id="defaultParentId"
        :modal-name="modalName"
        slug-info="يُستخدم في فلترة الدورات عبر الرابط."
    />
</template>
