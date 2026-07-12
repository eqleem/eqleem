<script setup>
import { computed, ref } from 'vue';
import Field from './Field.vue';

const props = defineProps({
    modelValue: { type: Array, default: () => [] },
    label: { type: String, default: 'ملفات التحميل' },
    maxFiles: { type: Number, default: 20 },
    uploading: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue', 'upload', 'remove', 'reorder']);

const fileInput = ref(null);
const dragId = ref(null);

const files = computed(() => Array.isArray(props.modelValue) ? props.modelValue : []);
const canUpload = computed(() => !props.disabled && files.value.length < props.maxFiles);

function formatSize(bytes) {
    const size = Number(bytes) || 0;

    if (size < 1024) {
        return `${size} B`;
    }

    if (size < 1024 * 1024) {
        return `${(size / 1024).toFixed(1)} KB`;
    }

    return `${(size / (1024 * 1024)).toFixed(1)} MB`;
}

function pickFiles() {
    if (!canUpload.value) {
        return;
    }

    fileInput.value?.click();
}

function onFilesSelected(event) {
    const selected = Array.from(event.target.files ?? []);
    event.target.value = '';

    if (selected.length === 0) {
        return;
    }

    const remaining = props.maxFiles - files.value.length;
    emit('upload', selected.slice(0, remaining));
}

function onDragStart(event, id) {
    dragId.value = id;
    event.dataTransfer.effectAllowed = 'move';
    event.dataTransfer.setData('text/plain', String(id));
}

function onDrop(targetId) {
    const sourceId = dragId.value ?? null;
    dragId.value = null;

    if (sourceId == null || sourceId === targetId) {
        return;
    }

    const order = files.value.map((item) => item.id);
    const from = order.indexOf(sourceId);
    const to = order.indexOf(targetId);

    if (from < 0 || to < 0) {
        return;
    }

    order.splice(from, 1);
    order.splice(to, 0, sourceId);

    const next = order
        .map((id) => files.value.find((item) => item.id === id))
        .filter(Boolean);

    emit('update:modelValue', next);
    emit('reorder', order);
}
</script>

<template>
    <Field :label="label" block>
        <div class="space-y-3">
            <div v-if="files.length === 0" class="rounded-lg border border-dashed border-stone-200 bg-stone-50 p-4 text-sm text-stone-500">
                لم يتم رفع ملفات تحميل بعد. سيحصل العميل على هذه الملفات بعد إتمام الشراء.
            </div>

            <div class="space-y-2">
                <div
                    v-for="file in files"
                    :key="file.id"
                    class="group flex items-center gap-3 rounded-lg border border-stone-200 bg-white px-3 py-2"
                    draggable="true"
                    @dragstart="onDragStart($event, file.id)"
                    @dragover.prevent
                    @drop.prevent="onDrop(file.id)"
                >
                    <span class="shrink-0 text-stone-400">⋮⋮</span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-stone-700">{{ file.name }}</p>
                        <p class="text-xs text-stone-400">{{ formatSize(file.size) }}</p>
                    </div>
                    <button
                        type="button"
                        class="rounded px-2 py-1 text-xs text-red-600 opacity-0 transition group-hover:opacity-100"
                        :disabled="disabled"
                        @click="$emit('remove', file.id)"
                    >
                        حذف
                    </button>
                </div>
            </div>

            <button
                v-if="canUpload"
                type="button"
                class="rounded-lg border border-dashed border-stone-300 bg-white px-4 py-2 text-sm text-stone-500 hover:border-primary-400 hover:text-primary-600 disabled:opacity-50"
                :disabled="uploading || disabled"
                @click="pickFiles"
            >
                <span v-if="uploading">جاري الرفع…</span>
                <span v-else>رفع ملفات التحميل</span>
            </button>

            <p class="text-xs text-stone-400">يمكنك سحب الملفات لإعادة ترتيبها.</p>

            <input
                ref="fileInput"
                type="file"
                class="hidden"
                multiple
                @change="onFilesSelected"
            >
        </div>
    </Field>
</template>
