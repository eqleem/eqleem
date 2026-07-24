<script setup>
import MainBox from '../ui/MainBox.vue';

defineProps({
    type: { type: Object, required: true },
    section: { type: String, default: '' },
    subTabs: { type: Array, default: () => [] },
    nowrap: { type: Boolean, default: false },
});
</script>

<template>
    <MainBox :title="type.name" :subtitle="type.description">
        <template #icon>
            <img :src="`/${type.icon}`" class="h-7 w-7" alt="">
        </template>

        <div v-if="subTabs.length">
            <div class="flex border-b border-stone-200 px-px flex items-center overflow-x-auto no-scrollbar">
                <RouterLink
                    v-for="tab in subTabs"
                    :key="tab.key"
                    :to="tab.to"
                    class="inline-flex items-center gap-1.5 px-4 py-3 text-sm transition shrink-0"
                    :class="[
                        nowrap ? 'whitespace-nowrap' : '',
                        section === tab.key ? 'border-b-2 border-primary-500 text-stone-900' : 'text-stone-500 hover:text-stone-800',
                    ]"
                >
                    <iconify-icon :icon="tab.icon" class="text-base"></iconify-icon>
                    {{ tab.label }}
                </RouterLink>
            </div>

            <slot />
        </div>

        <slot v-else />
    </MainBox>
</template>
