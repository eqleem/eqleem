<script setup>
import { onMounted, provide, ref } from 'vue';
import Button from '../../ui/Button.vue';
import Icon from '../../ui/Icon.vue';
import Modal from '../../ui/Modal.vue';
import Switch from '../../settings/Switch.vue';
import BlockEditor from '../editors/BlockEditor.vue';
import { openModal, closeModal } from '../../../lib/modal.js';
import { usePagesStore } from '../../../stores/pages.js';

const props = defineProps({
    pageUuid: { type: String, required: true },
});

const store = usePagesStore();
const dragId = ref(null);
const busyId = ref(null);
const editTitle = ref('إعدادات البلوك');

provide('blockActions', {
    updateBlock: (id, body) => store.updateBlock(props.pageUuid, id, body),
});

onMounted(() => {
    store.fetchBlocks(props.pageUuid);
});

function openAddModal() {
    openModal('add-page-block');
}

async function addBlock(type) {
    try {
        const block = await store.createBlock(props.pageUuid, type);
        closeModal('add-page-block');

        if (block?.id) {
            await openEdit(block.id, block.title);
        }
    } catch {
        // error surfaced via store
    }
}

async function openEdit(id, title = null) {
    editTitle.value = title || 'إعدادات البلوك';
    openModal('edit-page-block');

    try {
        const payload = await store.fetchBlock(props.pageUuid, id);
        editTitle.value = payload?.block?.title || title || 'إعدادات البلوك';
    } catch {
        // error surfaced via store
    }
}

async function onSaved(payload) {
    if (payload?.block?.title) {
        editTitle.value = payload.block.title;
    }

    closeModal('edit-page-block');
    store.clearEditingBlock();
}

async function toggleActive(block) {
    busyId.value = block.id;

    try {
        await store.toggleBlockActive(props.pageUuid, block.id, !block.active);
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
        await store.deleteBlock(props.pageUuid, block.id);
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

    const ids = store.blocks.map((block) => block.id);
    const from = ids.indexOf(sourceId);
    const to = ids.indexOf(targetId);

    if (from === -1 || to === -1) {
        return;
    }

    ids.splice(from, 1);
    ids.splice(to, 0, sourceId);

    try {
        await store.reorderBlocks(props.pageUuid, ids);
    } catch {
        // error surfaced via store
    }
}
</script>

<template>
    <div class="space-y-3">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="text-sm font-semibold text-gray-800">بلوكات الصفحة</p>
                <p class="mt-0.5 text-xs text-gray-400">أضف ورتّب بلوكات محتوى الصفحة.</p>
            </div>
            <Button
                type="button"
                label="إضافة بلوك"
                variant="secondary"
                :disabled="store.saving"
                @click="openAddModal"
            >
                <template #icon><Icon name="plus" class="h-4 w-4" /></template>
            </Button>
        </div>

        <p v-if="store.blocksLoading && !store.blocks.length" class="text-sm text-gray-400">جاري التحميل…</p>
        <p v-else-if="store.blocksError" class="text-sm text-red-500">{{ store.blocksError }}</p>

        <div v-else class="relative min-h-20 rounded-xl border border-gray-200 bg-gray-50/80">
            <ul class="space-y-1.5 p-2">
                <li
                    v-for="block in store.blocks"
                    :key="block.id"
                    class="group flex items-center gap-2 rounded-lg border border-transparent bg-white px-2 py-2 transition hover:border-gray-200"
                    :class="{ 'opacity-50': !block.active }"
                    draggable="true"
                    @dragstart="onDragStart($event, block.id)"
                    @dragover="onDragOver"
                    @drop="onDrop($event, block.id)"
                >
                    <button
                        type="button"
                        class="cursor-grab rounded-md p-1 text-gray-300 transition hover:bg-gray-100 hover:text-gray-500 active:cursor-grabbing"
                        aria-label="سحب لإعادة الترتيب"
                    >
                        <Icon name="grip-vertical" class="h-4 w-4" />
                    </button>

                    <button
                        v-if="block.editable"
                        type="button"
                        class="flex min-w-0 flex-1 items-center gap-2 text-start transition hover:text-primary-600"
                        @click="openEdit(block.id, block.title)"
                    >
                        <img :src="block.icon_url" alt="" class="h-6 w-6 shrink-0 rounded-md bg-gray-100 p-1">
                        <span class="truncate text-sm font-medium text-gray-800">{{ block.title }}</span>
                    </button>
                    <div v-else class="flex min-w-0 flex-1 items-center gap-2">
                        <img :src="block.icon_url" alt="" class="h-6 w-6 shrink-0 rounded-md bg-gray-100 p-1">
                        <span class="truncate text-sm font-medium text-gray-800">{{ block.title }}</span>
                    </div>

                    <button
                        type="button"
                        class="pointer-events-none shrink-0 rounded-lg p-1 text-red-400/80 opacity-0 transition hover:bg-red-50 hover:text-red-500 group-hover:pointer-events-auto group-hover:opacity-100"
                        aria-label="حذف البلوك"
                        :disabled="busyId === block.id"
                        @click.stop="removeBlock(block)"
                    >
                        <Icon name="trash" class="h-4 w-4" />
                    </button>

                    <button
                        v-if="block.editable"
                        type="button"
                        class="rounded-lg p-1 text-gray-400 transition hover:bg-gray-100 hover:text-primary-600"
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
                v-if="!store.blocks.length && !store.blocksLoading"
                class="pointer-events-none flex min-h-20 select-none items-center justify-center pb-3 text-[11px] text-gray-300"
            >
                أضف بلوكات لصفحتك من الزر بالأعلى
            </p>
        </div>

        <Modal title="إضافة بلوك" size="lg" name="add-page-block">
            <div class="space-y-2 p-4">
                <button
                    v-for="blockType in store.blockTypes"
                    :key="blockType.slug"
                    type="button"
                    class="flex w-full items-center gap-3 rounded-xl border border-gray-100 px-3 py-3 text-start transition hover:border-gray-200 hover:bg-gray-50 disabled:opacity-50"
                    :disabled="store.saving"
                    @click="addBlock(blockType.slug)"
                >
                    <img :src="blockType.icon_url" alt="" class="h-9 w-9 shrink-0 rounded-lg bg-gray-100 p-1.5">
                    <span class="min-w-0">
                        <span class="block text-sm font-medium text-gray-800">{{ blockType.name }}</span>
                        <span class="block text-xs text-gray-400">{{ blockType.description }}</span>
                    </span>
                </button>
            </div>
        </Modal>

        <Modal :title="editTitle" size="lg" name="edit-page-block">
            <p v-if="store.editingBlockLoading" class="px-4 py-6 text-sm text-gray-400">جاري التحميل...</p>
            <p v-else-if="store.editingBlockError" class="px-4 py-4 text-sm text-red-500">{{ store.editingBlockError }}</p>
            <BlockEditor
                v-else-if="store.editingBlock"
                :payload="store.editingBlock"
                @saved="onSaved"
                @close="closeModal('edit-page-block'); store.clearEditingBlock()"
            />
        </Modal>
    </div>
</template>
