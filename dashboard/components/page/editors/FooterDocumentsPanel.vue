<script setup>
import {
    computed, onBeforeUnmount, reactive, ref, watch,
} from 'vue';
import BrandMark from '../../ui/BrandMark.vue';
import { BrandMarkField } from '../../ui/asyncHeavy.js';
import Button from '../../ui/Button.vue';
import Icon from '../../ui/Icon.vue';
import Input from '../../ui/Input.vue';
import Select from '../../ui/Select.vue';
import { api, ApiError } from '../../../lib/api.js';
import { lockBodyScroll, unlockBodyScroll } from '../../../lib/bodyScrollLock.js';
import { notifyApiError, notifySuccess } from '../../../lib/notify.js';

const props = defineProps({
    blockId: { type: Number, required: true },
    editor: { type: Object, required: true },
});

const emit = defineEmits(['updated']);

const documents = ref([...(props.editor.documents ?? [])]);
const modalOpen = ref(false);
const editingId = ref(null);
const saving = ref(false);
const errors = ref({});
const dragId = ref(null);
const reorderBusyId = ref(null);
const brandMark = ref(null);
const form = reactive({
    type: 'vat',
    custom_label: '',
    value: '',
});

const typeOptions = computed(() => props.editor.document_type_options ?? {});
const modalTitle = computed(() => (editingId.value ? 'تعديل وثيقة' : 'إضافة وثيقة'));

watch(() => props.editor, (editor) => {
    documents.value = [...(editor.documents ?? [])];
}, { deep: true });

function openAdd() {
    editingId.value = null;
    form.type = Object.keys(typeOptions.value)[0] ?? 'vat';
    form.custom_label = '';
    form.value = '';
    brandMark.value = null;
    errors.value = {};
    modalOpen.value = true;
    lockBodyScroll();
}

function openEdit(document) {
    editingId.value = document.id;
    form.type = document.type;
    form.custom_label = document.custom_label ?? '';
    form.value = document.value ?? '';
    brandMark.value = document.brand_mark ? { ...document.brand_mark } : null;
    errors.value = {};
    modalOpen.value = true;
    lockBodyScroll();
}

function closeModal() {
    if (!modalOpen.value) {
        return;
    }

    modalOpen.value = false;
    editingId.value = null;
    errors.value = {};
    unlockBodyScroll();
}

async function saveDocument() {
    saving.value = true;
    errors.value = {};

    try {
        const body = new FormData();
        body.append('type', form.type);
        body.append('custom_label', form.custom_label);
        body.append('value', form.value);

        const mark = brandMark.value ?? {};

        if (mark.type === 'image' && mark.file) {
            body.append('logo', mark.file);
            body.append('brand_mark_type', 'image');
        } else if (mark.type === 'emoji' || mark.type === 'icon') {
            body.append('brand_mark_type', mark.type);
            body.append('brand_mark_value', mark.value ?? '');
            body.append('brand_mark_color', mark.color ?? '');
        } else if (mark.type === 'none') {
            body.append('brand_mark_type', 'none');
            body.append('remove_logo', '1');
        }

        const path = editingId.value
            ? `/page/blocks/${props.blockId}/footer-documents/${editingId.value}`
            : `/page/blocks/${props.blockId}/footer-documents`;
        const payload = await api(path, { method: 'POST', body });
        const editor = payload?.data ?? props.editor;

        documents.value = [...(editor.documents ?? [])];
        emit('updated', { editor });
        notifySuccess('Saved');
        closeModal();
    } catch (error) {
        if (error instanceof ApiError) {
            errors.value = error.errors ?? {};
        }

        notifyApiError(error, 'تعذر حفظ الوثيقة.');
    } finally {
        saving.value = false;
    }
}

async function deleteDocument(document) {
    if (!window.confirm('هل أنت متأكد من حذف هذه الوثيقة؟')) {
        return;
    }

    try {
        const payload = await api(`/page/blocks/${props.blockId}/footer-documents/${document.id}`, {
            method: 'DELETE',
        });
        const editor = payload?.data ?? props.editor;
        documents.value = [...(editor.documents ?? [])];
        emit('updated', { editor });
    } catch (error) {
        notifyApiError(error, 'تعذر حذف الوثيقة.');
    }
}

async function reorderDocuments(ids, activeId) {
    const previous = [...documents.value];
    documents.value = ids.map((id) => previous.find((document) => document.id === id)).filter(Boolean);
    reorderBusyId.value = activeId;

    try {
        const payload = await api(`/page/blocks/${props.blockId}/footer-documents/reorder`, {
            method: 'PUT',
            body: { order: ids },
        });
        const editor = payload?.data ?? props.editor;
        documents.value = [...(editor.documents ?? [])];
        emit('updated', { editor });
    } catch (error) {
        documents.value = previous;
        notifyApiError(error, 'تعذر إعادة ترتيب الوثائق.');
    } finally {
        reorderBusyId.value = null;
    }
}

async function onDrop(event, targetId) {
    event.preventDefault();
    const sourceId = dragId.value;
    dragId.value = null;

    if (!sourceId || sourceId === targetId || reorderBusyId.value !== null) {
        return;
    }

    const ids = documents.value.map((document) => document.id);
    const from = ids.indexOf(sourceId);
    const to = ids.indexOf(targetId);

    if (from === -1 || to === -1) {
        return;
    }

    ids.splice(from, 1);
    ids.splice(to, 0, sourceId);
    await reorderDocuments(ids, sourceId);
}

