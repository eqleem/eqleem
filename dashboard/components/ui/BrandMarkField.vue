<script setup>
import {
    computed, nextTick, onBeforeUnmount, ref, watch,
} from 'vue';
import { createSimpleCrop } from '../../../resources/js/lib/image-crop-engine.js';
import Field from './Field.vue';
import Button from './Button.vue';
import Icon from './Icon.vue';
import { api } from '../../lib/api.js';
import {
    emojiCategories,
    emojiGroups,
    iconColors,
    skinTones,
} from '../../data/emojis.js';

const props = defineProps({
    modelValue: { type: Object, default: null },
    name: { type: String, default: 'brand_mark' },
    label: { type: String, default: 'الشعار' },
    info: { type: String, default: '' },
    error: { type: String, default: null },
    shape: { type: String, default: 'square' },
    outputSize: { type: Number, default: 512 },
    accept: { type: String, default: 'image/jpeg,image/png,image/webp,image/gif' },
});

const emit = defineEmits(['update:modelValue', 'change']);

const open = ref(false);
const tab = ref('emoji');
const filter = ref('');
const skinTone = ref('default');
const showSkinPicker = ref(false);
const showColorPicker = ref(false);
const activeEmojiCategory = ref('people');
const emojiScrollEl = ref(null);
const pickerRoot = ref(null);
const triggerEl = ref(null);
const pickerStyle = ref({ top: '20vh', left: '50%', transform: 'translateX(-50%)' });

const icons = ref([]);
const iconsPage = ref(1);
const iconsHasMore = ref(false);
const iconsLoading = ref(false);
const iconsQuery = ref('');
const iconColor = ref('');

const fileInput = ref(null);
const cropHost = ref(null);
const cropOpen = ref(false);
const cropping = ref(false);
const pendingSrc = ref(null);
const pendingFile = ref(null);
const cropInstance = ref(null);

const recentEmojis = ref(loadRecent('emoji'));
const recentIcons = ref(loadRecent('icon'));

let iconSearchTimer = null;

const current = computed(() => normalizeMark(props.modelValue));

const filteredEmojiGroups = computed(() => {
    const q = filter.value.trim().toLowerCase();
    const tone = skinTones.find((item) => item.id === skinTone.value)?.modifier ?? null;
    const result = {};

    for (const [key, list] of Object.entries(emojiGroups)) {
        if (key === 'recent') {
            continue;
        }

        let items = list.map((emoji) => applySkinTone(emoji, tone));

        if (q) {
            items = items.filter((emoji) => emoji.includes(q));
        }

        if (items.length) {
            result[key] = items;
        }
    }

    return result;
});

const visibleRecentEmojis = computed(() => {
    const tone = skinTones.find((item) => item.id === skinTone.value)?.modifier ?? null;
    const q = filter.value.trim().toLowerCase();

    return recentEmojis.value
        .map((emoji) => applySkinTone(emoji, tone))
        .filter((emoji) => !q || emoji.includes(q));
});

const previewStyle = computed(() => iconColorStyle(current.value.type === 'icon' ? current.value.color : null));

const activeSkinModifier = computed(
    () => skinTones.find((item) => item.id === skinTone.value)?.modifier ?? null,
);

watch(filter, (value) => {
    if (tab.value === 'icons') {
        scheduleIconSearch(value);
    }
});

watch(tab, (value) => {
    filter.value = '';
    showSkinPicker.value = false;
    showColorPicker.value = false;

    if (value === 'icons' && icons.value.length === 0) {
        loadIcons(true);
    }
});

