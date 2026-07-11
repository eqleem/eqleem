<script setup>
import { computed, reactive, ref, watch } from 'vue';
import Input from '../../ui/Input.vue';
import Select from '../../ui/Select.vue';
import Toggle from '../../ui/Toggle.vue';
import Button from '../../ui/Button.vue';
import Icon from '../../ui/Icon.vue';
import { usePageStructureStore } from '../../../stores/pageStructure.js';
import { api, ApiError } from '../../../lib/api.js';
import { notifySuccess, notifyApiError } from '../../../lib/notify.js';

const props = defineProps({
    blockId: { type: Number, required: true },
    editor: { type: Object, required: true },
    contentType: { type: String, default: 'cta-link' },
    showSettings: { type: Boolean, default: false },
});

const emit = defineEmits(['saved', 'close', 'updated']);

const store = usePageStructureStore();
const links = ref([...(props.editor.links ?? [])]);
const linkTypeOptions = computed(() => props.editor.link_type_options ?? {});

const form = reactive({
    show_documents_warranties: Boolean(props.editor.show_documents_warranties ?? true),
    document_numbers: { ...(props.editor.document_numbers ?? {}) },
});

const linkModal = ref(false);
const editingLinkId = ref(null);
const linkForm = reactive({
    link_type: Object.keys(linkTypeOptions.value)[0] ?? 'external',
    label: '',
    url: '',
    icon: 'hugeicons:link-04',
    content_id: null,
    content_search: '',
    selected_content_title: '',
});
const contentResults = ref([]);
const showContentResults = ref(false);
const linkError = ref(null);
const linkSaving = ref(false);
const saving = ref(false);
const dragId = ref(null);

watch(() => props.editor, (value) => {
    links.value = [...(value.links ?? [])];
    form.show_documents_warranties = Boolean(value.show_documents_warranties ?? true);
    form.document_numbers = { ...(value.document_numbers ?? {}) };
}, { deep: true });

const needsContentPicker = computed(() => String(linkForm.link_type).startsWith('item:'));
const isExternal = computed(() => linkForm.link_type === 'external');

async function searchContent() {
    if (!needsContentPicker.value) {
        contentResults.value = [];
        return;
    }

    const params = new URLSearchParams();
    params.set('link_type', linkForm.link_type);
    if (linkForm.content_search.trim().length >= 2) {
        params.set('search', linkForm.content_search.trim());
    }

    try {
        const payload = await api(`/page/link-content?${params.toString()}`);
        contentResults.value = Array.isArray(payload?.data) ? payload.data : [];
        showContentResults.value = true;
    } catch {
        contentResults.value = [];
    }
}

function openAdd() {
    editingLinkId.value = null;
    Object.assign(linkForm, {
        link_type: Object.keys(linkTypeOptions.value).includes('external')
            ? 'external'
            : (Object.keys(linkTypeOptions.value)[0] ?? 'external'),
        label: '',
        url: '',
        icon: 'hugeicons:link-04',
        content_id: null,
        content_search: '',
        selected_content_title: '',
    });
    linkError.value = null;
    linkModal.value = true;
}

function openEdit(link) {
    editingLinkId.value = link.id;
    const data = link.data ?? {};
    const typeKey = data.link_type === 'external'
        ? 'external'
        : (data.link_type && data.content_type ? `${data.link_type}:${data.content_type}` : 'external');

    Object.assign(linkForm, {
        link_type: typeKey,
        label: data.label ?? link.label ?? '',
        url: data.url ?? '',
        icon: data.icon ?? 'hugeicons:link-04',
        content_id: data.content_id ?? null,
        content_search: link.title ?? '',
        selected_content_title: link.title ?? '',
    });
    linkError.value = null;
    linkModal.value = true;
}

function selectContent(item) {
    linkForm.content_id = item.id;
    linkForm.selected_content_title = item.title;
    linkForm.content_search = item.title;
    showContentResults.value = false;
}

async function saveLink() {
    linkError.value = null;
    linkSaving.value = true;

    try {
        const body = {
            link_type: linkForm.link_type,
            label: linkForm.label,
            url: linkForm.url || null,
            icon: linkForm.icon,
            content_id: linkForm.content_id,
        };

        const path = editingLinkId.value
            ? `/page/blocks/${props.blockId}/links/${editingLinkId.value}`
            : `/page/blocks/${props.blockId}/links`;

        await api(path, {
            method: editingLinkId.value ? 'PUT' : 'POST',
            body,
        });

        const refreshed = await store.fetchBlock(props.blockId);
        links.value = [...(refreshed?.editor?.links ?? [])];
        emit('updated', refreshed);
        notifySuccess('Saved');
        linkModal.value = false;
    } catch (error) {
        linkError.value = error instanceof ApiError ? error.message : 'تعذر حفظ الرابط.';
        notifyApiError(error, 'تعذر حفظ الرابط.');
    } finally {
        linkSaving.value = false;
    }
}

async function deleteLink(id) {
    if (!window.confirm('هل أنت متأكد من حذف هذا الرابط؟')) {
        return;
    }

    try {
        await api(`/page/blocks/${props.blockId}/links/${id}`, { method: 'DELETE' });
        links.value = links.value.filter((link) => link.id !== id);
        emit('updated', { editor: { ...props.editor, links: links.value } });
    } catch {
        // ignore
    }
}

