<script setup>
import { computed, ref } from 'vue';
import Field from './Field.vue';

const props = defineProps({
    modelValue: { type: Array, default: () => [] },
    label: { type: String, default: 'صور المشروع' },
    maxFiles: { type: Number, default: 20 },
    uploading: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue', 'upload', 'remove', 'reorder']);

const fileInput = ref(null);
const dragId = ref(null);

const images = computed(() => Array.isArray(props.modelValue) ? props.modelValue : []);
const canUpload = computed(() => !props.disabled && images.value.length < props.maxFiles);

function pickFiles() {
    if (!canUpload.value) {
        return;
    }

    fileInput.value?.click();
}

function onFilesSelected(event) {
    const files = Array.from(event.target.files ?? []);
    event.target.value = '';

    if (files.length === 0) {
        return;
    }

    const remaining = props.maxFiles - images.value.length;
    emit('upload', files.slice(0, remaining));
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

    const order = images.value.map((item) => item.id);
    const from = order.indexOf(sourceId);
    const to = order.indexOf(targetId);

    if (from < 0 || to < 0) {
        return;
    }

    order.splice(from, 1);
    order.splice(to, 0, sourceId);

    const next = order
        .map((id) => images.value.find((item) => item.id === id))
        .filter(Boolean);

    emit('update:modelValue', next);
    emit('reorder', order);
}
</script>

<template>
    <Field :label="label" block>
        <div class="space-y-3">
            <div class="flex flex-wrap gap-2">
                <div
                    v-for="image in images"
                    :key="image.id"
                    class="group relative h-20 w-20 overflow-hidden rounded-lg bg-gray-200"
                    draggable="true"
                    @dragstart="onDragStart($event, image.id)"
                    @dragover.prevent
                    @drop.prevent="onDrop(image.id)"
                >
                    <img :src="image.url" alt="" class="h-full w-full object-cover">
                    <button
                        type="button"
                        class="absolute end-1 top-1 rounded bg-black/60 px-1.5 py-0.5 text-xs text-white opacity-0 transition group-hover:opacity-100"
                        :disabled="disabled"
                        @click="$emit('remove', image.id)"
                    >
                        ×
                    </button>
                </div>

                <button
                    v-if="canUpload"
                    type="button"
                    class="flex h-20 w-20 items-center justify-center rounded-lg border border-dashed border-gray-300 bg-white text-center text-xs text-gray-400 hover:border-primary-400 hover:text-primary-600 disabled:opacity-50"
                    :disabled="uploading || disabled"
                    @click="pickFiles"
                >
                    <span v-if="uploading">جاري الرفع…</span>
                    <span v-else>رفع الصور</span>
                </button>
            </div>

            <p class="text-xs text-gray-400">يمكنك سحب الصور لإعادة ترتيبها. الحد الأقصى {{ maxFiles }} صور.</p>

            <input
                ref="fileInput"
                type="file"
                class="hidden"
                accept="image/jpeg,image/png,image/webp,image/gif"
                multiple
                @change="onFilesSelected"
            >
        </div>
    </Field>
</template>
