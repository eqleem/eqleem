<script setup>
import { computed } from 'vue';
import MainBox from '../../ui/MainBox.vue';
import { useFormsStore } from '../../../stores/forms.js';

const store = useFormsStore();
const formsType = computed(() => store.type);
</script>

<template>
    <MainBox :title="formsType.name" :subtitle="formsType.description">
        <template #icon>
            <img :src="`/${formsType.icon}`" class="h-7 w-7" alt="">
        </template>

        <slot />
    </MainBox>
</template>
