<script setup>
import ManageLayout from '../../../components/page/ManageLayout.vue';
import Shell from '../../../components/page/blog/Shell.vue';
import SectionSettingsForm from '../../../components/page/SectionSettingsForm.vue';
import { useBlogStore } from '../../../stores/blog.js';

const store = useBlogStore();
</script>

<template>
    <ManageLayout>
        <Shell>
            <SectionSettingsForm
                :store="store"
                title-label="عنوان قسم المدونة"
                title-placeholder="المدونة"
                description-placeholder="عرض وإدارة تدويناتك ومقالاتك"
            />
        </Shell>
    </ManageLayout>
</template>
