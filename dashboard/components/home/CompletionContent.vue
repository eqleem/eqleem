<script setup>
import { contentTypes } from '../../data/page.js';
import { closeModal, openModal } from '../../lib/modal.js';

/** @type {Record<string, { modal: string }>} */
const addForms = {
    blog: { modal: 'add-blog-post' },
    store: { modal: 'add-store-product' },
    courses: { modal: 'add-course' },
    portfolio: { modal: 'add-portfolio-project' },
    pages: { modal: 'add-page' },
    forms: { modal: 'add-form' },
    services: { modal: 'add-service' },
    'digital-products': { modal: 'add-digital-product' },
    'digital-services': { modal: 'add-digital-service' },
    newsletter: { modal: 'add-newsletter' },
    menu: { modal: 'add-menu-item' },
    'unit-rental': { modal: 'add-unit' },
};

function openAddModal(slug) {
    const form = addForms[slug];

    if (!form) {
        return;
    }

    closeModal('home-step-content');
    openModal(form.modal);
}
</script>

<template>
    <div class="grid grid-cols-1 gap-2 p-4 sm:grid-cols-2" dir="rtl">
        <button
            v-for="type in contentTypes"
            :key="type.slug"
            type="button"
            class="flex items-center gap-3 rounded-xl border border-gray-100 px-3 py-3 text-start transition hover:border-gray-200 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50"
            :disabled="!addForms[type.slug]"
            @click="openAddModal(type.slug)"
        >
            <img :src="`/${type.icon}`" alt="" class="size-9 shrink-0 rounded-lg bg-gray-100 p-1.5">
            <span class="min-w-0">
                <span class="block text-sm font-medium text-gray-800">{{ type.name }}</span>
                <span class="block text-xs text-gray-400">{{ type.description }}</span>
            </span>
        </button>
    </div>
</template>
