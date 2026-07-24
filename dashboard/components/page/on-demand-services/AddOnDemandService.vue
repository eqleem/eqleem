<script setup>
import AddContentModal from '../AddContentModal.vue';
import { useOnDemandServicesStore } from '../../../stores/on-demand-services.js';

const store = useOnDemandServicesStore();
</script>

<template>
    <AddContentModal
        :store="store"
        :create-fn="(title) => store.createOnDemandService(title)"
        modal-name="add-on-demand-service"
        :detail-path="item => `/manage/on-demand-services/detail/${item.uuid}`"
        label="اسم الخدمة"
        placeholder="مثال : تركيب أرضيات، طباعة لافتات، تفصيل ستائر .. الخ"
        info="اكتب اسم الخدمة، يمكن إكمال البيانات ورفع الصور بعد الإضافة"
        required-error="اسم الخدمة مطلوب."
        fail-error="تعذر إنشاء الخدمة."
    />
</template>
