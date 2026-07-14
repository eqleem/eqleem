<script setup>
import { onMounted, onBeforeUnmount, ref } from 'vue';
import { storeToRefs } from 'pinia';
import MainBox from '../ui/MainBox.vue';
import Button from '../ui/Button.vue';
import BrandMark from '../ui/BrandMark.vue';
import Icon from '../ui/Icon.vue';
import Modal from '../ui/Modal.vue';
import Switch from '../settings/Switch.vue';
import BlockEditor from './editors/BlockEditor.vue';
import BlockEditorSkeleton from './editors/BlockEditorSkeleton.vue';
import BlockLinksPanel from './editors/BlockLinksPanel.vue';
import FloatLinksPanel from './editors/FloatLinksPanel.vue';
import { openModal, closeModal } from '../../lib/modal.js';
import { notifyApiSuccess } from '../../lib/notify.js';
import { usePageStructureStore } from '../../stores/pageStructure.js';

function openCatalogSections() {
    openModal('catalog-sections');
}

const store = usePageStructureStore();
const {
    topBlocks,
    ctaBlock,
    userBlocks,
    bottomBlocks,
    floatLinksBlock,
    loading,
    error,
    saving,
    editing,
    editingLoading,
    editingError,
} = storeToRefs(store);

const dragId = ref(null);
const busyId = ref(null);
const editTitle = ref('إعدادات البلوك');
const ctaLinksPanel = ref(null);
const floatLinksPanel = ref(null);

function onEditModalClosed(event) {
    if (event.detail?.modal === 'edit-block') {
        store.clearEditing();
    }
}

onMounted(() => {
    store.fetchStructure();
    window.addEventListener('closemodal', onEditModalClosed);
});

onBeforeUnmount(() => {
    window.removeEventListener('closemodal', onEditModalClosed);
});

function openAddCtaLink() {
    ctaLinksPanel.value?.openAdd?.();
}

function openFloatLinksPosition() {
    floatLinksPanel.value?.openPosition?.();
}

function onCtaUpdated(payload) {
    if (payload?.editor) {
        store.setCtaEditor(payload.editor);
    }
}

function onFloatLinksUpdated(payload) {
    if (payload?.editor) {
        store.setFloatLinksEditor(payload.editor);
    }
}

function addLinkBlock() {
    editTitle.value = 'إضافة رابط';
    store.beginCreateBlockLink();
    openModal('edit-block');
}

async function openEdit(id, title = null) {
    editTitle.value = title || 'إعدادات البلوك';
    openModal('edit-block');

    try {
        const payload = await store.fetchBlock(id);
        editTitle.value = payload?.block?.title || title || 'إعدادات البلوك';
    } catch {
        // error surfaced via store
    }
}

function onCloseEdit() {
    closeModal('edit-block');
    store.clearEditing();
}

async function onSaved(payload) {
    if (payload?.block?.title) {
        editTitle.value = payload.block.title;
    }

    closeModal('edit-block');
    store.clearEditing();
    notifyApiSuccess(payload, 'Saved');
}

async function toggleActive(block) {
    busyId.value = block.id;

    try {
        await store.toggleActive(block.id, !block.active);
    } catch {
        // error surfaced via store
    } finally {
        busyId.value = null;
    }
}

async function removeBlock(block) {
    if (!window.confirm('هل أنت متأكد من حذف هذا البلوك؟')) {
        return;
    }

    busyId.value = block.id;

    try {
        await store.deleteBlock(block.id);
    } catch {
        // error surfaced via store
    } finally {
        busyId.value = null;
    }
}

function onDragStart(event, id) {
    dragId.value = id;
    event.dataTransfer.effectAllowed = 'move';
    event.dataTransfer.setData('text/plain', String(id));
}

function onDragOver(event) {
    event.preventDefault();
    event.dataTransfer.dropEffect = 'move';
}

