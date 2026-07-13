<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { createFreeCrop } from '../../../resources/js/lib/image-crop-engine.js';
import {
    COVER_CLEAR,
    COVER_COLORS,
    COVER_GRADIENTS,
    cssCoverBackground,
    encodeCssCover,
} from '../../data/coverPresets.js';
import { api, ApiError } from '../../lib/api.js';
import Field from './Field.vue';
import Button from './Button.vue';
import Icon from './Icon.vue';

const props = defineProps({
    modelValue: { default: '' },
    preview: { type: String, default: null },
    file: { default: null },
    position: { type: [Number, String], default: 50 },
    name: { type: String, default: 'cover' },
    label: { type: String, default: null },
    info: { type: String, default: '' },
    error: { type: String, default: null },
    outputSize: { type: Number, default: 1920 },
});

const emit = defineEmits(['update:modelValue', 'update:file', 'update:preview', 'update:position']);

const menuOpen = ref(false);
const activeTab = ref('gallery');
const rootEl = ref(null);
const menuEl = ref(null);
const changeBtn = ref(null);
const coverFrame = ref(null);
const fileInput = ref(null);
const customColorInput = ref(null);
const customColor = ref('#6366f1');
const pickingCustomColor = ref(false);
/** @type {import('vue').Ref<{ top: string, left: string, width: string }|null>} */
const menuStyle = ref(null);

const repositioning = ref(false);
const positionY = ref(clampPosition(props.position));
const positionDraft = ref(positionY.value);
const dragging = ref(false);
let dragStartClientY = 0;
let dragStartPosition = 50;

const cropOpen = ref(false);
const cropping = ref(false);
const cropHost = ref(null);
const cropInstance = ref(null);
const pendingSrc = ref(null);
const pendingFile = ref(null);

const unsplashQuery = ref('');
const unsplashLoading = ref(false);
const unsplashError = ref(null);
const unsplashPhotos = ref([]);
const unsplashSelecting = ref(false);
let unsplashTimer = null;
let unsplashLoadedOnce = false;

const localPreview = ref(props.preview);
const cssBackground = ref(cssCoverBackground(props.modelValue));

watch(() => props.preview, (value) => {
    if (! props.file) {
        localPreview.value = value;
    }
});

watch(() => props.modelValue, (value) => {
    if (! props.file) {
        cssBackground.value = cssCoverBackground(value);
    }
});

watch(() => props.file, (value) => {
    if (! value && ! localPreview.value) {
        localPreview.value = props.preview;
        cssBackground.value = cssCoverBackground(props.modelValue);
    }
});

watch(() => props.position, (value) => {
    if (repositioning.value) {
        return;
    }

    positionY.value = clampPosition(value);
    positionDraft.value = positionY.value;
});

const hasCover = computed(() => Boolean(localPreview.value || cssBackground.value));
const canReposition = computed(() => Boolean(localPreview.value) && ! cssBackground.value);
const displayPosition = computed(() => (
    repositioning.value ? positionDraft.value : positionY.value
));

const coverStyle = computed(() => {
    if (cssBackground.value) {
        return { background: cssBackground.value };
    }

    return {};
});

const imageStyle = computed(() => ({
    objectPosition: `50% ${displayPosition.value}%`,
}));

/**
 * @param {unknown} value
 */
function clampPosition(value) {
    const number = Number(value);

    if (! Number.isFinite(number)) {
        return 50;
    }

    return Math.min(100, Math.max(0, Math.round(number)));
}

function resetPosition(value = 50) {
    const next = clampPosition(value);
    positionY.value = next;
    positionDraft.value = next;
    emit('update:position', next);
}

function revokeIfBlob(url) {
    if (url?.startsWith?.('blob:')) {
        URL.revokeObjectURL(url);
    }
}

function closeMenu() {
    menuOpen.value = false;
    menuStyle.value = null;
    pickingCustomColor.value = false;
}

function positionMenu() {
    const btn = changeBtn.value;

    if (! btn) {
        return;
    }

    const rect = btn.getBoundingClientRect();
    const width = Math.min(window.innerWidth - 24, window.innerWidth >= 640 ? 448 : 352);
    let left = rect.right - width;

    if (left < 12) {
        left = 12;
    }

    if (left + width > window.innerWidth - 12) {
        left = Math.max(12, window.innerWidth - width - 12);
    }

    let top = rect.bottom + 8;

    // Keep the menu on-screen when near the bottom of the viewport.
    const estimatedHeight = 360;

    if (top + estimatedHeight > window.innerHeight - 12) {
        top = Math.max(12, rect.top - estimatedHeight - 8);
    }

    menuStyle.value = {
        top: `${Math.round(top)}px`,
        left: `${Math.round(left)}px`,
        width: `${width}px`,
    };
}