async function moveDocument(documentId, direction) {
    const ids = documents.value.map((document) => document.id);
    const from = ids.indexOf(documentId);
    const to = from + direction;

    if (from === -1 || to < 0 || to >= ids.length || reorderBusyId.value !== null) {
        return;
    }

    ids.splice(from, 1);
    ids.splice(to, 0, documentId);
    await reorderDocuments(ids, documentId);
}

onBeforeUnmount(() => {
    if (modalOpen.value) {
        modalOpen.value = false;
        unlockBodyScroll();
    }
});

defineExpose({ openAdd });
</script>

<template>
    <div class="relative min-h-20">
        <p
            v-if="!documents.length"
            class="pointer-events-none absolute inset-0 flex select-none items-center justify-center px-4 text-center text-xs text-stone-400"
        >
            لا توجد وثائق بعد. اضغط «أضف وثيقة» لإضافة أول وثيقة أو ضمان.
        </p>

        <ul v-else class="space-y-1.5 p-2">
            <li
                v-for="(document, index) in documents"
                :key="document.id"
                class="group flex items-center gap-2 rounded-lg border border-transparent bg-white px-2 py-2 transition hover:border-stone-200"
                @dragover.prevent
                @drop="onDrop($event, document.id)"
            >
                <button
                    type="button"
                    draggable="true"
                    class="hidden cursor-grab rounded-md p-1 text-stone-300 transition hover:bg-stone-100 hover:text-stone-500 active:cursor-grabbing sm:block"
                    aria-label="سحب لإعادة الترتيب"
                    @dragstart="dragId = document.id"
                    @dragend="dragId = null"
                >
                    <Icon name="grip-vertical" class="h-4 w-4" />
                </button>

                <div class="flex shrink-0 items-center sm:hidden">
                    <button
                        type="button"
                        class="rounded-md p-1 text-stone-400 transition hover:bg-stone-100 hover:text-primary-600 disabled:cursor-not-allowed disabled:opacity-25"
                        aria-label="نقل الوثيقة للأعلى"
                        :disabled="index === 0 || reorderBusyId !== null"
                        @click.stop="moveDocument(document.id, -1)"
                    >
                        <Icon name="arrow-up" class="h-4 w-4" />
                    </button>
                    <button
                        type="button"
                        class="rounded-md p-1 text-stone-400 transition hover:bg-stone-100 hover:text-primary-600 disabled:cursor-not-allowed disabled:opacity-25"
                        aria-label="نقل الوثيقة للأسفل"
                        :disabled="index === documents.length - 1 || reorderBusyId !== null"
                        @click.stop="moveDocument(document.id, 1)"
                    >
                        <Icon name="arrow-down" class="h-4 w-4" />
                    </button>
                </div>

                <button type="button" class="flex min-w-0 flex-1 items-center gap-2 text-start" @click="openEdit(document)">
                    <BrandMark
                        :mark="document.brand_mark"
                        :alt="document.label"
                        size-class="size-8 rounded-md bg-stone-50"
                        icon-class="text-xl"
                        img-class="object-contain"
                    />
                    <span class="min-w-0">
                        <span class="block truncate text-sm font-medium text-stone-800">{{ document.label }}</span>
                        <span class="block truncate text-xs text-stone-400" dir="ltr">{{ document.value }}</span>
                    </span>
                </button>

                <button
                    type="button"
                    class="pointer-events-none shrink-0 rounded-lg p-1 text-red-400/80 opacity-0 transition hover:bg-red-50 hover:text-red-500 group-hover:pointer-events-auto group-hover:opacity-100"
                    aria-label="حذف الوثيقة"
                    @click="deleteDocument(document)"
                >
                    <Icon name="trash" class="h-4 w-4" />
                </button>
            </li>
        </ul>
    </div>

    <Teleport to="body">
        <div v-if="modalOpen" class="relative z-50" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-stone-800/75" @click="closeModal" />
            <div class="fixed inset-0 overflow-y-auto overscroll-contain">
                <div class="flex min-h-full items-center justify-center p-4" @click.self="closeModal">
                    <div class="relative w-full max-w-lg overflow-visible rounded-xl bg-stone-50 shadow-xl">
                        <div class="flex items-center justify-between border-b border-stone-100 bg-white p-3 px-4">
                            <p class="text-sm font-semibold text-stone-600">{{ modalTitle }}</p>
                            <button type="button" class="rounded-md bg-stone-100 p-1 text-stone-400 hover:bg-stone-200" aria-label="إغلاق" @click="closeModal">
                                <Icon name="x" class="h-4 w-4" />
                            </button>
                        </div>

                        <div class="space-y-3 p-4">
                            <Select
                                v-model="form.type"
                                name="document_type"
                                label="نوع الوثيقة"
                                :options="typeOptions"
                                :error="errors.type?.[0]"
                            />
                            <Input
                                v-if="form.type === 'other'"
                                v-model="form.custom_label"
                                name="custom_label"
                                label="اسم الوثيقة"
                                :error="errors.custom_label?.[0]"
                            />
                            <Input
                                v-model="form.value"
                                name="document_value"
                                label="القيمة"
                                dir="ltr"
                                :error="errors.value?.[0]"
                            />
                            <BrandMarkField
                                v-model="brandMark"
                                name="document_mark"
                                label="الأيقونة أو الصورة"
                                shape="square"
                                :error="errors.logo?.[0] || errors.brand_mark_value?.[0]"
                            />
                        </div>

                        <div class="flex justify-end gap-2 border-t border-stone-100 bg-white p-3 px-4">
                            <Button type="button" variant="ghost" label="إلغاء" :disabled="saving" @click="closeModal" />
                            <Button type="button" label="حفظ" :loading="saving" @click="saveDocument" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>
