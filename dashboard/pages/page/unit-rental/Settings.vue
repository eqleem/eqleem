<script setup>
import ManageLayout from '../../../components/page/ManageLayout.vue';
import Shell from '../../../components/page/unit-rental/Shell.vue';
import SectionSettingsForm from '../../../components/page/SectionSettingsForm.vue';
import { useUnitRentalStore } from '../../../stores/unit-rental.js';

const store = useUnitRentalStore();
</script>

<template>
    <ManageLayout>
        <Shell>
            <SectionSettingsForm
                :store="store"
                title-label="عنوان قسم تأجير الوحدات"
                title-placeholder="تأجير الوحدات"
                description-placeholder="احجز الوحدات المتاحة للتأجير بسهولة"
            />
        </Shell>
    </ManageLayout>
</template>