watch(open, (value) => {
    if (value) {
        if (current.value.type === 'icon') {
            tab.value = 'icons';
            iconColor.value = current.value.color || '';
        } else if (current.value.type === 'image' && current.value.url) {
            tab.value = 'upload';
        } else if (current.value.type === 'emoji') {
            tab.value = 'emoji';
        } else {
            tab.value = 'emoji';
        }

        if (tab.value === 'icons') {
            loadIcons(true);
        }

        nextTick(() => {
            updatePickerPosition();
            document.addEventListener('mousedown', onOutsideClick);
            window.addEventListener('resize', updatePickerPosition);
            window.addEventListener('scroll', updatePickerPosition, true);
        });
    } else {
        document.removeEventListener('mousedown', onOutsideClick);
        window.removeEventListener('resize', updatePickerPosition);
        window.removeEventListener('scroll', updatePickerPosition, true);
        showSkinPicker.value = false;
        showColorPicker.value = false;
    }
});

onBeforeUnmount(() => {
    document.removeEventListener('mousedown', onOutsideClick);
    window.removeEventListener('resize', updatePickerPosition);
    window.removeEventListener('scroll', updatePickerPosition, true);
    teardownCropper();
    revokeIfBlob(current.value.url);
    clearTimeout(iconSearchTimer);
});

function normalizeMark(value) {
    if (!value || typeof value !== 'object') {
        return {
            type: null, value: '', color: '', url: null, file: null,
        };
    }

    return {
        type: value.type ?? null,
        value: value.value ?? '',
        color: typeof value.color === 'string' ? value.color : '',
        url: value.url ?? null,
        file: value.file ?? null,
    };
}

function iconColorStyle(color) {
    if (!color) {
        return {};
    }

    return { color };
}

function commit(next) {
    const mark = normalizeMark(next);
    emit('update:modelValue', mark);
    emit('change', mark);
}

function updatePickerPosition() {
    const el = triggerEl.value;

    if (!el) {
        return;
    }

    const rect = el.getBoundingClientRect();
    const width = 360;
    const left = Math.min(Math.max(12, rect.left), window.innerWidth - width - 12);
    const top = Math.min(rect.bottom + 8, window.innerHeight - 420);

    pickerStyle.value = {
        top: `${Math.max(12, top)}px`,
        left: `${left}px`,
    };
}

function onOutsideClick(event) {
    if (!pickerRoot.value || pickerRoot.value.contains(event.target) || triggerEl.value?.contains(event.target)) {
        return;
    }

    open.value = false;
}

function toggleOpen() {
    open.value = !open.value;
}

function selectEmoji(emoji) {
    pushRecent('emoji', stripSkinTone(emoji));
    recentEmojis.value = loadRecent('emoji');
    commit({
        type: 'emoji', value: emoji, color: '', url: null, file: null,
    });
    open.value = false;
}

function selectIcon(iconId) {
    pushRecent('icon', { id: iconId, color: iconColor.value });
    recentIcons.value = loadRecent('icon');
    commit({
        type: 'icon', value: iconId, color: iconColor.value, url: null, file: null,
    });
    open.value = false;
}

function selectIconColor(color) {
    iconColor.value = color;
    showColorPicker.value = false;

    if (current.value.type === 'icon' && current.value.value) {
        commit({ ...current.value, color });
        pushRecent('icon', { id: current.value.value, color });
        recentIcons.value = loadRecent('icon');
    }
}

function setSkinTone(id) {
    skinTone.value = id;
    showSkinPicker.value = false;
}

function emojiCategoryLabel(key) {
    const labels = {
        people: 'أشخاص',
        nature: 'طبيعة',
        food: 'طعام',
        activity: 'أنشطة',
        travel: 'سفر',
        objects: 'أشياء',
        symbols: 'رموز',
        flags: 'أعلام',
    };

    return labels[key] || emojiCategories.find((item) => item.id === key)?.label || key;
}

function removeMark() {
    commit({
        type: 'none', value: '', color: '', url: null, file: null,
    });
    open.value = false;
}

function randomEmoji() {
    const all = Object.values(filteredEmojiGroups.value).flat();

    if (!all.length) {
        return;
    }

    selectEmoji(all[Math.floor(Math.random() * all.length)]);
}

