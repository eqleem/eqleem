<script setup>
// The white card with a gray header (icon + title) used across the detail pages.
import Icon from './Icon.vue';

defineProps({
    title: { type: String, default: '' },
    icon: { type: String, default: null },
});
</script>

<template>
    <section class="overflow-hidden rounded-xl bg-white">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-stone-100 bg-stone-50 px-5 py-4">
            <div class="flex items-center gap-2">
                <Icon v-if="icon" :name="icon" class="h-5 w-5 text-primary-600" />
                <h2 class="text-sm font-semibold text-stone-700">{{ title }}</h2>
                <slot name="titleExtra" />
            </div>
            <slot name="action" />
        </div>
        <slot />
    </section>
</template>
