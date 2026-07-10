<script setup>
import Container from '../ui/Container.vue';
import PageNav from './PageNav.vue';
</script>

<template>
    <Container>
        <div class="flex items-start gap-2 lg:gap-5">
            <PageNav />
            <div class="min-w-0 flex-1">
                <slot />
            </div>
        </div>
    </Container>
</template>
