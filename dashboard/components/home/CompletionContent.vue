<script setup>
import { useRouter } from 'vue-router';
import { contentTypes } from '../../data/page.js';
import { closeModal } from '../../lib/modal.js';

const router = useRouter();

function openType(slug) {
    closeModal('home-step-content');
    router.push(`/manage/${slug}`);
}
</script>

<template>
    <div class="grid grid-cols-1 gap-2 p-4 sm:grid-cols-2" dir="rtl">
        <button
            v-for="type in contentTypes"
            :key="type.slug"
            type="button"
            class="flex items-center gap-3 rounded-xl border border-gray-100 px-3 py-3 text-start transition hover:border-gray-200 hover:bg-gray-50"
            @click="openType(type.slug)"
        >
            <img :src="`/${type.icon}`" alt="" class="size-9 shrink-0 rounded-lg bg-gray-100 p-1.5">
            <span class="min-w-0">
                <span class="block text-sm font-medium text-gray-800">{{ type.name }}</span>
                <span class="block text-xs text-gray-400">{{ type.description }}</span>
            </span>
        </button>
    </div>
</template>
