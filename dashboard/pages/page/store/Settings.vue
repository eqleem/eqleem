<script setup>
import ManageLayout from '../../../components/page/ManageLayout.vue';
import Shell from '../../../components/page/store/Shell.vue';
import SectionSettingsForm from '../../../components/page/SectionSettingsForm.vue';
import { useStoreStore } from '../../../stores/store.js';

const store = useStoreStore();
</script>

<template>
    <ManageLayout>
        <Shell>
            <SectionSettingsForm
                :store="store"
                title-label="عنوان قسم المتجر"
                title-placeholder="المتجر"
                description-placeholder="تسوق منتجاتنا المميزة"
            />
        </Shell>
    </ManageLayout>
</template>
