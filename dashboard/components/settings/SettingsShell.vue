<script setup>
import Container from '../../components/ui/Container.vue';

defineProps({
    title: { type: String, required: true },
    embedded: { type: Boolean, default: false },
});
</script>

<template>
    <Container v-if="!embedded" :title="title" back-route="/settings">
        <div class="space-y-10">
            <slot />
        </div>
    </Container>
    <div v-else class="space-y-10">
        <slot />
    </div>
</template>
