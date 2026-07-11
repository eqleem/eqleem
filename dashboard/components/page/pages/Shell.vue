<script setup>
import MainBox from '../../ui/MainBox.vue';
import { usePagesStore } from '../../../stores/pages.js';

const store = usePagesStore();
</script>

<template>
    <MainBox :title="store.type.name" :subtitle="store.type.description">
        <template #icon>
            <img :src="`/${store.type.icon}`" class="h-7 w-7" alt="">
        </template>

        <slot />
    </MainBox>
</template>
