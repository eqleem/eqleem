<script setup>
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue';
import { createFreeCrop, createSimpleCrop } from '../../../resources/js/lib/image-crop-engine.js';
import Field from './Field.vue';
import Button from './Button.vue';
import Icon from './Icon.vue';

const props = defineProps({
    modelValue: { default: null },
    preview: { type: String, default: null },
    name: { type: String, default: 'file' },
    label: { type: String, default: null },
    info: { type: String, default: '' },
    uploadLabel: { type: String, default: 'رفع صورة' },
    cropTitle: { type: String, default: 'قص الصورة' },
    shape: { type: String, default: 'square' },
    outputSize: { type: Number, default: 512 },
    allowShapeSwitch: { type: Boolean, default: false },
    previewClass: { type: String, default: 'size-20 rounded-lg object-cover' },
    placeholder: { type: String, default: '/assets/images/user.png' },
    placeholderClass: { type: String, default: 'size-12 opacity-60' },
    accept: { type: String, default: 'image/jpeg,image/png,image/webp,image/gif' },
    error: { type: String, default: null },
    /** When true, opens crop modal after pick; user can confirm crop or use original. */
    enableCrop: { type: Boolean, default: true },
});

const emit = defineEmits(['update:modelValue', 'update:preview', 'change']);

const fileInput = ref(null);
const cropHost = ref(null);
const open = ref(false);
const cropping = ref(false);
const pendingSrc = ref(null);
const pendingFile = ref(null);
const activeShape = ref(props.shape);
const cropInstance = ref(null);
const localPreview = ref(props.preview);

watch(() => props.preview, (value) => {
    if (!props.modelValue) {
        localPreview.value = value;
    }
});

watch(() => props.shape, (value) => {
    activeShape.value = value;
});

const displayPreview = computed(() => localPreview.value || props.preview);
const modalMaxWidth = computed(() => (activeShape.value === 'free' ? 'max-w-lg' : 'max-w-md'));
const cropHostClass = computed(() => (
    activeShape.value === 'free'
        ? 'min-h-[400px] min-w-[360px]'
        : 'min-h-[360px] min-w-[320px]'
));

function revokeIfBlob(url) {
    if (url?.startsWith?.('blob:')) {
        URL.revokeObjectURL(url);
    }
}

function setPreviewFromFile(file) {
    revokeIfBlob(localPreview.value);
    const url = URL.createObjectURL(file);
    localPreview.value = url;
    emit('update:preview', url);
}

function commitFile(file) {
    setPreviewFromFile(file);
    emit('update:modelValue', file);
    emit('change', file);
}

function pickFile() {
    fileInput.value?.click();
}

function onFileChange(event) {
    const file = event.target.files?.[0] ?? null;
    event.target.value = '';

    if (!file) {
        return;
    }

    if (!props.enableCrop) {
        commitFile(file);
        return;
    }

    const reader = new FileReader();
    reader.onload = (loadEvent) => {
        pendingSrc.value = loadEvent.target?.result ?? null;
        pendingFile.value = file;
        activeShape.value = props.shape;

        if (!pendingSrc.value) {
            return;
        }

        open.value = true;
        nextTick(() => {
            requestAnimationFrame(() => mountCropper());
        });
    };
    reader.readAsDataURL(file);
}

function teardownCropper() {
    cropInstance.value?.destroy();
    cropInstance.value = null;

    if (cropHost.value) {
        cropHost.value.innerHTML = '';
    }
}

function mountCropper() {
    if (!open.value || !pendingSrc.value || !cropHost.value) {
        return;
    }

    teardownCropper();

    if (activeShape.value === 'free') {
        cropInstance.value = createFreeCrop(cropHost.value, pendingSrc.value, {
            containerWidth: 360,
            containerHeight: 400,
            maxOutputSize: props.outputSize,
        });
    } else {
        cropInstance.value = createSimpleCrop(cropHost.value, pendingSrc.value, {
            viewportSize: 220,
            containerSize: 320,
            shape: activeShape.value,
        });
    }
}

function setCropShape(nextShape) {
    if (activeShape.value === nextShape) {
        return;
    }

    activeShape.value = nextShape;
    mountCropper();
}

function closeCropper() {
    open.value = false;
    pendingSrc.value = null;
    pendingFile.value = null;
    teardownCropper();
}

function useWithoutCrop() {
    if (!pendingFile.value || cropping.value) {
        return;
    }

    commitFile(pendingFile.value);
    closeCropper();
}