function randomIcon() {
    const pool = icons.value.length
        ? icons.value
        : recentIcons.value.map((item) => ({ id: item.id || item }));

    if (!pool.length) {
        return;
    }

    selectIcon(pool[Math.floor(Math.random() * pool.length)].id);
}

function scheduleIconSearch(value) {
    clearTimeout(iconSearchTimer);
    iconSearchTimer = setTimeout(() => {
        iconsQuery.value = value.trim();
        loadIcons(true);
    }, 220);
}

async function loadIcons(reset = false) {
    if (iconsLoading.value) {
        return;
    }

    if (reset) {
        iconsPage.value = 1;
        icons.value = [];
        iconsHasMore.value = false;
    }

    iconsLoading.value = true;

    try {
        const params = new URLSearchParams({
            page: String(iconsPage.value),
            per_page: '96',
        });

        if (iconsQuery.value) {
            params.set('q', iconsQuery.value);
        }

        const payload = await api(`/icons/tabler?${params.toString()}`);
        const batch = Array.isArray(payload?.data) ? payload.data : [];
        icons.value = reset ? batch : [...icons.value, ...batch];
        iconsHasMore.value = Boolean(payload?.meta?.has_more);
    } catch {
        if (reset) {
            icons.value = [];
            iconsHasMore.value = false;
        }
    } finally {
        iconsLoading.value = false;
    }
}

function onIconsScroll(event) {
    const el = event.target;

    if (!iconsHasMore.value || iconsLoading.value) {
        return;
    }

    if (el.scrollTop + el.clientHeight >= el.scrollHeight - 80) {
        iconsPage.value += 1;
        loadIcons(false);
    }
}

