<script setup>
import { computed, onMounted } from 'vue';
import { storeToRefs } from 'pinia';
import { closeModal, openModal } from '../../lib/modal.js';
import { useContentTypesStore } from '../../stores/contentTypes.js';

const permanentContentTypes = new Set(['pages', 'forms']);

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
    'on-demand-services': { modal: 'add-on-demand-service' },
    newsletter: { modal: 'add-newsletter' },
    menu: { modal: 'add-menu-item' },
    'unit-rental': { modal: 'add-unit' },
};

const contentTypesStore = useContentTypesStore();
const { contentTypes, catalogContentEnabled } = storeToRefs(contentTypesStore);

const visibleContentTypes = computed(() => {
    const enabledSlugs = new Set([
        ...catalogContentEnabled.value,
        ...permanentContentTypes,
    ]);

    return contentTypes.value
        .filter((type) => enabledSlugs.has(type.slug) && Boolean(addForms[type.slug]))
        .sort((first, second) => (
            Number(permanentContentTypes.has(first.slug))
            - Number(permanentContentTypes.has(second.slug))
        ));
});

onMounted(() => {
    void Promise.all([
        contentTypesStore.fetchContentTypes({ force: true }),
        contentTypesStore.fetchCatalogSections({ force: true }),
    ]).catch(() => {});
});

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
            v-for="type in visibleContentTypes"
            :key="type.slug"
            type="button"
            class="flex items-center gap-3 rounded-xl border border-stone-100 px-3 py-3 text-start transition hover:border-stone-200 hover:bg-stone-50 disabled:cursor-not-allowed disabled:opacity-50"
            @click="openAddModal(type.slug)"
        >
            <img :src="`/${type.icon}`" alt="" class="size-9 shrink-0 rounded-lg bg-stone-100 p-1.5">
            <span class="min-w-0">
                <span class="block text-sm font-medium text-stone-800">{{ type.name }}</span>
                <span class="block text-xs text-stone-400">{{ type.description }}</span>
            </span>
        </button>
    </div>
</template>