async function confirmCrop() {
    if (!cropInstance.value || cropping.value) {
        return;
    }

    cropping.value = true;

    try {
        const blob = activeShape.value === 'free'
            ? await cropInstance.value.toBlob('image/jpeg', 0.92)
            : await cropInstance.value.toBlob(props.outputSize, 'image/jpeg', 0.92);

        if (!blob) {
            throw new Error('Export failed');
        }

        const file = new File([blob], 'cropped.jpg', { type: 'image/jpeg' });
        commitFile(file);
        closeCropper();
    } catch (error) {
        console.error('FileCrop confirmCrop failed', error);

        // Fall back to the original file so upload still works if canvas export fails.
        if (pendingFile.value) {
            commitFile(pendingFile.value);
            closeCropper();
        }
    } finally {
        cropping.value = false;
    }
}

onBeforeUnmount(() => {
    teardownCropper();
    revokeIfBlob(localPreview.value);
});
</script>

<template>
    <div class="relative">
        <Field :name="name" :label="label" :info="info" :error="error">
            <div class="flex flex-1 items-center gap-3 p-2">
                <div class="flex size-20 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-white">
                    <img
                        v-if="displayPreview"
                        :src="displayPreview"
                        :alt="label || ''"
                        :class="previewClass"
                    >
                    <img
                        v-else-if="placeholder"
                        :src="placeholder"
                        alt=""
                        :class="placeholderClass"
                    >
                </div>

                <button
                    type="button"
                    class="cursor-pointer text-sm font-medium text-primary-700 hover:underline"
                    @click="pickFile"
                >
                    {{ uploadLabel }}
                </button>

                <input
                    ref="fileInput"
                    type="file"
                    :accept="accept"
                    class="sr-only"
                    @change="onFileChange"
                >
            </div>
        </Field>

        <Teleport to="body">
            <div
                v-if="open"
                class="fixed inset-0 z-[100] flex items-center justify-center p-4"
                data-file-crop-overlay
            >
                <div class="absolute inset-0 bg-gray-800/75" @click="closeCropper" />

                <div
                    class="relative w-full rounded-xl bg-white shadow-xl"
                    :class="modalMaxWidth"
                    @click.stop
                >
                    <div class="flex items-center justify-between border-b border-gray-100 p-3 px-4">
                        <p class="text-sm font-semibold text-gray-600" dir="rtl">{{ cropTitle }}</p>
                        <button
                            type="button"
                            class="rounded-md bg-gray-100 p-1 text-gray-400 hover:bg-gray-200"
                            :disabled="cropping"
                            @click="closeCropper"
                        >
                            <Icon name="x" class="h-4 w-4" />
                        </button>
                    </div>

                    <div v-if="allowShapeSwitch" class="flex justify-center gap-2 border-b border-gray-50 px-4 py-2">
                        <button
                            type="button"
                            class="rounded-md px-3 py-1 text-xs font-medium"
                            :class="activeShape === 'square' ? 'bg-primary-100 text-primary-700' : 'bg-gray-100 text-gray-500'"
                            :disabled="cropping"
                            @click="setCropShape('square')"
                        >
                            مربع
                        </button>
                        <button
                            type="button"
                            class="rounded-md px-3 py-1 text-xs font-medium"
                            :class="activeShape === 'circle' ? 'bg-primary-100 text-primary-700' : 'bg-gray-100 text-gray-500'"
                            :disabled="cropping"
                            @click="setCropShape('circle')"
                        >
                            دائري
                        </button>
                        <button
                            type="button"
                            class="rounded-md px-3 py-1 text-xs font-medium"
                            :class="activeShape === 'free' ? 'bg-primary-100 text-primary-700' : 'bg-gray-100 text-gray-500'"
                            :disabled="cropping"
                            @click="setCropShape('free')"
                        >
                            حر
                        </button>
                    </div>

                    <div class="flex justify-center p-4">
                        <div ref="cropHost" :class="cropHostClass" />
                    </div>

                    <div class="flex flex-wrap justify-end gap-2 border-t border-gray-100 p-3 px-4" dir="rtl">
                        <Button
                            type="button"
                            variant="ghost"
                            label="إلغاء"
                            :disabled="cropping"
                            @click="closeCropper"
                        />
                        <Button
                            type="button"
                            variant="secondary"
                            label="استخدام بدون قص"
                            :disabled="cropping"
                            @click="useWithoutCrop"
                        />
                        <Button
                            type="button"
                            label="تأكيد القص"
                            :loading="cropping"
                            @click="confirmCrop"
                        />
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>
