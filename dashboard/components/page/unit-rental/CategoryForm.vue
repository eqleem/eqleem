<script setup>
import CategoryForm from '../CategoryForm.vue';
import { useUnitRentalStore } from '../../../stores/unit-rental.js';

defineProps({
    categoryId: { type: [Number, String], default: null },
    defaultParentId: { type: [Number, String], default: null },
    modalName: { type: String, required: true },
});

const store = useUnitRentalStore();
</script>

<template>
    <CategoryForm
        :store="store"
        :category-id="categoryId"
        :default-parent-id="defaultParentId"
        :modal-name="modalName"
        slug-info="يُستخدم في فلترة الوحدات عبر الرابط."
    />
</template>