function toggleMenu() {
    if (menuOpen.value) {
        closeMenu();

        return;
    }

    positionMenu();
    menuOpen.value = true;

    if (activeTab.value === 'unsplash' && ! unsplashLoadedOnce) {
        loadUnsplash();
    }
}

function setTab(tab) {
    activeTab.value = tab;

    if (tab === 'unsplash' && ! unsplashLoadedOnce) {
        loadUnsplash();
    }
}

function onDocumentPointerDown(event) {
    if (! menuOpen.value || pickingCustomColor.value) {
        return;
    }

    const target = event.target;

    if (rootEl.value?.contains(target) || menuEl.value?.contains(target)) {
        return;
    }

    closeMenu();
}

function onWindowReposition() {
    if (menuOpen.value) {
        positionMenu();
    }
}

onMounted(() => {
    document.addEventListener('pointerdown', onDocumentPointerDown, true);
    window.addEventListener('resize', onWindowReposition);
    window.addEventListener('scroll', onWindowReposition, true);
});

onBeforeUnmount(() => {
    document.removeEventListener('pointerdown', onDocumentPointerDown, true);
    window.removeEventListener('resize', onWindowReposition);
    window.removeEventListener('scroll', onWindowReposition, true);
    teardownCropper();
    revokeIfBlob(localPreview.value);

    if (unsplashTimer) {
        clearTimeout(unsplashTimer);
    }
});

/**
 * @param {'color'|'gradient'} type
 * @param {string} css
 * @param {{ closeMenu?: boolean }} [options]
 */
function commitCss(type, css, options = {}) {
    const shouldCloseMenu = options.closeMenu !== false;

    revokeIfBlob(localPreview.value);
    localPreview.value = null;
    cssBackground.value = css;
    emit('update:preview', null);
    emit('update:file', null);
    emit('update:modelValue', encodeCssCover(type, css));
    resetPosition(50);
    exitReposition(false);

    if (shouldCloseMenu) {
        closeMenu();
    }
}

function commitFile(file) {
    revokeIfBlob(localPreview.value);
    const url = URL.createObjectURL(file);
    localPreview.value = url;
    cssBackground.value = null;
    emit('update:preview', url);
    emit('update:file', file);
    emit('update:modelValue', '');
    resetPosition(50);
    closeMenu();
    exitReposition(false);
}

function commitRemoteUrl(url) {
    revokeIfBlob(localPreview.value);
    localPreview.value = url;
    cssBackground.value = null;
    emit('update:preview', url);
    emit('update:file', null);
    emit('update:modelValue', url);
    resetPosition(50);
    closeMenu();
    exitReposition(false);
}

function removeCover() {
    revokeIfBlob(localPreview.value);
    localPreview.value = null;
    cssBackground.value = null;
    emit('update:preview', null);
    emit('update:file', null);
    emit('update:modelValue', COVER_CLEAR);
    resetPosition(50);
    closeMenu();
    exitReposition(false);
}

function startReposition() {
    if (! canReposition.value) {
        return;
    }

    closeMenu();
    positionDraft.value = positionY.value;
    repositioning.value = true;
}

function exitReposition(restoreDraft = true) {
    stopDragging();
    repositioning.value = false;

    if (restoreDraft) {
        positionDraft.value = positionY.value;
    }
}

function cancelReposition() {
    exitReposition(true);
}

function saveReposition() {
    positionY.value = clampPosition(positionDraft.value);
    emit('update:position', positionY.value);
    exitReposition(false);
}

function onRepositionPointerDown(event) {
    if (! repositioning.value || event.button !== 0) {
        return;
    }

    dragging.value = true;
    dragStartClientY = event.clientY;
    dragStartPosition = positionDraft.value;
    coverFrame.value?.setPointerCapture?.(event.pointerId);
}