async function onDrop(event, targetId) {
    event.preventDefault();
    const sourceId = dragId.value;
    dragId.value = null;
    if (!sourceId || sourceId === targetId) {
        return;
    }

    const ids = links.value.map((link) => link.id);
    const from = ids.indexOf(sourceId);
    const to = ids.indexOf(targetId);
    if (from === -1 || to === -1) {
        return;
    }

    ids.splice(from, 1);
    ids.splice(to, 0, sourceId);
    links.value = ids.map((id) => links.value.find((link) => link.id === id)).filter(Boolean);

    try {
        await api(`/page/blocks/${props.blockId}/links/reorder`, {
            method: 'PUT',
            body: { order: ids },
        });
    } catch {
        // ignore
    }
}

async function saveSettings() {
    if (!props.showSettings) {
        emit('close');
        return;
    }

    saving.value = true;

    try {
        const payload = await store.updateBlock(props.blockId, {
            show_documents_warranties: form.show_documents_warranties,
            document_numbers: form.document_numbers,
        });
        emit('saved', payload);
    } catch (error) {
        notifyApiError(error, 'تعذر الحفظ.');
    } finally {
        saving.value = false;
    }
}
</script>

<template>
    <div class="space-y-4 !p-4">
        <template v-if="showSettings">
            <Toggle v-model="form.show_documents_warranties" name="show_documents_warranties" label="إظهار الوثائق والضمانات" />
            <div v-if="form.show_documents_warranties" class="space-y-2">
                <Input
                    v-for="doc in (editor.business_documents ?? [])"
                    :key="doc.key"
                    v-model="form.document_numbers[doc.key]"
                    :name="`document_${doc.key}`"
                    :label="doc.label"
                />
            </div>
        </template>

        <div class="flex items-center justify-between gap-3">
            <p class="text-xs font-semibold text-gray-500">الروابط</p>
            <Button type="button" variant="secondary" label="إضافة رابط" class="w-auto" @click="openAdd">
                <template #icon><Icon name="plus" class="h-4 w-4" /></template>
            </Button>
        </div>

        <p v-if="!links.length" class="py-2 text-xs text-gray-400">لا توجد روابط بعد. أضف أول زر إجراء.</p>
        <ul v-else class="space-y-1.5">
            <li
                v-for="link in links"
                :key="link.id"
                class="group flex items-center gap-2 rounded-lg border border-gray-100 bg-white px-2 py-2 transition hover:border-gray-200"
                draggable="true"
                @dragstart="dragId = link.id"
                @dragover.prevent
                @drop="onDrop($event, link.id)"
            >
                <button type="button" class="cursor-grab rounded-md p-1 text-gray-300">
                    <Icon name="grip-vertical" class="h-4 w-4" />
                </button>
                <button type="button" class="flex min-w-0 flex-1 flex-col items-start text-start hover:text-primary-600" @click="openEdit(link)">
                    <span class="truncate text-sm font-medium text-gray-800">{{ link.label }}</span>
                    <span class="truncate text-xs text-gray-400">{{ link.type_label }} · {{ link.summary }}</span>
                </button>
                <button type="button" class="shrink-0 rounded-lg p-1 text-red-400/80 hover:bg-red-50 hover:text-red-500" @click="deleteLink(link.id)">
                    <Icon name="trash" class="h-4 w-4" />
                </button>
            </li>
        </ul>


        <div class="flex justify-end pt-2">
            <Button
                v-if="showSettings"
                type="button"
                label="حفظ"
                :loading="saving"
                @click="saveSettings"
            />
            <Button v-else type="button" label="تم" @click="$emit('close')" />
        </div>
    </div>

    <div v-if="linkModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-gray-800/75" @click="linkModal = false"></div>
        <div class="relative max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-xl bg-white shadow-xl">
            <div class="sticky top-0 z-10 flex items-center justify-between border-b border-gray-100 bg-white p-3 px-4">
                <p class="text-sm font-semibold text-gray-600">{{ editingLinkId ? 'تعديل رابط' : 'إضافة رابط' }}</p>
                <button type="button" class="rounded-md bg-gray-100 p-1 text-gray-400" @click="linkModal = false">
                    <Icon name="x" class="h-4 w-4" />
                </button>
            </div>
            <div class="space-y-3 p-4">
                <Select v-model="linkForm.link_type" name="link_type" label="نوع الرابط" :options="linkTypeOptions" @update:model-value="searchContent" />
                <Input v-if="isExternal || !needsContentPicker" v-model="linkForm.label" name="label" label="الاسم" />
                <Input v-if="isExternal" v-model="linkForm.url" name="url" label="الرابط" placeholder="https://..." dir="ltr" />
                <div v-if="needsContentPicker" class="space-y-2">
                    <Input
                        v-model="linkForm.content_search"
                        name="content_search"
                        label="بحث المحتوى"
                        @focus="searchContent"
                        @input="searchContent"
                    />
                    <ul v-if="showContentResults && contentResults.length" class="max-h-40 overflow-y-auto rounded-lg border border-gray-100">
                        <li v-for="item in contentResults" :key="item.id">
                            <button type="button" class="w-full px-3 py-2 text-start text-sm hover:bg-gray-50" @click="selectContent(item)">
                                {{ item.title }}
                            </button>
                        </li>
                    </ul>
                    <p v-if="linkForm.selected_content_title" class="text-xs text-gray-400">المحدد: {{ linkForm.selected_content_title }}</p>
                </div>
                <p v-if="linkError" class="text-sm text-red-500">{{ linkError }}</p>
            </div>
            <div class="sticky bottom-0 flex justify-end gap-2 border-t border-gray-100 bg-white p-3 px-4">
                <Button type="button" variant="ghost" label="إلغاء" @click="linkModal = false" />
                <Button type="button" :label="editingLinkId ? 'حفظ' : 'إضافة'" :loading="linkSaving" @click="saveLink" />
            </div>
        </div>
    </div>
</template>
