<script setup>
import { computed, defineAsyncComponent } from 'vue';
import { useRoute } from 'vue-router';
import ManageLayout from '../../components/page/ManageLayout.vue';

const StructureTab = defineAsyncComponent(() => import('../../components/page/StructureTab.vue'));
const DesignTab = defineAsyncComponent(() => import('../../components/page/DesignTab.vue'));

// Ported from resources/views/admin/page/home.blade.php — the fixed structure/design tabs.
// Content types live on their own /manage/:type routes (see the nav).
const route = useRoute();
const active = computed(() => (route.query.tab === 'design' ? 'design' : 'structure'));
</script>

<template>
    <ManageLayout>
        <StructureTab v-if="active === 'structure'" />
        <DesignTab v-else />
    </ManageLayout>
</template>
