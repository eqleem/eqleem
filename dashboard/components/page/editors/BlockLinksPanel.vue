<script setup>
import { computed, onBeforeUnmount, reactive, ref, watch } from 'vue';
import Toggle from '../../ui/Toggle.vue';
import Button from '../../ui/Button.vue';
import Icon from '../../ui/Icon.vue';
import BlockLinkEditor from './BlockLinkEditor.vue';
import { usePageStructureStore } from '../../../stores/pageStructure.js';
import { api } from '../../../lib/api.js';
import { notifySuccess, notifyApiError } from '../../../lib/notify.js';
import { lockBodyScroll, unlockBodyScroll } from '../../../lib/bodyScrollLock.js';

const props = defineProps({
    blockId: { type: Number, required: true },
    editor: { type: Object, required: true },
    contentType: { type: String, default: 'cta-link' },
    showSettings: { type: Boolean, default: false },
    embedded: { type: Boolean, default: false },
    showPrimaryBadge: { type: Boolean, default: false },
});

const emit = defineEmits(['saved', 'close', 'updated']);

const store = usePageStructureStore();
const links = ref([...(props.editor.links ?? [])]);

const form = reactive({
    show_documents_warranties: Boolean(props.editor.show_documents_warranties ?? true),
});

const linkModal = ref(false);
const editingLinkId = ref(null);
const linkEditor = ref(null);
const linkEditorKey = ref(0);
const saving = ref(false);
const dragId = ref(null);
const reorderBusyId = ref(null);

watch(() => props.editor, (value) => {
    links.value = [...(value.links ?? [])];
    form.show_documents_warranties = Boolean(value.show_documents_warranties ?? true);
}, { deep: true });

function linkTypeFromData(data = {}) {
    if (data.link_type === 'external' || data.link_type === 'booking') {
        return data.link_type;
    }

    if (data.link_type && data.content_type) {
        return `${data.link_type}:${data.content_type}`;
    }

    return '';
}

function buildLinkEditor(link = null) {
    const data = link?.data ?? {};
    const brandMark = link?.brand_mark
        ?? (isObjectMark(data.brand_mark) ? data.brand_mark : null);
    const icon = data.icon || link?.icon || '';

    return {
        type: 'block-link',
        title: data.label ?? link?.label ?? '',
        description: '',
        url: data.url ?? '',
        link_type: link ? linkTypeFromData(data) : '',
        content_id: data.content_id ?? null,
        selected_content_title: link?.title ?? '',
        icon,
        brand_mark: brandMark ?? (icon
            ? { type: 'icon', value: icon, color: '', url: null }
            : null),
        branch_ids: [...(data.branch_ids ?? [])].map((id) => Number(id)),
        calendar_ids: [...(data.calendar_ids ?? [])].map((id) => Number(id)),
        allow_client_choice: data.allow_client_choice ?? true,
        duration_minutes: Number(data.duration_minutes ?? 30) || 30,
        link_type_picker_options: props.editor.link_type_picker_options ?? [],
        booking_targets: props.editor.booking_targets ?? { branches: [], calendars: [] },
    };
}

function isObjectMark(value) {
    return Boolean(value && typeof value === 'object' && value.type);
}

function openAdd() {
    editingLinkId.value = null;
    linkEditor.value = buildLinkEditor();
    linkEditorKey.value += 1;
    linkModal.value = true;
    lockBodyScroll();
}

function openEdit(link) {
    editingLinkId.value = link.id;
    linkEditor.value = buildLinkEditor(link);
    linkEditorKey.value += 1;
    linkModal.value = true;
    lockBodyScroll();
}

function closeLinkModal() {
    if (!linkModal.value) {
        return;
    }

    linkModal.value = false;
    editingLinkId.value = null;
    linkEditor.value = null;
    unlockBodyScroll();
}

async function refreshLinks() {
    const payload = await api(`/page/blocks/${props.blockId}`);
    const editor = payload?.data?.editor ?? null;
    links.value = [...(editor?.links ?? [])];

    return editor;
}

