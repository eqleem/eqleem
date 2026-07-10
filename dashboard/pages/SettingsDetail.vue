<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import Container from '../components/ui/Container.vue';

// Mirrors admin/settings/detail.blade.php: <ui:container :title :backRoute>.
const route = useRoute();
const title = computed(() => route.query.name ?? route.params.slug);
</script>

<template>
    <Container :title="title" back-route="/settings">
        <div class="rounded-xl bg-white p-6 text-sm text-gray-400">لا توجد بيانات حقيقية هنا بعد.</div>
    </Container>
</template>
