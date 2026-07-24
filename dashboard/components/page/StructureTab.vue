<script setup>
import { defineAsyncComponent, nextTick, onMounted, onBeforeUnmount, ref } from 'vue';
import { storeToRefs } from 'pinia';
import MainBox from '../ui/MainBox.vue';
import Button from '../ui/Button.vue';
import BrandMark from '../ui/BrandMark.vue';
import Icon from '../ui/Icon.vue';
import Modal from '../ui/Modal.vue';
import BlockEditorSkeleton from './editors/BlockEditorSkeleton.vue';
import { openModal } from '../../lib/modal.js';
import { notifyApiSuccess } from '../../lib/notify.js';
import { lockBodyScroll, unlockBodyScroll } from '../../lib/bodyScrollLock.js';
import { usePageStructureStore } from '../../stores/pageStructure.js';

const BlockEditor = defineAsyncComponent(() => import('./editors/BlockEditor.vue'));
const BlockLinkEditor = defineAsyncComponent(() => import('./editors/BlockLinkEditor.vue'));
const BlockLinksPanel = defineAsyncComponent(() => import('./editors/BlockLinksPanel.vue'));
const FooterDocumentsPanel = defineAsyncComponent(() => import('./editors/FooterDocumentsPanel.vue'));
const FloatLinksPanel = defineAsyncComponent(() => import('./editors/FloatLinksPanel.vue'));
const HeaderSocialLinksPanel = defineAsyncComponent(() => import('./editors/HeaderSocialLinksPanel.vue'));
const CatalogSectionsModal = defineAsyncComponent(() => import('./CatalogSectionsModal.vue'));

function openManageSections() {
    openModal('catalog-sections');
}

const store = usePageStructureStore();
const {
    topBlocks,
    ctaBlock,
    userBlocks,
    footerBlock,
    floatLinksBlock,
    contentCounts,
    loading,
    error,
    saving,
    editing,
    editingLoading,
    editingError,
} = storeToRefs(store);

const dragId = ref(null);
const busyId = ref(null);
const reorderBusyId = ref(null);
const editTitle = ref('إعدادات البلوك');
const editModalOpen = ref(false);
const addLinkModalOpen = ref(false);
const addLinkEditorKey = ref(0);
const ctaLinksPanel = ref(null);
const footerDocumentsPanel = ref(null);
const footerLinksPanel = ref(null);
const headerSocialBlockId = ref(null);
const socialModalOpen = ref(false);
const expandedSectionStorageKey = 'eqleem:page-structure:expanded-section';
const sectionIds = new Set([
    'quick-buttons',
    'page-sections',
    'footer-documents',
    'footer-links',
    'floating-buttons',
]);

function initialExpandedSection() {
    if (typeof window === 'undefined') {
        return 'page-sections';
    }

    try {
        const storedValue = window.localStorage.getItem(expandedSectionStorageKey);

        if (storedValue === null) {
            return 'page-sections';
        }

        const storedSection = JSON.parse(storedValue);

        if (storedSection === null || sectionIds.has(storedSection)) {
            return storedSection;
        }
    } catch {}

    return 'page-sections';
}

const expandedSection = ref(initialExpandedSection());

function isSectionExpanded(section) {
    return expandedSection.value === section;
}

function toggleSection(section) {
    expandedSection.value = isSectionExpanded(section) ? null : section;

    try {
        window.localStorage.setItem(expandedSectionStorageKey, JSON.stringify(expandedSection.value));
    } catch {}
}

onMounted(() => {
    void store.fetchStructure()
        .then(() => store.fetchContentCounts())
        .catch(() => {});
});

onBeforeUnmount(() => {
    closeHeaderSocialLinks();
    closeAddLinkModal();
});

function openAddCtaLink() {
    ctaLinksPanel.value?.openAdd?.();
}

function openAddFooterLink() {
    footerLinksPanel.value?.openAdd?.();
}

async function openAddPageLink() {
    editTitle.value = 'إضافة رابط';
    expandedSection.value = 'page-sections';

    try {
        window.localStorage.setItem(expandedSectionStorageKey, JSON.stringify(expandedSection.value));
    } catch {}

    store.beginCreateBlockLink();
    addLinkEditorKey.value += 1;

    // Defer past the opening click so the new overlay cannot swallow it and auto-close.
    await nextTick();
    addLinkModalOpen.value = true;
    lockBodyScroll();
}