async function onDrop(event, targetId) {
    event.preventDefault();

    const sourceId = dragId.value ?? Number(event.dataTransfer.getData('text/plain'));
    dragId.value = null;

    if (!sourceId || sourceId === targetId) {
        return;
    }

    const ids = userBlocks.value.map((block) => block.id);
    const from = ids.indexOf(sourceId);
    const to = ids.indexOf(targetId);

    if (from === -1 || to === -1) {
        return;
    }

    ids.splice(from, 1);
    ids.splice(to, 0, sourceId);

    try {
        await store.reorderBlocks(ids);
    } catch {
        // error surfaced via store
    }
}

function contentManageTo(block) {
    const url = block.content_manage_url;

    if (!url) {
        return null;
    }

    // API returns /dashboard/manage/...; Vue router base is already /dashboard.
    return url.replace(/^\/dashboard/, '') || null;
}

function contentManageLabel(block) {
    return block.content_manage_label || 'إدارة المحتوى';
}
</script>

<template>
    <MainBox title="هيكل الصفحة" subtitle="إدارة وترتيب بلوكات الصفحة الرئيسية.">
        <template #icon>
            <img :src="'/assets/icons/tabler/puzzle-2.svg'" class="h-7 w-7" alt="">
        </template>

        <div v-if="loading && !topBlocks.length && !userBlocks.length" class="px-4 py-6 flex items-center justify-center"><LoadingSpinner size="sm" /></div>
        <p v-else-if="error" class="px-4 pt-3 text-sm text-red-500">{{ error }}</p>

        <div v-else class="space-y-4 p-4">
            <!-- <div class="flex items-center justify-between gap-3 rounded-xl border border-stone-200 bg-white px-3 py-2.5">
                <button
                    type="button"
                    class="min-w-0 flex-1 text-start transition hover:text-primary-600"
                    @click="openCatalogSections"
                >
                    <span class="block text-sm font-medium text-stone-800">الكتالوج</span>
                    <span class="mt-0.5 block text-xs text-stone-400">ايش تبيع؟ — اختيار الأقسام المفعّلة</span>
                </button>
                <button
                    type="button"
                    class="shrink-0 rounded-lg p-1.5 text-stone-400 transition hover:bg-stone-100 hover:text-primary-600"
                    aria-label="إعدادات الكتالوج"
                    @click="openCatalogSections"
                >
                    <Icon name="settings" class="h-5 w-5" />
                </button>
            </div> -->

            <div v-if="topBlocks.length" class="overflow-hidden rounded-xl border border-stone-200 bg-stone-50/80">
                <ul class="space-y-1.5 p-2">
                    <li
                        v-for="block in topBlocks"
                        :key="block.id"
                        class="flex items-center gap-2 rounded-lg border border-transparent bg-white px-2 py-2"
                    >
                        <div class="rounded-md p-1 text-stone-200">
                            <Icon name="lock" class="h-4 w-4" />
                        </div>

                        <button
                            v-if="block.editable"
                            type="button"
                            class="flex min-w-0 flex-1 cursor-pointer items-center gap-2 text-start transition hover:text-primary-600"
                            @click="openEdit(block.id, block.title)"
                        >
                            <img :src="block.icon_url" alt="" class="h-6 w-6 shrink-0 rounded-md bg-stone-100 p-1">
                            <span class="truncate text-sm font-medium text-stone-800">{{ block.title }}</span>
                        </button>
                        <div v-else class="flex min-w-0 flex-1 items-center gap-2">
                            <img :src="block.icon_url" alt="" class="h-6 w-6 shrink-0 rounded-md bg-stone-100 p-1">
                            <span class="truncate text-sm font-medium text-stone-800">{{ block.title }}</span>
                        </div>

                        <button
                            v-if="block.editable"
                            type="button"
                            class="cursor-pointer rounded-lg p-1 text-stone-400 transition hover:bg-stone-100 hover:text-primary-600"
                            aria-label="خيارات البلوك"
                            @click="openEdit(block.id, block.title)"
                        >
                            <Icon name="settings" class="h-5 w-5" />
                        </button>
                    </li>
                </ul>
            </div>

            <div v-if="ctaBlock?.editor" class="overflow-hidden rounded-xl border border-stone-200 bg-stone-50/80">
                <div class="flex items-center justify-between gap-3 border-b border-dotted border-stone-200 px-3 py-2.5">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-stone-700">أزرار الإجراء</p>
                        <p class="text-xs text-stone-400">هدف الصفحة، مالذي تريد أن يقوم العميل به، أنشئ اهم رابط أو رابطين</p>
                    </div>
                    <Button label="إضافة رابط" class="shrink-0" @click="openAddCtaLink">
                        <template #icon><Icon name="plus" class="h-4 w-4" /></template>
                    </Button>
                </div>

                <BlockLinksPanel
                    ref="ctaLinksPanel"
                    embedded
                    :block-id="ctaBlock.id"
                    :editor="ctaBlock.editor"
                    @updated="onCtaUpdated"
                />
            </div>

            <div class="overflow-hidden rounded-xl border border-stone-200 bg-stone-50/80">
                <div class="flex items-center justify-between gap-3 border-b border-dotted border-stone-200 px-3 py-2.5">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-stone-700">بلوكات الصفحة</p>
                        <p class="text-xs text-stone-400">البلوكات التي تضيفها تظهر هنا بين الهيدر والفوتر</p>
                    </div>
                    <Button label="إضافة رابط" class="shrink-0" :disabled="saving" @click="addLinkBlock">
                        <template #icon><Icon name="plus" class="h-4 w-4" /></template>
                    </Button>
                </div>

                <div class="relative min-h-20">
                    <ul class="space-y-1.5 p-2">
                        <li
                            v-for="block in userBlocks"
                            :key="block.id"
                            class="group flex items-center lg:gap-2 gap-1 rounded-lg border border-transparent bg-white px-2 py-2 transition hover:border-stone-200"
                            :class="{ 'opacity-50': !block.active }"
                            @dragover="onDragOver"
                            @drop="onDrop($event, block.id)"
                        >
                            <div
                                draggable="true"
                                class="cursor-grab rounded-md p-1 text-stone-300 transition hover:bg-stone-100 hover:text-stone-500 active:cursor-grabbing"
                                role="button"
                                tabindex="0"
                                aria-label="سحب لإعادة الترتيب"
                                @dragstart="onDragStart($event, block.id)"
                                @dragend="dragId = null"
                            >
                                <Icon name="grip-vertical" class="h-4 w-4" />
                            </div>

                            <button
                                type="button"
                                class="flex min-w-0 flex-1 cursor-pointer items-center gap-2 text-start transition hover:text-primary-600"
                                @click.stop="openEdit(block.id, block.title)"
                            >
                                <div class="hidden shrink-0 sm:block">
                                    <BrandMark
                                        :mark="block.brand_mark"
                                        :url="block.icon_url"
                                        :fallback="block.icon_url"
                                        :alt="block.title"
                                        size-class="h-6 w-6 rounded-md bg-stone-100 p-1"
                                        icon-class="text-base leading-none"
                                        img-class="object-contain"
                                    />
                                </div>
                                <span class="min-w-0 truncate text-sm font-medium text-stone-800">
                                    {{ block.title }}
                                </span>
                            </button>

                            <button
                                type="button"
                                class="pointer-events-none shrink-0 cursor-pointer rounded-lg p-1 text-red-400/80 opacity-0 transition hover:bg-red-50 hover:text-red-500 group-hover:pointer-events-auto group-hover:opacity-100"
                                aria-label="حذف البلوك"
                                :disabled="busyId === block.id"
                                @click.stop="removeBlock(block)"
                            >
                                <Icon name="trash" class="h-4 w-4" />
                            </button>

                            <RouterLink
                                v-if="contentManageTo(block)"
                                :to="contentManageTo(block)"
                                class="hidden shrink-0 cursor-pointer rounded-lg px-2 py-1 text-xs font-medium text-primary-600 transition hover:bg-primary-50 sm:inline-flex"
                                @click.stop
                            >
                                {{ contentManageLabel(block) }}
                            </RouterLink>

                            <button
                                v-if="block.editable"
                                type="button"
                                class="cursor-pointer rounded-lg p-1 text-stone-400 transition hover:bg-stone-100 hover:text-primary-600"
                                aria-label="خيارات البلوك"
                                @click="openEdit(block.id, block.title)"
                            >
                                <Icon name="settings" class="h-5 w-5" />
                            </button>

                            <Switch
                                :model-value="block.active"
                                :label="block.active ? 'تعطيل البلوك' : 'تفعيل البلوك'"
                                :disabled="busyId === block.id"
                                @update:model-value="toggleActive(block)"
                            />
                        </li>
                    </ul>

                    <p
                        v-if="!userBlocks.length"
                        class="pointer-events-none absolute inset-0 flex select-none items-center justify-center px-4 text-center text-xs text-stone-400"
                    >
                        لا توجد روابط بعد. اضغط «إضافة رابط» لإضافة أول رابط في هذا القسم.
                    </p>
                </div>
            </div>

            <div v-if="bottomBlocks.length" class="overflow-hidden rounded-xl border border-stone-200 bg-stone-50/80">
                <ul class="space-y-1.5 p-2">
                    <li
                        v-for="block in bottomBlocks"
                        :key="block.id"
                        class="flex items-center gap-2 rounded-lg border border-transparent bg-white px-2 py-2"
                    >
                        <div class="rounded-md p-1 text-stone-200">
                            <Icon name="lock" class="h-4 w-4" />
                        </div>

                        <button
                            v-if="block.editable"
                            type="button"
                            class="flex min-w-0 flex-1 cursor-pointer items-center gap-2 text-start transition hover:text-primary-600"
                            @click="openEdit(block.id, block.title)"
                        >
                            <img :src="block.icon_url" alt="" class="h-6 w-6 shrink-0 rounded-md bg-stone-100 p-1">
                            <span class="truncate text-sm font-medium text-stone-800">{{ block.title }}</span>
                        </button>
                        <div v-else class="flex min-w-0 flex-1 items-center gap-2">
                            <img :src="block.icon_url" alt="" class="h-6 w-6 shrink-0 rounded-md bg-stone-100 p-1">
                            <span class="truncate text-sm font-medium text-stone-800">{{ block.title }}</span>
                        </div>

                        <button
                            v-if="block.editable"
                            type="button"
                            class="cursor-pointer rounded-lg p-1 text-stone-400 transition hover:bg-stone-100 hover:text-primary-600"
                            aria-label="خيارات البلوك"
                            @click="openEdit(block.id, block.title)"
                        >
                            <Icon name="settings" class="h-5 w-5" />
                        </button>
                    </li>
                </ul>
            </div>

            <div v-if="floatLinksBlock?.editor" class="overflow-hidden rounded-xl border border-stone-200 bg-stone-50/80">
                <div class="flex items-center justify-between gap-3 border-b border-dotted border-stone-200 px-3 py-2.5">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-stone-700">الأزرار الطافية</p>
                        <p class="text-xs text-stone-400">أزرار سريعة ثابتة للتواصل تظهر عائمة أسفل الصفحة بشكل دائم</p>
                    </div>
                    <button
                        type="button"
                        class="shrink-0 cursor-pointer rounded-lg p-1 text-stone-400 transition hover:bg-stone-100 hover:text-primary-600"
                        aria-label="خيارات الأزرار الطافية"
                        @click="openFloatLinksPosition"
                    >
                        <Icon name="settings" class="h-5 w-5" />
                    </button>
                </div>

                <FloatLinksPanel
                    ref="floatLinksPanel"
                    :block-id="floatLinksBlock.id"
                    :editor="floatLinksBlock.editor"
                    @updated="onFloatLinksUpdated"
                />
            </div>
        </div>

        <Modal :title="editTitle" size="lg" name="edit-block">
            <BlockEditorSkeleton v-if="editingLoading" />
            <p v-else-if="editingError" class="px-4 py-4 text-sm text-red-500">{{ editingError }}</p>
            <BlockEditor
                v-else-if="editing"
                :payload="editing"
                @saved="onSaved"
                @close="onCloseEdit"
            />
        </Modal>
    </MainBox>
</template>