function onRepositionPointerMove(event) {
    if (! dragging.value || ! coverFrame.value) {
        return;
    }

    const height = coverFrame.value.clientHeight || 200;
    const deltaRatio = (event.clientY - dragStartClientY) / height;
    // Dragging the image down reveals more of the top (lower object-position Y).
    positionDraft.value = clampPosition(dragStartPosition - (deltaRatio * 100));
}

function stopDragging() {
    dragging.value = false;
}

function onRepositionPointerUp(event) {
    if (! dragging.value) {
        return;
    }

    coverFrame.value?.releasePointerCapture?.(event.pointerId);
    stopDragging();
}

function selectColor(color) {
    commitCss('color', color.value);
}

function selectGradient(gradient) {
    commitCss('gradient', gradient.value);
}

/**
 * @param {string} value
 */
function normalizeHex(value) {
    const raw = String(value ?? '').replace('#', '').trim();

    if (/^[0-9a-fA-F]{3}$/.test(raw)) {
        return `#${raw[0]}${raw[0]}${raw[1]}${raw[1]}${raw[2]}${raw[2]}`.toLowerCase();
    }

    if (/^[0-9a-fA-F]{6}$/.test(raw)) {
        return `#${raw.toLowerCase()}`;
    }

    return null;
}

function openCustomColorPicker() {
    pickingCustomColor.value = true;
    nextTick(() => {
        customColorInput.value?.focus();
        customColorInput.value?.click();
    });
}

function onCustomColorInput(event) {
    const hex = normalizeHex(event.target?.value);

    if (! hex) {
        return;
    }

    customColor.value = hex;
    // Live preview while the OS color dialog is open — keep the menu visible.
    commitCss('color', hex, { closeMenu: false });
}

function onCustomColorChange(event) {
    const hex = normalizeHex(event.target?.value);

    if (hex) {
        customColor.value = hex;
        commitCss('color', hex, { closeMenu: false });
    }

    // Dialog closed (confirm or cancel) — allow outside-click again, keep gallery open.
    pickingCustomColor.value = false;
}

function onCustomHexCommit(event) {
    const hex = normalizeHex(event.target?.value);

    if (! hex) {
        event.target.value = customColor.value;

        return;
    }

    customColor.value = hex;
    event.target.value = hex;
    commitCss('color', hex, { closeMenu: false });
}

function applyCustomColor() {
    const hex = normalizeHex(customColor.value);

    if (! hex) {
        return;
    }

    customColor.value = hex;
    commitCss('color', hex);
}

function onCustomColorBlur() {
    window.setTimeout(() => {
        pickingCustomColor.value = false;
    }, 250);
}

function pickFile() {
    fileInput.value?.click();
}