function closeAddLinkModal() {
    if (!addLinkModalOpen.value) {
        return;
    }

    addLinkModalOpen.value = false;
    store.clearEditing();
    unlockBodyScroll();
}

function openAddFooterDocument() {
    footerDocumentsPanel.value?.openAdd?.();
}

function openHeaderSocialLinks(block) {
    headerSocialBlockId.value = Number(block.id);
    socialModalOpen.value = true;
}

function closeHeaderSocialLinks() {
    socialModalOpen.value = false;
}

function onSocialModalClosed() {
    headerSocialBlockId.value = null;
}

function onCtaUpdated(payload) {
    if (payload?.editor) {
        store.setCtaEditor(payload.editor);
    }
}

function onFooterLinksUpdated(payload) {
    if (payload?.editor) {
        store.setFooterEditor(payload.editor);
    }
}

function onFooterDocumentsUpdated(payload) {
    if (payload?.editor) {
        store.setFooterEditor(payload.editor);
    }
}

function onFloatLinksUpdated(payload) {
    if (payload?.editor) {
        store.setFloatLinksEditor(payload.editor);
    }
}

async function openEdit(id, title = null) {
    editTitle.value = title || 'إعدادات البلوك';
    editModalOpen.value = true;

    try {
        const payload = await store.fetchBlock(id);
        editTitle.value = payload?.block?.title || title || 'إعدادات البلوك';
    } catch {
        // error surfaced via store
    }
}

function onCloseEdit() {
    editModalOpen.value = false;
    store.clearEditing();
}

function onEditModalClosed() {
    store.clearEditing();
}