function scrollToEmojiCategory(id) {
    activeEmojiCategory.value = id;
    const target = emojiScrollEl.value?.querySelector(`[data-emoji-cat="${id}"]`);
    target?.scrollIntoView({ behavior: 'smooth', block: 'start' });
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

    const reader = new FileReader();
    reader.onload = (loadEvent) => {
        pendingSrc.value = loadEvent.target?.result ?? null;
        pendingFile.value = file;

        if (!pendingSrc.value) {
            return;
        }

        open.value = false;
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
    if (!cropOpen.value || !pendingSrc.value || !cropHost.value) {
        return;
    }

    teardownCropper();
    cropInstance.value = createSimpleCrop(cropHost.value, pendingSrc.value, {
        viewportSize: 220,
        containerSize: 320,
        shape: props.shape,
    });
}

function closeCropper() {
    cropOpen.value = false;
    pendingSrc.value = null;
    pendingFile.value = null;
    teardownCropper();
}

function useWithoutCrop() {
    if (!pendingFile.value || cropping.value) {
        return;
    }

    commitImage(pendingFile.value);
    closeCropper();
}

async function confirmCrop() {
    if (!cropInstance.value || cropping.value) {
        return;
    }

    cropping.value = true;

    try {
        const blob = await cropInstance.value.toBlob(props.outputSize, 'image/jpeg', 0.92);

        if (!blob) {
            throw new Error('Export failed');
        }

        commitImage(new File([blob], 'cropped.jpg', { type: 'image/jpeg' }));
        closeCropper();
    } catch {
        if (pendingFile.value) {
            commitImage(pendingFile.value);
            closeCropper();
        }
    } finally {
        cropping.value = false;
    }
}

function commitImage(file) {
    const url = URL.createObjectURL(file);
    const previous = current.value.url;

    commit({
        type: 'image', value: '', color: '', url, file,
    });
    revokeIfBlob(previous);
}

function revokeIfBlob(url) {
    if (url?.startsWith?.('blob:')) {
        URL.revokeObjectURL(url);
    }
}

function loadRecent(kind) {
    try {
        const raw = localStorage.getItem(`eqleem.brandMark.recent.${kind}`);
        const parsed = raw ? JSON.parse(raw) : [];

        return Array.isArray(parsed) ? parsed.slice(0, 24) : [];
    } catch {
        return [];
    }
}

function pushRecent(kind, item) {
    const key = `eqleem.brandMark.recent.${kind}`;
    const list = loadRecent(kind).filter((entry) => {
        if (kind === 'icon') {
            return (entry.id || entry) !== (item.id || item);
        }

        return entry !== item;
    });

    list.unshift(item);
    localStorage.setItem(key, JSON.stringify(list.slice(0, 24)));
}

function applySkinTone(emoji, modifier) {
    if (!modifier) {
        return emoji;
    }

    const base = normalizeEmojiBase(emoji);

    if (!SKIN_TONEABLE_EMOJI.has(base)) {
        return emoji;
    }

    return `${base}${modifier}`;
}

function stripSkinTone(emoji) {
    return String(emoji)
        .replace(/[\u{1F3FB}-\u{1F3FF}]/gu, '')
        .replace(/\uFE0F/g, '');
}

function normalizeEmojiBase(emoji) {
    return stripSkinTone(emoji);
}

const SKIN_TONEABLE_EMOJI = new Set([
    '👋', '🤚', '🖐', '✋', '🖖', '👌', '🤌', '🤏', '✌', '🤞', '🤟', '🤘', '🤙',
    '👈', '👉', '👆', '🖕', '👇', '☝', '👍', '👎', '✊', '👊', '🤛', '🤜', '👏',
    '🙌', '👐', '🤲', '🙏', '✍', '💅', '🤳', '💪', '🦵', '🦶', '👂', '👃',
    '👶', '🧒', '👦', '👧', '🧑', '👱', '👨', '🧔', '👩', '🧓', '👴', '👵',
    '🙍', '🙎', '🙅', '🙆', '💁', '🙋', '🧏', '🙇', '🤦', '🤷',
    '👮', '🕵', '💂', '👷', '🤴', '👸', '👳', '👲', '🧕', '🤵', '👰',
    '🤰', '🤱', '🎅', '🤶', '🦸', '🦹', '🧙', '🧚', '🧛', '🧜', '🧝',
    '💆', '💇', '🚶', '🧍', '🧎', '🏃', '💃', '🕺', '🕴',
    '🧗', '🏇', '🏂', '🏌', '🏄', '🚣', '🏊', '⛹', '🏋', '🚴', '🚵',
    '🤸', '🤽', '🤾', '🤹', '🧘', '🛀',
]);
</script>

<template>
    <div class="relative">
        <Field :name="name" :label="label" :info="info" :error="error">
            <div class="flex flex-1 items-center gap-3 p-2">
                <button
                    ref="triggerEl"
                    type="button"
                    class="flex size-20 shrink-0 cursor-pointer items-center justify-center overflow-hidden rounded-lg bg-transparent ring-1 ring-stone-200 transition hover:ring-primary-300"
                    @click="toggleOpen"
                >
                    <span
                        v-if="current.type === 'emoji'"
                        class="text-4xl leading-none"
                    >{{ current.value }}</span>
                    <iconify-icon
                        v-else-if="current.type === 'icon'"
                        :icon="current.value"
                        class="text-4xl"
                        :style="previewStyle"
                        stroke-width="1.5"
                    />
                    <img
                        v-else-if="current.url"
                        :src="current.url"
                        :alt="label || ''"
                        class="size-20 rounded-lg object-cover"
                    >
                    <span v-else class="text-xs text-stone-400">إضافة</span>
                </button>
            </div>
        </Field>

        <Teleport to="body">
            <div
                v-if="open"
                ref="pickerRoot"
                class="fixed z-[90] w-[360px] overflow-hidden rounded-xl border border-stone-700/80 bg-[#202020] text-stone-100 shadow-2xl"
                :style="pickerStyle"
                dir="rtl"
            >
                <div class="flex items-center justify-between gap-3 border-b border-white/10 px-3">
                    <div class="flex items-center gap-4 text-sm">
                        <button
                            v-for="item in [
                                { id: 'emoji', label: 'إيموجي' },
                                { id: 'icons', label: 'أيقونة' },
                                { id: 'upload', label: 'صورة' },
                            ]"
                            :key="item.id"
                            type="button"
                            class="relative py-2.5 transition"
                            :class="tab === item.id ? 'text-white' : 'text-stone-400 hover:text-stone-200'"
                            @click="tab = item.id"
                        >
                            {{ item.label }}
                            <span
                                v-if="tab === item.id"
                                class="absolute inset-x-0 -bottom-px h-0.5 rounded-full bg-white"
                            />
                        </button>
                    </div>
                    <button
                        type="button"
                        class="shrink-0 py-2.5 text-sm text-stone-400 hover:text-white"
                        @click="removeMark"
                    >
                        حذف
                    </button>
                </div>

                <div v-if="tab !== 'upload'" class="flex items-center gap-2 border-b border-white/5 p-2" dir="ltr">
                    <div class="relative min-w-0 flex-1">
                        <span class="pointer-events-none absolute inset-y-0 start-2 flex items-center text-stone-500">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <circle cx="11" cy="11" r="7" />
                                <path d="m20 20-3-3" />
                            </svg>
                        </span>
                        <input
                            v-model="filter"
                            type="search"
                            placeholder="تصفية..."
                            class="w-full rounded-md border-0 bg-[#2a2a2a] py-1.5 pe-2 ps-8 text-sm text-stone-100 outline-none ring-1 ring-transparent placeholder:text-stone-500 focus:ring-blue-500"
                        >
                    </div>
                    <button
                        type="button"
                        class="rounded-md bg-[#2a2a2a] p-1.5 text-stone-300 hover:bg-[#333] hover:text-white"
                        :title="tab === 'emoji' ? 'إيموجي عشوائي' : 'أيقونة عشوائية'"
                        @click="tab === 'emoji' ? randomEmoji() : randomIcon()"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M16 3h5v5M4 20 21 3M21 16v5h-5M15 15l6 6M4 4l5 5" />
                        </svg>
                    </button>
                    <div v-if="tab === 'emoji'" class="relative">
                        <button
                            type="button"
                            class="rounded-md bg-[#2a2a2a] px-2 py-1 text-lg leading-none hover:bg-[#333]"
                            @click="showSkinPicker = !showSkinPicker; showColorPicker = false"
                        >
                            {{ applySkinTone('✋', activeSkinModifier) }}
                        </button>
                        <div
                            v-if="showSkinPicker"
                            class="absolute end-0 top-full z-20 mt-1 flex items-center gap-1 rounded-lg border border-white/10 bg-[#2a2a2a] p-1.5 shadow-xl"
                        >
                            <button
                                v-for="tone in skinTones"
                                :key="tone.id"
                                type="button"
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md text-lg leading-none hover:bg-white/10"
                                :class="{ 'bg-white/15': skinTone === tone.id }"
                                @click="setSkinTone(tone.id)"
                            >
                                {{ applySkinTone('✋', tone.modifier) }}
                            </button>
                        </div>
                    </div>
                    <div v-else class="relative">
                        <button
                            type="button"
                            class="flex h-8 w-8 items-center justify-center rounded-md bg-[#2a2a2a] hover:bg-[#333]"
                            @click="showColorPicker = !showColorPicker; showSkinPicker = false"
                        >
                            <span
                                class="relative block h-4 w-4 overflow-hidden rounded-full border border-stone-400"
                                :style="iconColor ? { backgroundColor: iconColor } : { backgroundColor: 'transparent' }"
                            >
                                <span
                                    v-if="!iconColor"
                                    class="absolute inset-0 flex items-center justify-center text-[9px] font-semibold leading-none text-stone-200"
                                >A</span>
                            </span>
                        </button>
                        <div
                            v-if="showColorPicker"
                            class="absolute end-0 top-full z-20 mt-1 flex w-[148px] flex-wrap gap-2 rounded-lg border border-white/10 bg-[#2a2a2a] p-2 shadow-xl"
                        >
                            <button
                                v-for="color in iconColors"
                                :key="color || 'inherit'"
                                type="button"
                                class="relative block h-6 w-6 shrink-0 overflow-hidden rounded-full border hover:scale-110"
                                :class="[
                                    !color || color === '#FFFFFF' || color === '#000000' ? 'border-stone-400' : 'border-white/10',
                                    { 'outline outline-2 outline-offset-2 outline-blue-400': iconColor === color },
                                ]"
                                :style="color ? { backgroundColor: color } : { backgroundColor: 'transparent' }"
                                :aria-label="color || 'افتراضي (لون النص)'"
                                :title="color || 'افتراضي (لون النص)'"
                                @click="selectIconColor(color)"
                            >
                                <span
                                    v-if="!color"
                                    class="absolute inset-0 flex items-center justify-center text-[10px] font-semibold leading-none text-stone-200"
                                >A</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div v-if="tab === 'emoji'" class="flex h-[320px] flex-col">
                    <div ref="emojiScrollEl" class="min-h-0 flex-1 overflow-y-auto px-2 pb-2">
                        <div v-if="visibleRecentEmojis.length" class="pt-2">
                            <p class="mb-1 px-1 text-[11px] font-medium tracking-wide text-stone-500">الأخيرة</p>
                            <div class="grid grid-cols-10 gap-0.5">
                                <button
                                    v-for="emoji in visibleRecentEmojis"
                                    :key="`recent-${skinTone}-${emoji}`"
                                    type="button"
                                    class="flex size-8 items-center justify-center rounded-md text-xl hover:bg-white/10"
                                    @click="selectEmoji(emoji)"
                                >
                                    {{ emoji }}
                                </button>
                            </div>
                        </div>

                        <div
                            v-for="(items, key) in filteredEmojiGroups"
                            :key="`${key}-${skinTone}`"
                            :data-emoji-cat="key"
                            class="pt-3"
                        >
                            <p class="mb-1 px-1 text-[11px] font-medium tracking-wide text-stone-500">
                                {{ emojiCategoryLabel(key) }}
                            </p>
                            <div class="grid grid-cols-10 gap-0.5">
                                <button
                                    v-for="emoji in items"
                                    :key="`${key}-${skinTone}-${emoji}`"
                                    type="button"
                                    class="flex size-8 items-center justify-center rounded-md text-xl hover:bg-white/10"
                                    @click="selectEmoji(emoji)"
                                >
                                    {{ emoji }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-1 border-t border-white/10 px-2 py-1.5">
                        <button
                            v-for="cat in emojiCategories"
                            :key="cat.id"
                            type="button"
                            class="flex size-7 items-center justify-center rounded-md text-sm hover:bg-white/10"
                            :class="{ 'bg-white/15 ring-1 ring-blue-400': activeEmojiCategory === cat.id }"
                            :title="cat.label"
                            @click="scrollToEmojiCategory(cat.id)"
                        >
                            {{ cat.emoji }}
                        </button>
                    </div>
                </div>

                <div v-else-if="tab === 'icons'" class="flex h-[320px] flex-col">
                    <div
                        class="min-h-0 flex-1 overflow-y-auto px-2 pb-2"
                        @scroll="onIconsScroll"
                    >
                        <div v-if="recentIcons.length && !filter.trim()" class="pt-2">
                            <p class="mb-1 px-1 text-[11px] font-medium tracking-wide text-stone-500">الأخيرة</p>
                            <div class="grid grid-cols-10 gap-0.5">
                                <button
                                    v-for="item in recentIcons"
                                    :key="`recent-icon-${item.id || item}`"
                                    type="button"
                                    class="flex size-8 items-center justify-center rounded-md hover:bg-white/10"
                                    :class="{ 'bg-white/10': current.type === 'icon' && current.value === (item.id || item) }"
                                    @click="selectIcon(item.id || item)"
                                >
                                    <iconify-icon
                                        :icon="item.id || item"
                                        class="text-xl"
                                        :style="iconColorStyle(item.color ?? iconColor)"
                                        stroke-width="1.5"
                                    />
                                </button>
                            </div>
                        </div>

                        <div class="pt-3">
                            <p class="mb-1 px-1 text-[11px] font-medium tracking-wide text-stone-500">الأيقونات</p>
                            <div class="grid grid-cols-10 gap-0.5">
                                <button
                                    v-for="icon in icons"
                                    :key="icon.id"
                                    type="button"
                                    class="flex size-8 items-center justify-center rounded-md hover:bg-white/10"
                                    :class="{ 'bg-white/10': current.type === 'icon' && current.value === icon.id }"
                                    :title="icon.name"
                                    @click="selectIcon(icon.id)"
                                >
                                    <iconify-icon
                                        :icon="icon.id"
                                        class="text-xl"
                                        :style="iconColorStyle(iconColor)"
                                        stroke-width="1.5"
                                    />
                                </button>
                            </div>
                            <p v-if="iconsLoading" class="py-3 text-center text-xs text-stone-500">جاري التحميل…</p>
                            <p v-else-if="!icons.length" class="py-6 text-center text-xs text-stone-500">لا توجد أيقونات</p>
                        </div>
                    </div>
                </div>

                <div v-else class="flex h-[280px] flex-col items-center justify-center gap-3 px-6">
                    <button
                        type="button"
                        class="flex w-full items-center justify-center gap-2 rounded-lg bg-[#2a2a2a] px-4 py-3 text-sm font-medium text-stone-100 hover:bg-[#333]"
                        @click="pickFile"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <rect x="3" y="5" width="18" height="14" rx="2" />
                            <circle cx="8.5" cy="10.5" r="1.5" />
                            <path d="m21 15-5-5L5 21" />
                        </svg>
                        رفع صورة
                    </button>
                    <p class="text-center text-xs text-stone-500">الأفضل 280×280 · بحد أقصى 5MB</p>
                    <input
                        ref="fileInput"
                        type="file"
                        :accept="accept"
                        class="sr-only"
                        @change="onFileChange"
                    >
                </div>
            </div>
        </Teleport>

        <Teleport to="body">
            <div
                v-if="cropOpen"
                class="fixed inset-0 z-[100] flex items-center justify-center p-4"
            >
                <div class="absolute inset-0 bg-stone-800/75" @click="closeCropper" />
                <div class="relative w-full max-w-md rounded-xl bg-white shadow-xl" @click.stop>
                    <div class="flex items-center justify-between border-b border-stone-100 p-3 px-4">
                        <p class="text-sm font-semibold text-stone-600" dir="rtl">قص الشعار</p>
                        <button type="button" class="rounded-md bg-stone-100 p-1 text-stone-400 hover:bg-stone-200" :disabled="cropping" @click="closeCropper">
                            <Icon name="x" class="h-4 w-4" />
                        </button>
                    </div>
                    <div class="flex justify-center p-4">
                        <div ref="cropHost" class="min-h-[360px] min-w-[320px]" />
                    </div>
                    <div class="flex flex-wrap justify-end gap-2 border-t border-stone-100 p-3 px-4" dir="rtl">
                        <Button type="button" variant="ghost" label="إلغاء" :disabled="cropping" @click="closeCropper" />
                        <Button type="button" variant="secondary" label="استخدام بدون قص" :disabled="cropping" @click="useWithoutCrop" />
                        <Button type="button" label="تأكيد القص" :loading="cropping" @click="confirmCrop" />
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>
