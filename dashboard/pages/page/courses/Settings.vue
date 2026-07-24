<script setup>
import ManageLayout from '../../../components/page/ManageLayout.vue';
import Shell from '../../../components/page/courses/Shell.vue';
import SectionSettingsForm from '../../../components/page/SectionSettingsForm.vue';
import { useCoursesStore } from '../../../stores/courses.js';

const store = useCoursesStore();
</script>

<template>
    <ManageLayout>
        <Shell>
            <SectionSettingsForm
                :store="store"
                title-label="عنوان قسم الدورات التدريبية"
                title-placeholder="الدورات التدريبية"
                description-placeholder="دورات تعليمية عملية مع دروس وفصول منظمة"
            />
        </Shell>
    </ManageLayout>
</template>