async function onSaved(payload) {
    if (payload?.block?.title) {
        editTitle.value = payload.block.title;
    }

    if (addLinkModalOpen.value) {
        closeAddLinkModal();
    } else {
        editModalOpen.value = false;
        store.clearEditing();
    }

    notifyApiSuccess(payload, 'Saved');
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

async function moveBlock(blockId, direction) {
    const ids = userBlocks.value.map((block) => block.id);
    const from = ids.indexOf(blockId);
    const to = from + direction;

    if (from === -1 || to < 0 || to >= ids.length || reorderBusyId.value !== null) {
        return;
    }

    ids.splice(from, 1);
    ids.splice(to, 0, blockId);
    reorderBusyId.value = blockId;

    try {
        await store.reorderBlocks(ids);
    } catch {
        // error surfaced via store
    } finally {
        reorderBusyId.value = null;
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

</script>

<template>
    <MainBox title="صفحتي" subtitle="قم بإضافة وترتيب مكونات ومحتوى صفحتك">
        <template #icon>
            <img :src="'/assets/icons/tabler/puzzle-2.svg'" class="h-7 w-7" alt="">
        </template>

        <div v-if="loading && !topBlocks.length && !userBlocks.length" class="px-4 py-6 flex items-center justify-center"><LoadingSpinner size="sm" /></div>
        <p v-else-if="error" class="px-4 pt-3 text-sm text-red-500">{{ error }}</p>

        <div v-else class="space-y-4 p-4">
            <template v-if="topBlocks.length">
                <div
                    v-for="block in topBlocks"
                    :key="block.id"
                    class="overflow-hidden rounded-xl border border-stone-200 bg-stone-50/80"
                >
                    <ul class="p-2">
                        <li
                            class="flex items-center gap-2 rounded-lg border border-transparent bg-white px-2 py-2"
                        >
                            <div class="hidden rounded-md p-1 text-stone-200 sm:block">
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
                                v-if="block.type === 'header'"
                                type="button"
                                class="shrink-0 cursor-pointer rounded-lg border border-stone-200 bg-white px-2.5 py-1.5 text-xs font-medium text-stone-600 transition hover:border-primary-200 hover:bg-primary-50 hover:text-primary-700"
                                @click.stop="openHeaderSocialLinks(block)"
                            >
                                <span class="sm:hidden">السوشال ميديا</span>
                                <span class="hidden sm:inline">روابط الشبكات الإجتماعية</span>
                            </button>

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
            </template>

            <div>
                <div class="border-t-2 border-stone-200 my-10 border-dotted" aria-hidden="true" />
            </div>

            <div v-if="ctaBlock?.editor" class="overflow-hidden rounded-xl border border-stone-200 bg-stone-50/80">
                <div class="flex items-center justify-between gap-3 border-b border-dotted border-stone-200 px-3 py-2.5">
                    <button
                        type="button"
                        class="group flex min-w-0 flex-1 cursor-pointer items-center gap-2 text-start"
                        aria-controls="quick-buttons-section"
                        :aria-expanded="isSectionExpanded('quick-buttons')"
                        :aria-label="isSectionExpanded('quick-buttons') ? 'طي الأزرار السريعة' : 'توسيع الأزرار السريعة'"
                        @click="toggleSection('quick-buttons')"
                    >
                        <span class="shrink-0 rounded-lg p-1.5 text-stone-400 transition group-hover:bg-white group-hover:text-primary-600">
                            <Icon
                                name="chevron-down"
                                class="h-5 w-5 transition-transform"
                                :class="{ 'rotate-180': isSectionExpanded('quick-buttons') }"
                            />
                        </span>
                        <img :src="'/assets/icons/tabler/hand-click.svg'" alt="" class="hidden h-7 w-7 shrink-0 rounded-md bg-white p-1 sm:block">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-stone-700">الأزرار السريعة (هدف الصفحة)</p>
                            <p class="text-xs text-stone-400">أضف زر رئيسي وأزرار ثانوية لدفع العميل لإتخاذ الإجراء المطلوب.</p>
                        </div>
                    </button>
                    <Button
                        class="shrink-0 !px-2.5 sm:!px-4"
                        aria-label="أضف زر"
                        @click="openAddCtaLink"
                    >
                        <template #icon><Icon name="plus" class="h-4 w-4" /></template>
                        <span class="hidden sm:inline">أضف زر</span>
                    </Button>
                </div>

                <div id="quick-buttons-section" v-show="isSectionExpanded('quick-buttons')">
                    <BlockLinksPanel
                        ref="ctaLinksPanel"
                        embedded
                        show-primary-badge
                        :block-id="ctaBlock.id"
                        :editor="ctaBlock.editor"
                        @updated="onCtaUpdated"
                    />
                </div>
            </div>

            <div>
                <div class="border-t-2 border-stone-200 my-10 border-dotted" aria-hidden="true" />
            </div>

            <div class="overflow-hidden rounded-xl border border-stone-200 bg-stone-50/80">
                <div class="flex items-center justify-between gap-3 border-b border-dotted border-stone-200 px-3 py-2.5">
                    <button
                        type="button"
                        class="group flex min-w-0 flex-1 cursor-pointer items-center gap-2 text-start"
                        aria-controls="page-sections-list"
                        :aria-expanded="isSectionExpanded('page-sections')"
                        :aria-label="isSectionExpanded('page-sections') ? 'طي أقسام الصفحة' : 'توسيع أقسام الصفحة'"
                        @click="toggleSection('page-sections')"
                    >
                        <span class="shrink-0 rounded-lg p-1.5 text-stone-400 transition group-hover:bg-white group-hover:text-primary-600">
                            <Icon
                                name="chevron-down"
                                class="h-5 w-5 transition-transform"
                                :class="{ 'rotate-180': isSectionExpanded('page-sections') }"
                            />
                        </span>
                        <img :src="'/assets/icons/tabler/layout-list.svg'" alt="" class="hidden h-7 w-7 shrink-0 rounded-md bg-white p-1 sm:block">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-stone-700">مكونات الصفحة</p>
                            <p class="text-xs text-stone-400">قم بإضافة وترتيب مكونات نشاطك</p>
                        </div>
                    </button>
                    <div class="flex shrink-0 items-center gap-2">
                        <button
                            type="button"
                            class="inline-flex h-9 shrink-0 cursor-pointer items-center justify-center gap-2 whitespace-nowrap rounded-md bg-primary-600 !px-2.5 py-2 text-sm text-white transition hover:bg-primary-700 disabled:pointer-events-none disabled:opacity-50 sm:!px-4"
                            aria-label="إضافة رابط"
                            :disabled="saving"
                            @click.stop.prevent="openAddPageLink"
                        >
                            <Icon name="plus" class="h-4 w-4" />
                            <span class="hidden sm:inline">إضافة رابط</span>
                        </button>
                        <Button
                            class="shrink-0 !px-2.5 sm:!px-4"
                            variant="secondary"
                            aria-label="إدارة المكونات"
                            :disabled="saving"
                            @click="openManageSections"
                        >
                            <template #icon><Icon name="settings" class="h-4 w-4" /></template>
                            <span class="hidden sm:inline">إدارة المكونات</span>
                        </Button>
                    </div>
                </div>

                <div id="page-sections-list" v-show="isSectionExpanded('page-sections')" class="relative min-h-20">
                    <ul class="space-y-1.5 p-2">
                        <li
                            v-for="(block, index) in userBlocks"
                            :key="block.id"
                            class="group flex items-center lg:gap-2 gap-1 rounded-lg border border-transparent bg-white px-2 py-2 transition hover:border-stone-200"
                            :class="{ 'opacity-50': !block.active }"
                            @dragover="onDragOver"
                            @drop="onDrop($event, block.id)"
                        >
                            <div
                                draggable="true"
                                class="hidden cursor-grab rounded-md p-1 text-stone-300 transition hover:bg-stone-100 hover:text-stone-500 active:cursor-grabbing sm:block"
                                role="button"
                                tabindex="0"
                                aria-label="سحب لإعادة الترتيب"
                                @dragstart="onDragStart($event, block.id)"
                                @dragend="dragId = null"
                            >
                                <Icon name="grip-vertical" class="h-4 w-4" />
                            </div>

                            <div class="flex shrink-0 items-center sm:hidden">
                                <button
                                    type="button"
                                    class="rounded-md p-1 text-stone-400 transition hover:bg-stone-100 hover:text-primary-600 disabled:cursor-not-allowed disabled:opacity-25"
                                    aria-label="نقل القسم للأعلى"
                                    :disabled="index === 0 || reorderBusyId !== null"
                                    @click.stop="moveBlock(block.id, -1)"
                                >
                                    <Icon name="arrow-up" class="h-4 w-4" />
                                </button>
                                <button
                                    type="button"
                                    class="rounded-md p-1 text-stone-400 transition hover:bg-stone-100 hover:text-primary-600 disabled:cursor-not-allowed disabled:opacity-25"
                                    aria-label="نقل القسم للأسفل"
                                    :disabled="index === userBlocks.length - 1 || reorderBusyId !== null"
                                    @click.stop="moveBlock(block.id, 1)"
                                >
                                    <Icon name="arrow-down" class="h-4 w-4" />
                                </button>
                            </div>

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

                            <div class="flex min-w-0 flex-1 items-center gap-2">
                                <RouterLink
                                    v-if="contentManageTo(block)"
                                    :to="contentManageTo(block)"
                                    class="inline-flex min-w-0 cursor-pointer items-center gap-1 transition hover:text-primary-600"
                                    @click.stop
                                >
                                    <span class="truncate text-sm font-medium text-stone-800">
                                        {{ block.title }}
                                    </span>
                                    <Icon name="arrow-up" class="h-4 w-4 shrink-0 -rotate-90 text-stone-400" />
                                </RouterLink>

                                <button
                                    v-else
                                    type="button"
                                    class="min-w-0 cursor-pointer text-start transition hover:text-primary-600"
                                    @click.stop="openEdit(block.id, block.title)"
                                >
                                    <span class="block truncate text-sm font-medium text-stone-800">
                                        {{ block.title }}
                                    </span>
                                </button>

                                <span
                                    v-if="contentCounts[block.id] !== undefined"
                                    class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-medium"
                                    :class="Number(contentCounts[block.id].count) > 0
                                        ? 'bg-emerald-50 text-emerald-700'
                                        : 'bg-stone-100 text-stone-500'"
                                >
                                    {{ contentCounts[block.id].count }}
                                    <span class="hidden sm:inline">{{ contentCounts[block.id].label }}</span>
                                </span>
                            </div>

                            <button
                                type="button"
                                class="hidden"
                                aria-label="حذف البلوك"
                                :disabled="busyId === block.id"
                                @click.stop="removeBlock(block)"
                            >
                                <Icon name="trash" class="h-4 w-4" />
                            </button>

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

                    <p
                        v-if="!userBlocks.length"
                        class="pointer-events-none absolute inset-0 flex select-none items-center justify-center px-4 text-center text-xs text-stone-400"
                    >
                        لا توجد مكونات بعد. اضغط «إضافة رابط» أو «إدارة المكونات» للبدء.
                    </p>
                </div>
            </div>

            <div>
                <div class="border-t-2 border-stone-200 my-10 border-dotted" aria-hidden="true" />
            </div>

            <div v-if="footerBlock?.editor" class="overflow-hidden rounded-xl border border-stone-200 bg-stone-50/80">
                <div class="flex items-center justify-between gap-3 border-b border-dotted border-stone-200 px-3 py-2.5">
                    <button
                        type="button"
                        class="group flex min-w-0 flex-1 cursor-pointer items-center gap-2 text-start"
                        aria-controls="footer-documents-section"
                        :aria-expanded="isSectionExpanded('footer-documents')"
                        :aria-label="isSectionExpanded('footer-documents') ? 'طي الوثائق والضمانات' : 'توسيع الوثائق والضمانات'"
                        @click="toggleSection('footer-documents')"
                    >
                        <span class="shrink-0 rounded-lg p-1.5 text-stone-400 transition group-hover:bg-white group-hover:text-primary-600">
                            <Icon
                                name="chevron-down"
                                class="h-5 w-5 transition-transform"
                                :class="{ 'rotate-180': isSectionExpanded('footer-documents') }"
                            />
                        </span>
                        <img :src="'/assets/icons/tabler/file-certificate.svg'" alt="" class="hidden h-7 w-7 shrink-0 rounded-md bg-white p-1 sm:block">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-stone-700">الوثائق والضمانات</p>
                            <p class="text-xs text-stone-400">أضف وثائق النشاط والضمانات التي تظهر في تذييل الصفحة</p>
                        </div>
                    </button>
                    <Button
                        class="shrink-0 !px-2.5 sm:!px-4"
                        aria-label="أضف وثيقة"
                        @click="openAddFooterDocument"
                    >
                        <template #icon><Icon name="plus" class="h-4 w-4" /></template>
                        <span class="hidden sm:inline">أضف وثيقة</span>
                    </Button>
                </div>

                <div id="footer-documents-section" v-show="isSectionExpanded('footer-documents')">
                    <FooterDocumentsPanel
                        ref="footerDocumentsPanel"
                        :block-id="footerBlock.id"
                        :editor="footerBlock.editor"
                        @updated="onFooterDocumentsUpdated"
                    />
                </div>
            </div>

            <div>
                <div class="border-t-2 border-stone-200 my-10 border-dotted" aria-hidden="true" />
            </div>

            <div v-if="footerBlock?.editor" class="overflow-hidden rounded-xl border border-stone-200 bg-stone-50/80">
                <div class="flex items-center justify-between gap-3 border-b border-dotted border-stone-200 px-3 py-2.5">
                    <button
                        type="button"
                        class="group flex min-w-0 flex-1 cursor-pointer items-center gap-2 text-start"
                        aria-controls="footer-links-section"
                        :aria-expanded="isSectionExpanded('footer-links')"
                        :aria-label="isSectionExpanded('footer-links') ? 'طي روابط تذييل الصفحة' : 'توسيع روابط تذييل الصفحة'"
                        @click="toggleSection('footer-links')"
                    >
                        <span class="shrink-0 rounded-lg p-1.5 text-stone-400 transition group-hover:bg-white group-hover:text-primary-600">
                            <Icon
                                name="chevron-down"
                                class="h-5 w-5 transition-transform"
                                :class="{ 'rotate-180': isSectionExpanded('footer-links') }"
                            />
                        </span>
                        <img :src="'/assets/icons/tabler/Link.svg'" alt="" class="hidden h-7 w-7 shrink-0 rounded-md bg-white p-1 sm:block">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-stone-700">روابط تذييل الصفحة</p>
                            <p class="text-xs text-stone-400">أضف روابط مهمة تظهر في تذييل الصفحة</p>
                        </div>
                    </button>
                    <Button
                        class="shrink-0 !px-2.5 sm:!px-4"
                        aria-label="أضف رابط"
                        @click="openAddFooterLink"
                    >
                        <template #icon><Icon name="plus" class="h-4 w-4" /></template>
                        <span class="hidden sm:inline">أضف رابط</span>
                    </Button>
                </div>

                <div id="footer-links-section" v-show="isSectionExpanded('footer-links')">
                    <BlockLinksPanel
                        ref="footerLinksPanel"
                        embedded
                        content-type="footer-link"
                        :block-id="footerBlock.id"
                        :editor="footerBlock.editor"
                        @updated="onFooterLinksUpdated"
                    />
                </div>
            </div>

            <div v-if="floatLinksBlock?.editor" class="overflow-hidden rounded-xl border border-stone-200 bg-stone-50/80">
                <div class="flex items-center justify-between gap-3 border-b border-dotted border-stone-200 px-3 py-2.5">
                    <button
                        type="button"
                        class="group flex min-w-0 flex-1 cursor-pointer items-center gap-2 text-start"
                        aria-controls="floating-buttons-section"
                        :aria-expanded="isSectionExpanded('floating-buttons')"
                        :aria-label="isSectionExpanded('floating-buttons') ? 'طي الأزرار الطافية' : 'توسيع الأزرار الطافية'"
                        @click="toggleSection('floating-buttons')"
                    >
                        <span class="shrink-0 rounded-lg p-1.5 text-stone-400 transition group-hover:bg-white group-hover:text-primary-600">
                            <Icon
                                name="chevron-down"
                                class="h-5 w-5 transition-transform"
                                :class="{ 'rotate-180': isSectionExpanded('floating-buttons') }"
                            />
                        </span>
                        <img :src="'/assets/icons/tabler/float-right.svg'" alt="" class="hidden h-7 w-7 shrink-0 rounded-md bg-white p-1 sm:block">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-stone-700">الأزرار الطافية</p>
                            <p class="text-xs text-stone-400">أزرار سريعة ثابتة للتواصل تظهر عائمة أسفل الصفحة بشكل دائم</p>
                        </div>
                    </button>
                </div>

                <div id="floating-buttons-section" v-show="isSectionExpanded('floating-buttons')">
                    <FloatLinksPanel
                        :block-id="floatLinksBlock.id"
                        :editor="floatLinksBlock.editor"
                        @updated="onFloatLinksUpdated"
                    />
                </div>
            </div>
        </div>

        <Modal
            v-model:open="editModalOpen"
            :title="editTitle"
            size="lg"
            name="edit-block"
            @closed="onEditModalClosed"
        >
            <BlockEditorSkeleton v-if="editingLoading" />
            <p v-else-if="editingError" class="px-4 py-4 text-sm text-red-500">{{ editingError }}</p>
            <BlockEditor
                v-else-if="editing"
                :payload="editing"
                @saved="onSaved"
                @close="onCloseEdit"
            />
        </Modal>

        <Teleport to="body">
            <div
                v-if="addLinkModalOpen && editing?.editor"
                class="relative z-[70]"
                role="dialog"
                aria-modal="true"
            >
                <div class="fixed inset-0 bg-stone-800/75" @click="closeAddLinkModal" />

                <div class="fixed inset-0 overflow-y-auto overscroll-contain">
                    <div class="flex min-h-full items-center justify-center p-4" @click.self="closeAddLinkModal">
                        <div class="relative w-full max-w-lg overflow-hidden rounded-xl bg-white shadow-xl">
                            <div class="flex items-center justify-between border-b border-stone-100 bg-white p-3 px-4">
                                <p class="text-sm font-semibold text-stone-600">{{ editTitle }}</p>
                                <button
                                    type="button"
                                    class="cursor-pointer rounded-md bg-stone-100 p-1 text-stone-400 transition hover:bg-stone-200 hover:text-stone-600"
                                    aria-label="إغلاق"
                                    @click="closeAddLinkModal"
                                >
                                    <Icon name="x" class="h-4 w-4" />
                                </button>
                            </div>

                            <BlockLinkEditor
                                :key="addLinkEditorKey"
                                :editor="editing.editor"
                                @saved="onSaved"
                                @close="closeAddLinkModal"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>

        <Modal
            v-model:open="socialModalOpen"
            title="روابط الشبكات الإجتماعية"
            size="md"
            name="header-social-links"
            @closed="onSocialModalClosed"
        >
            <div class="border-b border-stone-100 px-4 pb-3">
                <p class="text-xs text-stone-400">أضف وعدّل حسابات التواصل التي تظهر في معلومات نشاطك أعلى الصفحة.</p>
            </div>
            <HeaderSocialLinksPanel
                v-if="headerSocialBlockId"
                :block-id="headerSocialBlockId"
            />
        </Modal>
    </MainBox>

    <CatalogSectionsModal />
</template>
