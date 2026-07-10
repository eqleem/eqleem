<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import ManageLayout from '../../components/page/ManageLayout.vue';
import ContentShell from '../../components/page/ContentShell.vue';
import CategoriesTable from '../../components/page/CategoriesTable.vue';
import NotFound from '../NotFound.vue';
import { contentTypeBySlug } from '../../data/page.js';

const route = useRoute();
const contentType = computed(() => contentTypeBySlug(route.params.type));
</script>

<template>
    <ManageLayout v-if="contentType">
        <ContentShell :content-type="contentType">
            <CategoriesTable :key="contentType.slug" :content-type="contentType" />
        </ContentShell>
    </ManageLayout>
    <NotFound v-else />
</template>