async function onLinkSaved() {
    try {
        const editor = await refreshLinks();
        emit('updated', { editor });
        notifySuccess('Saved');
        closeLinkModal();
    } catch (error) {
        notifyApiError(error, 'تعذر تحديث الروابط.');
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

async function reorderLinks(ids, activeId) {
    const previous = [...links.value];
    links.value = ids.map((id) => previous.find((link) => link.id === id)).filter(Boolean);
    reorderBusyId.value = activeId;

    try {
        await api(`/page/blocks/${props.blockId}/links/reorder`, {
            method: 'PUT',
            body: { order: ids },
        });
        emit('updated', { editor: { ...props.editor, links: links.value } });
    } catch {
        links.value = previous;
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

    const ids = links.value.map((link) => link.id);
    const from = ids.indexOf(sourceId);
    const to = ids.indexOf(targetId);

    if (from === -1 || to === -1) {
        return;
    }

    ids.splice(from, 1);
    ids.splice(to, 0, sourceId);
    await reorderLinks(ids, sourceId);
}

async function moveLink(linkId, direction) {
    const ids = links.value.map((link) => link.id);
    const from = ids.indexOf(linkId);
    const to = from + direction;

    if (from === -1 || to < 0 || to >= ids.length || reorderBusyId.value !== null) {
        return;
    }

    ids.splice(from, 1);
    ids.splice(to, 0, linkId);
    await reorderLinks(ids, linkId);
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
        });
        emit('saved', payload);
    } catch (error) {
        notifyApiError(error, 'تعذر الحفظ.');
    } finally {
        saving.value = false;
    }
}

const modalTitle = computed(() => (editingLinkId.value ? 'تعديل رابط' : 'إضافة رابط'));
const emptyLinksMessage = computed(() => {
    if (props.contentType === 'footer-link') {
        return props.embedded
            ? 'لا توجد روابط بعد. اضغط «أضف رابط» لإضافة أول رابط في تذييل الصفحة.'
            : 'لا توجد روابط بعد. أضف أول رابط في تذييل الصفحة.';
    }

    return props.embedded
        ? 'لا توجد أزرار بعد. اضغط «أضف زر» لإضافة أول زر إجراء.'
        : 'لا توجد روابط بعد. أضف أول زر إجراء.';
});

onBeforeUnmount(() => {
    if (linkModal.value) {
        linkModal.value = false;
        unlockBodyScroll();
    }
});

defineExpose({ openAdd });
</script>

