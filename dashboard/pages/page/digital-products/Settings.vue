<script setup>
import ManageLayout from '../../../components/page/ManageLayout.vue';
import Shell from '../../../components/page/digital-products/Shell.vue';
import SectionSettingsForm from '../../../components/page/SectionSettingsForm.vue';
import { useDigitalProductsStore } from '../../../stores/digital-products.js';

const store = useDigitalProductsStore();
</script>

<template>
    <ManageLayout>
        <Shell>
            <SectionSettingsForm
                :store="store"
                title-label="عنوان قسم المنتجات الرقمية"
                title-placeholder="المنتجات الرقمية"
                description-placeholder="منتجات رقمية قابلة للتحميل والوصول الفوري"
            />
        </Shell>
    </ManageLayout>
</template>
