<script setup>
import Container from '../../components/ui/Container.vue';

defineProps({
    title: { type: String, required: true },
});
</script>

<template>
    <Container :title="title" back-route="/settings">
        <div class="space-y-10">
            <slot />
        </div>
    </Container>
</template>
