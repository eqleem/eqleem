<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { storeToRefs } from 'pinia';
import Modal from '../ui/Modal.vue';
import Button from '../ui/Button.vue';
import { closeModal } from '../../lib/modal.js';
import { notifyApiSuccess } from '../../lib/notify.js';
import { useContentTypesStore } from '../../stores/contentTypes.js';

const MODAL_NAME = 'catalog-sections';

const store = useContentTypesStore();
const {
    catalogOptions,
    catalogLoading,
    catalogSaving,
    catalogError,
} = storeToRefs(store);

const selected = ref([]);
const localError = ref(null);

const error = computed(() => localError.value || catalogError.value);

watch(catalogOptions, (options) => {
    selected.value = options
        .filter((option) => option.enabled)
        .map((option) => option.slug);
}, { immediate: true });

function toggle(slug) {
    if (selected.value.includes(slug)) {
        selected.value = selected.value.filter((item) => item !== slug);
    } else {
        selected.value = [...selected.value, slug];
    }
}

async function loadSections() {
    localError.value = null;

    try {
        await store.fetchCatalogSections({ force: true });
    } catch {
        // surfaced via store
    }
}

function onOpenModal(event) {
    if (event.detail?.modal === MODAL_NAME) {
        loadSections();
    }
}

async function save() {
    localError.value = null;

    try {
        const payload = await store.saveCatalogSections(selected.value);
        notifyApiSuccess(payload, 'تم حفظ أقسام الكتالوج.');
        closeModal(MODAL_NAME);
    } catch (err) {
        localError.value = err?.message || 'تعذر حفظ أقسام الكتالوج.';
    }
}

onMounted(() => {
    window.addEventListener('openmodal', onOpenModal);
});

onBeforeUnmount(() => {
    window.removeEventListener('openmodal', onOpenModal);
});
</script>

<template>
    <Modal :title="'ايش تبيع؟'" size="2xl" :name="MODAL_NAME">
        <div class="space-y-4 p-4">
            <p class="text-sm text-stone-500">
               اختر الأنواع اللي تبيعها في نشاط، يمكنك اضافة وتعطيل أي نوع بأي وقت.
            </p>

            <div v-if="catalogLoading && !catalogOptions.length" class="py-8 text-center text-sm text-stone-400">
                جاري التحميل…
            </div>

            <div v-else class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                <button
                    v-for="option in catalogOptions"
                    :key="option.slug"
                    type="button"
                    class="flex items-start gap-3 rounded-xl border px-3 py-3 text-start transition"
                    :class="selected.includes(option.slug)
                        ? 'border-primary-300 bg-primary-50/70 ring-1 ring-primary-200'
                        : 'border-stone-100 bg-white hover:border-stone-200 hover:bg-stone-50'"
                    @click="toggle(option.slug)"
                >
                    <img :src="`/${option.icon}`" alt="" class="size-10 shrink-0 rounded-lg bg-white p-1.5 shadow-sm">
                    <span class="min-w-0 flex-1">
                        <span class="flex items-center justify-between gap-2">
                            <span class="block text-sm font-semibold text-stone-800">{{ option.name }}</span>
                            <span
                                class="inline-flex size-5 shrink-0 items-center justify-center rounded-full border"
                                :class="selected.includes(option.slug)
                                    ? 'border-primary-500 bg-primary-500 text-white'
                                    : 'border-stone-200 bg-white text-transparent'"
                            >
                                <iconify-icon icon="hugeicons:tick-02" class="text-sm"></iconify-icon>
                            </span>
                        </span>
                        <span class="mt-0.5 block text-xs leading-relaxed text-stone-400">{{ option.description }}</span>
                    </span>
                </button>
            </div>

            <p v-if="error" class="text-xs text-red-500">{{ error }}</p>
        </div>

        <template #footer>
            <div class="flex justify-end gap-2">
                <Button
                    label="إلغاء"
                    variant="secondary"
                    :disabled="catalogSaving"
                    @click="closeModal(MODAL_NAME)"
                />
                <Button
                    label="حفظ"
                    :loading="catalogSaving"
                    :disabled="catalogSaving"
                    @click="save"
                />
            </div>
        </template>
    </Modal>
</template>