<template>
    <div :class="embedded ? 'relative min-h-20' : 'space-y-4 !p-4'">
        <template v-if="showSettings">
            <Toggle v-model="form.show_documents_warranties" name="show_documents_warranties" label="إظهار الوثائق والضمانات" />
        </template>

        <div v-if="!embedded" class="flex items-center justify-between gap-3">
            <p class="text-xs font-semibold text-stone-500">الروابط</p>
            <Button type="button" variant="secondary" label="إضافة رابط" class="w-auto" @click="openAdd">
                <template #icon><Icon name="plus" class="h-4 w-4" /></template>
            </Button>
        </div>

        <p
            v-if="!links.length"
            :class="embedded
                ? 'pointer-events-none absolute inset-0 flex select-none items-center justify-center px-4 text-center text-xs text-stone-400'
                : 'py-2 text-xs text-stone-400'"
        >
            {{ emptyLinksMessage }}
        </p>
        <ul v-else :class="embedded ? 'space-y-1.5 p-2' : 'space-y-1.5'">
            <li
                v-for="(link, index) in links"
                :key="link.id"
                class="group flex items-center gap-2 rounded-lg border border-transparent bg-white px-2 py-2 transition hover:border-stone-200"
                @dragover.prevent
                @drop="onDrop($event, link.id)"
            >
                <button
                    type="button"
                    draggable="true"
                    class="hidden cursor-grab rounded-md p-1 text-stone-300 transition hover:bg-stone-100 hover:text-stone-500 active:cursor-grabbing sm:block"
                    aria-label="سحب لإعادة الترتيب"
                    @dragstart="dragId = link.id"
                    @dragend="dragId = null"
                >
                    <Icon name="grip-vertical" class="h-4 w-4" />
                </button>
                <div class="flex shrink-0 items-center sm:hidden">
                    <button
                        type="button"
                        class="rounded-md p-1 text-stone-400 transition hover:bg-stone-100 hover:text-primary-600 disabled:cursor-not-allowed disabled:opacity-25"
                        aria-label="نقل الرابط للأعلى"
                        :disabled="index === 0 || reorderBusyId !== null"
                        @click.stop="moveLink(link.id, -1)"
                    >
                        <Icon name="arrow-up" class="h-4 w-4" />
                    </button>
                    <button
                        type="button"
                        class="rounded-md p-1 text-stone-400 transition hover:bg-stone-100 hover:text-primary-600 disabled:cursor-not-allowed disabled:opacity-25"
                        aria-label="نقل الرابط للأسفل"
                        :disabled="index === links.length - 1 || reorderBusyId !== null"
                        @click.stop="moveLink(link.id, 1)"
                    >
                        <Icon name="arrow-down" class="h-4 w-4" />
                    </button>
                </div>
                <button type="button" class="flex min-w-0 flex-1 cursor-pointer flex-col items-start text-start hover:text-primary-600" @click="openEdit(link)">
                    <span class="flex min-w-0 max-w-full items-center gap-2">
                        <span class="truncate text-sm font-medium text-stone-800">{{ link.label }}</span>
                        <span
                            v-if="showPrimaryBadge && index === 0"
                            class="shrink-0 rounded-full bg-primary-100 px-2 py-0.5 text-[10px] font-semibold text-primary-700"
                        >
                            الزر الرئيسي
                        </span>
                    </span>
                    <span class="truncate text-xs text-stone-400">{{ link.type_label }} · {{ link.summary }}</span>
                </button>
                <button
                    type="button"
                    class="pointer-events-none shrink-0 cursor-pointer rounded-lg p-1 text-red-400/80 opacity-0 transition hover:bg-red-50 hover:text-red-500 group-hover:pointer-events-auto group-hover:opacity-100"
                    @click="deleteLink(link.id)"
                >
                    <Icon name="trash" class="h-4 w-4" />
                </button>
            </li>
        </ul>

        <div v-if="!embedded" class="flex justify-end pt-2">
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

    <Teleport to="body">
        <div
            v-if="linkModal && linkEditor"
            class="relative z-50"
            role="dialog"
            aria-modal="true"
        >
            <div class="fixed inset-0 bg-stone-800/75" @click="closeLinkModal" />

            <div class="fixed inset-0 overflow-y-auto overscroll-contain">
                <div class="flex min-h-full items-center justify-center p-4" @click.self="closeLinkModal">
                    <div class="relative w-full max-w-lg overflow-hidden rounded-xl bg-white shadow-xl">
                        <div class="flex items-center justify-between border-b border-stone-100 bg-white p-3 px-4">
                            <p class="text-sm font-semibold text-stone-600">{{ modalTitle }}</p>
                            <button
                                type="button"
                                class="cursor-pointer rounded-md bg-stone-100 p-1 text-stone-400 transition hover:bg-stone-200 hover:text-stone-600"
                                aria-label="إغلاق"
                                @click="closeLinkModal"
                            >
                                <Icon name="x" class="h-4 w-4" />
                            </button>
                        </div>

                        <BlockLinkEditor
                            :key="linkEditorKey"
                            :block-id="blockId"
                            :link-id="editingLinkId"
                            :editor="linkEditor"
                            mode="nested-link"
                            :show-description="false"
                            @saved="onLinkSaved"
                            @close="closeLinkModal"
                        />
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>