function onFileChange(event) {
    const file = event.target.files?.[0] ?? null;
    event.target.value = '';

    if (! file) {
        return;
    }

    const reader = new FileReader();
    reader.onload = (loadEvent) => {
        pendingSrc.value = loadEvent.target?.result ?? null;
        pendingFile.value = file;

        if (! pendingSrc.value) {
            return;
        }

        cropOpen.value = true;
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
    if (! cropOpen.value || ! pendingSrc.value || ! cropHost.value) {
        return;
    }

    teardownCropper();
    cropInstance.value = createFreeCrop(cropHost.value, pendingSrc.value, {
        containerWidth: 420,
        containerHeight: 280,
        maxOutputSize: props.outputSize,
    });
}

function closeCropper() {
    cropOpen.value = false;
    pendingSrc.value = null;
    pendingFile.value = null;
    teardownCropper();
}

function useWithoutCrop() {
    if (! pendingFile.value || cropping.value) {
        return;
    }

    commitFile(pendingFile.value);
    closeCropper();
}

async function confirmCrop() {
    if (! cropInstance.value || cropping.value) {
        return;
    }

    cropping.value = true;

    try {
        const blob = await cropInstance.value.toBlob('image/jpeg', 0.92);

        if (! blob) {
            throw new Error('Export failed');
        }

        commitFile(new File([blob], 'cover.jpg', { type: 'image/jpeg' }));
        closeCropper();
    } catch (error) {
        console.error('UploadCover confirmCrop failed', error);

        if (pendingFile.value) {
            commitFile(pendingFile.value);
            closeCropper();
        }
    } finally {
        cropping.value = false;
    }
}

async function loadUnsplash(query = unsplashQuery.value) {
    unsplashLoading.value = true;
    unsplashError.value = null;

    try {
        const params = new URLSearchParams({ per_page: '16' });

        if (query.trim()) {
            params.set('query', query.trim());
        }

        const payload = await api(`/unsplash/photos?${params.toString()}`);
        unsplashPhotos.value = Array.isArray(payload?.data) ? payload.data : [];
        unsplashLoadedOnce = true;
    } catch (error) {
        unsplashPhotos.value = [];
        unsplashError.value = error instanceof ApiError
            ? error.message
            : 'تعذر تحميل صور Unsplash.';
    } finally {
        unsplashLoading.value = false;
    }
}

function onUnsplashQueryInput() {
    if (unsplashTimer) {
        clearTimeout(unsplashTimer);
    }

    unsplashTimer = setTimeout(() => {
        loadUnsplash(unsplashQuery.value);
    }, 400);
}

async function selectUnsplashPhoto(photo) {
    if (! photo?.id || unsplashSelecting.value) {
        return;
    }

    unsplashSelecting.value = true;
    unsplashError.value = null;

    try {
        const payload = await api('/unsplash/photos/select', {
            method: 'POST',
            body: { id: photo.id },
        });

        const url = payload?.data?.url ?? photo.url;

        if (! url) {
            throw new Error('Missing image URL');
        }

        commitRemoteUrl(url);
    } catch (error) {
        unsplashError.value = error instanceof ApiError
            ? error.message
            : 'تعذر اختيار الصورة.';
    } finally {
        unsplashSelecting.value = false;
    }
}
</script>

<template>
    <div ref="rootEl" class="relative w-full">
        <Field :name="name" :label="label" :info="info" :error="error" block label-width="w-full">
            <div class="w-full space-y-2 p-1">
                <div
                    ref="coverFrame"
                    class="group relative w-full overflow-hidden rounded-xl bg-stone-200 select-none"
                    style="height: 200px"
                    :class="repositioning ? (dragging ? 'cursor-grabbing' : 'cursor-grab') : ''"
                    @pointerdown="onRepositionPointerDown"
                    @pointermove="onRepositionPointerMove"
                    @pointerup="onRepositionPointerUp"
                    @pointercancel="onRepositionPointerUp"
                >
                    <div
                        v-if="cssBackground"
                        class="absolute inset-0"
                        :style="coverStyle"
                    />
                    <img
                        v-else-if="localPreview"
                        :src="localPreview"
                        :alt="label || ''"
                        class="pointer-events-none absolute inset-0 h-full w-full object-cover"
                        :style="imageStyle"
                        draggable="false"
                    >
                    <div
                        v-else
                        class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-stone-200 via-stone-100 to-stone-300"
                    >
                        <span class="text-sm text-stone-400">لا توجد صورة غلاف</span>
                    </div>

                    <div
                        v-if="repositioning"
                        class="pointer-events-none absolute inset-x-0 top-1/2 z-20 flex -translate-y-1/2 justify-center"
                    >
                        <span class="rounded-md bg-stone-900/75 px-3 py-1.5 text-xs font-medium text-white shadow-lg backdrop-blur-sm">
                            اسحب الصورة لإعادة التموضع
                        </span>
                    </div>

                    <div
                        v-if="repositioning"
                        class="absolute end-3 top-3 z-20 flex items-center gap-px overflow-hidden rounded-md bg-stone-900/80 text-xs font-medium text-white shadow-lg backdrop-blur-sm"
                        @pointerdown.stop
                    >
                        <button
                            type="button"
                            class="px-2.5 py-1.5 transition hover:bg-white/10"
                            @click.stop="saveReposition"
                        >
                            حفظ الموضع
                        </button>
                        <button
                            type="button"
                            class="border-s border-white/15 px-2.5 py-1.5 text-stone-300 transition hover:bg-white/10 hover:text-white"
                            @click.stop="cancelReposition"
                        >
                            إلغاء
                        </button>
                    </div>

                    <div
                        v-else
                        class="absolute end-3 top-3 z-10 flex items-center gap-px overflow-hidden rounded-md bg-stone-900/80 text-xs font-medium text-white shadow-lg backdrop-blur-sm"
                        @pointerdown.stop
                    >
                        <button
                            ref="changeBtn"
                            type="button"
                            class="px-2.5 py-1.5 transition hover:bg-white/10"
                            :class="menuOpen ? 'bg-white/15' : ''"
                            @click.stop="toggleMenu"
                        >
                            تغيير
                        </button>
                        <button
                            v-if="canReposition"
                            type="button"
                            class="border-s border-white/15 px-2.5 py-1.5 transition hover:bg-white/10"
                            @click.stop="startReposition"
                        >
                            إعادة التموضع
                        </button>
                        <button
                            v-if="hasCover"
                            type="button"
                            class="border-s border-white/15 px-2.5 py-1.5 text-rose-300 transition hover:bg-white/10 hover:text-rose-200"
                            @click.stop="removeCover"
                        >
                            إزالة
                        </button>
                    </div>

                    <input
                        ref="fileInput"
                        type="file"
                        accept="image/jpeg,image/png,image/webp,image/gif"
                        class="sr-only"
                        @change="onFileChange"
                    >
                </div>
            </div>
        </Field>

        <Teleport to="body">
            <div
                v-if="menuOpen && menuStyle"
                ref="menuEl"
                class="fixed z-[200] overflow-hidden rounded-xl border border-stone-700/80 bg-[#2f2f2f] text-stone-100 shadow-2xl"
                :style="menuStyle"
                @click.stop
            >
                <div class="flex items-center gap-1 border-b border-white/10 px-2">
                    <button
                        v-for="tab in [
                            { id: 'gallery', label: 'المعرض' },
                            { id: 'upload', label: 'رفع صورة' },
                            { id: 'unsplash', label: 'ألبوم الصور' },
                        ]"
                        :key="tab.id"
                        type="button"
                        class="-mb-px px-2.5 py-2.5 text-xs transition sm:text-sm"
                        :class="activeTab === tab.id
                            ? 'border-b-2 border-white font-medium text-white'
                            : 'border-b-2 border-transparent text-stone-400 hover:text-stone-200'"
                        @click="setTab(tab.id)"
                    >
                        {{ tab.label }}
                    </button>
                </div>

                <div class="max-h-80 overflow-y-auto p-3">
                    <div v-if="activeTab === 'gallery'" class="space-y-4">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-[11px] font-medium uppercase tracking-wide text-stone-500">ألوان</p>
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-1.5 rounded-md border border-stone-500/80 px-2 py-1 text-[11px] text-stone-200 transition hover:bg-white/5"
                                    @click="openCustomColorPicker"
                                >
                                    <span
                                        class="size-3.5 rounded-sm ring-1 ring-white/25"
                                        :style="{ background: customColor }"
                                    />
                                    تخصيص لون
                                </button>
                            </div>

                            <div
                                class="flex items-center gap-2 rounded-md border border-stone-600/80 bg-stone-900/50 p-2"
                            >
                                <input
                                    ref="customColorInput"
                                    type="color"
                                    :value="customColor"
                                    class="h-9 w-12 cursor-pointer rounded border border-stone-500 bg-transparent p-0.5"
                                    @pointerdown.stop
                                    @click.stop="pickingCustomColor = true"
                                    @input="onCustomColorInput"
                                    @change="onCustomColorChange"
                                    @blur="onCustomColorBlur"
                                >
                                <input
                                    type="text"
                                    :value="customColor"
                                    maxlength="7"
                                    dir="ltr"
                                    class="min-w-0 flex-1 rounded-md border border-stone-600 bg-stone-950/60 px-2 py-1.5 font-mono text-xs text-stone-100 placeholder:text-stone-500 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500"
                                    placeholder="#6366f1"
                                    @pointerdown.stop
                                    @focus="pickingCustomColor = true"
                                    @blur="onCustomColorBlur"
                                    @change="onCustomHexCommit"
                                >
                                <button
                                    type="button"
                                    class="shrink-0 rounded-md bg-white/10 px-2.5 py-1.5 text-[11px] font-medium text-white transition hover:bg-white/15"
                                    @click.stop="applyCustomColor"
                                >
                                    تطبيق
                                </button>
                            </div>
                            <div class="grid grid-cols-5 gap-2 sm:grid-cols-6">
                                <button
                                    v-for="color in COVER_COLORS"
                                    :key="color.id"
                                    type="button"
                                    class="aspect-[16/10] rounded-md ring-1 ring-white/10 transition hover:ring-2 hover:ring-white/40 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-400"
                                    :style="{ background: color.value }"
                                    :title="color.id"
                                    @click="selectColor(color)"
                                />
                                <button
                                    type="button"
                                    class="relative flex aspect-[16/10] items-center justify-center overflow-hidden rounded-md ring-1 ring-white/20 transition hover:ring-2 hover:ring-white/50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-400"
                                    title="تخصيص لون"
                                    @click="openCustomColorPicker"
                                >
                                    <span
                                        class="absolute inset-0 opacity-90"
                                        style="background: conic-gradient(#ef4444, #eab308, #22c55e, #06b6d4, #3b82f6, #a855f7, #ef4444)"
                                    />
                                    <span class="relative rounded bg-stone-950/70 px-1 py-0.5 text-[9px] font-medium text-white">
                                        مخصص
                                    </span>
                                </button>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <p class="text-[11px] font-medium uppercase tracking-wide text-stone-500">تدرجات</p>
                            <div class="grid grid-cols-4 gap-2 sm:grid-cols-5">
                                <button
                                    v-for="gradient in COVER_GRADIENTS"
                                    :key="gradient.id"
                                    type="button"
                                    class="aspect-[16/10] rounded-md ring-1 ring-white/10 transition hover:ring-2 hover:ring-white/40 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-400"
                                    :style="{ background: gradient.value }"
                                    :title="gradient.id"
                                    @click="selectGradient(gradient)"
                                />
                            </div>
                        </div>
                    </div>

                    <div v-else-if="activeTab === 'upload'" class="space-y-3 py-2 text-center">
                        <button
                            type="button"
                            class="w-full rounded-md border border-stone-500 px-4 py-2.5 text-sm text-stone-100 transition hover:bg-white/5"
                            @click="pickFile"
                        >
                            رفع ملف
                        </button>
                        <p class="text-xs text-stone-500">صور أعرض من 1500px تعطي أفضل نتيجة.</p>
                    </div>

                    <div v-else class="space-y-3">
                        <input
                            v-model="unsplashQuery"
                            type="search"
                            placeholder="ابحث عن صورة..."
                            class="w-full rounded-md border border-stone-600 bg-stone-900/60 px-3 py-2 text-sm text-stone-100 placeholder:text-stone-500 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500"
                            @input="onUnsplashQueryInput"
                        >

                        <p v-if="unsplashError" class="text-xs text-rose-300">{{ unsplashError }}</p>
                        <p v-else-if="unsplashLoading" class="py-6 text-center text-xs text-stone-500">جاري التحميل...</p>
                        <p v-else-if="!unsplashPhotos.length" class="py-6 text-center text-xs text-stone-500">لا توجد نتائج.</p>

                        <div v-else class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                            <button
                                v-for="photo in unsplashPhotos"
                                :key="photo.id"
                                type="button"
                                class="group/photo overflow-hidden rounded-md text-start disabled:opacity-60"
                                :disabled="unsplashSelecting"
                                @click="selectUnsplashPhoto(photo)"
                            >
                                <img
                                    :src="photo.thumb"
                                    :alt="photo.alt || ''"
                                    class="aspect-[16/10] w-full object-cover transition group-hover/photo:opacity-90"
                                    loading="lazy"
                                >
                                <span class="mt-1 block truncate text-[10px] text-stone-500">
                                    by
                                    <a
                                        v-if="photo.author_url"
                                        :href="photo.author_url"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="underline hover:text-stone-300"
                                        @click.stop
                                    >{{ photo.author }}</a>
                                    <template v-else>{{ photo.author }}</template>
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div
                v-if="cropOpen"
                class="fixed inset-0 z-[210] flex items-center justify-center p-4"
            >
                <div class="absolute inset-0 bg-stone-800/75" @click="closeCropper" />

                <div class="relative w-full max-w-lg rounded-xl bg-white shadow-xl" @click.stop>
                    <div class="flex items-center justify-between border-b border-stone-100 p-3 px-4">
                        <p class="text-sm font-semibold text-stone-600" dir="rtl">قص صورة الغلاف</p>
                        <button
                            type="button"
                            class="rounded-md bg-stone-100 p-1 text-stone-400 hover:bg-stone-200"
                            :disabled="cropping"
                            @click="closeCropper"
                        >
                            <Icon name="x" class="h-4 w-4" />
                        </button>
                    </div>

                    <div class="flex justify-center p-4">
                        <div ref="cropHost" class="min-h-[280px] min-w-[360px]" />
                    </div>

                    <div class="flex flex-wrap justify-end gap-2 border-t border-stone-100 p-3 px-4" dir="rtl">
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
