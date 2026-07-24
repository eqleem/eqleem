<script setup>
import ManageLayout from '../../../components/page/ManageLayout.vue';
import Shell from '../../../components/page/menu/Shell.vue';
import SectionSettingsForm from '../../../components/page/SectionSettingsForm.vue';
import { useMenuStore } from '../../../stores/menu.js';

const store = useMenuStore();
</script>

<template>
    <ManageLayout>
        <Shell>
            <SectionSettingsForm
                :store="store"
                title-label="عنوان قسم قائمة الطعام"
                title-placeholder="قائمة الطعام"
                description-placeholder="وجبات طازجة مع أحجام وإضافات متنوعة"
            />
        </Shell>
    </ManageLayout>
</template>
