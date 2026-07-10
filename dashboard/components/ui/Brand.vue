<script setup>
// Port of resources/views/ui/brand.blade.php
defineProps({
    name: { type: String, default: null },
    logo: { type: String, default: null },
    alt: { type: String, default: '' },
    href: { type: String, default: '#' },
    subtitle: { type: String, default: null },
});
</script>

<template>
    <a :href="href" class="inline-flex items-center gap-2">
        <img v-if="logo" :src="logo" :alt="alt" class="size-5">
        <div>
            <span class="font-bold">{{ name }}</span>
            <p v-if="subtitle" class="text-xs opacity-60">{{ subtitle }}</p>
        </div>
    </a>
</template>
