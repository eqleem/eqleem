<script setup>
import { computed, inject, nextTick, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue';
import Form from '../../ui/Form.vue';
import Field from '../../ui/Field.vue';
import Input from '../../ui/Input.vue';
import Textarea from '../../ui/Textarea.vue';
import Toggle from '../../ui/Toggle.vue';
import Button from '../../ui/Button.vue';
import Icon from '../../ui/Icon.vue';
import BrandMarkField from '../../ui/BrandMarkField.vue';
import { usePageStructureStore } from '../../../stores/pageStructure.js';
import { api, ApiError } from '../../../lib/api.js';
import { notifyApiError } from '../../../lib/notify.js';

const props = defineProps({
    blockId: { type: Number, default: null },
    linkId: { type: Number, default: null },
    editor: { type: Object, required: true },
    mode: { type: String, default: 'block' },
    showDescription: { type: Boolean, default: true },
});

const emit = defineEmits(['saved', 'close']);
const store = usePageStructureStore();
const blockActions = inject('blockActions', null);
const isNestedLink = computed(() => props.mode === 'nested-link');
const isNew = computed(() => (isNestedLink.value ? !props.linkId : !props.blockId));

const pickerOptions = computed(() => props.editor.link_type_picker_options ?? []);

function parseInitialPickerKey(linkType) {
    const value = String(linkType ?? '');

    if (value === 'external' || value === 'booking') {
        return value;
    }

    if (value.startsWith('section:') || value.startsWith('item:')) {
        return value.slice(value.indexOf(':') + 1);
    }

    return '';
}

function parseInitialSpecificItem(linkType, option) {
    const value = String(linkType ?? '');

    if (value.startsWith('item:')) {
        return true;
    }

    // Section links (including catalog-managed ones) never pick a specific item.
    if (value.startsWith('section:')) {
        return false;
    }

    // New / unset link: default to item when the destination has no section route.
    if (option && !option.supports_section && option.supports_item) {
        return true;
    }

    return false;
}

function brandMarkFromEditor(editor) {
    const mark = editor?.brand_mark;

    if (mark && typeof mark === 'object' && mark.type) {
        return {
            type: mark.type,
            value: mark.value ?? '',
            color: mark.color ?? '',
            url: mark.type === 'image' ? (mark.url || null) : null,
            file: null,
        };
    }

    const icon = editor?.icon;
    if (typeof icon === 'string' && icon.trim()) {
        return {
            type: 'icon',
            value: icon,
            color: '',
            url: null,
            file: null,
        };
    }

    return {
        type: null,
        value: '',
        color: '',
        url: null,
        file: null,
    };
}

const initialPickerKey = parseInitialPickerKey(props.editor.link_type);
const initialOption = pickerOptions.value.find((option) => option.key === initialPickerKey) ?? null;
const isManagedSection = computed(() => Boolean(props.editor.managed_section));

const form = reactive({
    picker_key: initialPickerKey,
    specific_item: parseInitialSpecificItem(props.editor.link_type, initialOption),
    title: props.editor.title ?? '',
    description: props.editor.description ?? '',
    url: props.editor.url ?? '',
    content_id: props.editor.content_id ?? null,
    selected_content_title: props.editor.selected_content_title ?? '',
    branch_ids: [...(props.editor.branch_ids ?? [])].map((id) => Number(id)),
    calendar_ids: [...(props.editor.calendar_ids ?? [])].map((id) => Number(id)),
    allow_client_choice: props.editor.allow_client_choice ?? true,
    duration_minutes: Number(props.editor.duration_minutes ?? 30) || 30,
});

const brandMark = ref(brandMarkFromEditor(props.editor));
const useCustomCopy = ref(initialPickerKey === 'external' || initialPickerKey === 'booking');

const bookingTargets = computed(() => props.editor.booking_targets ?? { branches: [], calendars: [] });
const bookingBranches = computed(() => bookingTargets.value.branches ?? []);
const bookingCalendars = computed(() => bookingTargets.value.calendars ?? []);

const pickerOpen = ref(false);
const pickerQuery = ref('');
const pickerRoot = ref(null);
const pickerPanel = ref(null);
const pickerStyle = ref({});
const contentPickerOpen = ref(false);
const contentQuery = ref('');
const contentSearchRoot = ref(null);
const contentPickerPanel = ref(null);
const contentPickerStyle = ref({});
const contentResults = ref([]);
const contentSearching = ref(false);
const errors = reactive({});
const saving = ref(false);
let contentSearchTimer = null;

const selectedOption = computed(() => (
    pickerOptions.value.find((option) => option.key === form.picker_key) ?? null
));

const isExternal = computed(() => form.picker_key === 'external');
const isBooking = computed(() => form.picker_key === 'booking');
const hasLinkType = computed(() => Boolean(form.picker_key));
const canPickSpecificItem = computed(() => (
    Boolean(selectedOption.value?.supports_item)
));
const mustPickSpecificItem = computed(() => (
    Boolean(selectedOption.value)
    && !selectedOption.value.supports_section
    && selectedOption.value.supports_item
));
const needsContentPicker = computed(() => (
    hasLinkType.value
    && !isExternal.value
    && !isBooking.value
    && !isManagedSection.value
    && form.specific_item
    && canPickSpecificItem.value
));

const canSaveLink = computed(() => {
    if (!hasLinkType.value) {
        return false;
    }

    if (mustPickSpecificItem.value && !form.content_id) {
        return false;
    }

    if (isBooking.value && form.branch_ids.length === 0 && form.calendar_ids.length === 0) {
        return false;
    }

    return true;
});

const linkType = computed(() => {
    if (!form.picker_key) {
        return '';
    }

    if (form.picker_key === 'external' || form.picker_key === 'booking') {
        return form.picker_key;
    }

    if (isManagedSection.value) {
        return `section:${form.picker_key}`;
    }

    return form.specific_item && canPickSpecificItem.value
        ? `item:${form.picker_key}`
        : `section:${form.picker_key}`;
});

const contentOptions = computed(() => pickerOptions.value.filter((option) => option.group === 'content'));
const externalOption = computed(() => pickerOptions.value.find((option) => option.group === 'external') ?? null);
const bookingOption = computed(() => pickerOptions.value.find((option) => option.group === 'booking') ?? null);

const filteredContentOptions = computed(() => {
    const query = pickerQuery.value.trim().toLocaleLowerCase('ar');

    if (!query) {
        return contentOptions.value;
    }

    return contentOptions.value.filter((option) => (
        String(option.label).toLocaleLowerCase('ar').includes(query)
    ));
});

const selectedLabel = computed(() => selectedOption.value?.label ?? 'اختر نوع الرابط...');

const contentSelectedLabel = computed(() => (
    form.selected_content_title || 'اختر مادة...'
));

function applyDefaultCopy() {
    if (isExternal.value || isBooking.value) {
        return;
    }

    if (!selectedOption.value) {
        return;
    }

    if (form.specific_item && form.selected_content_title) {
        form.title = form.selected_content_title;
        if (props.showDescription) {
            form.description = selectedOption.value.item_description || '';
        }
        return;
    }

    form.title = selectedOption.value.section_title || selectedOption.value.label.replace(/^رابط\s+/, '');
    if (props.showDescription) {
        form.description = form.specific_item
            ? (selectedOption.value.item_description || '')
            : (selectedOption.value.section_description || '');
    }
}

watch(() => form.specific_item, async (enabled) => {
    form.content_id = null;
    form.selected_content_title = '';
    contentQuery.value = '';
    contentPickerOpen.value = false;
    contentResults.value = [];

    if (!useCustomCopy.value) {
        applyDefaultCopy();
    } else if (props.showDescription && enabled && selectedOption.value) {
        if (!form.description || form.description === selectedOption.value.section_description) {
            form.description = selectedOption.value.item_description || form.description;
        }
    } else if (props.showDescription && selectedOption.value) {
        if (!form.description || form.description === selectedOption.value.item_description) {
            form.description = selectedOption.value.section_description || '';
        }
    }

    if (enabled && selectedOption.value) {
        await nextTick();
        await searchContent();
    }
});

watch(contentQuery, () => {
    if (!needsContentPicker.value || !contentPickerOpen.value) {
        return;
    }

    clearTimeout(contentSearchTimer);
    contentSearchTimer = setTimeout(() => {
        searchContent();
    }, 200);
});

watch(mustPickSpecificItem, (required) => {
    if (required) {
        form.specific_item = true;
    }
});

watch(useCustomCopy, (enabled) => {
    if (!enabled && !isExternal.value && !isBooking.value) {
        applyDefaultCopy();
    }
});

onMounted(() => {
    document.addEventListener('mousedown', onDocumentClick);
    window.addEventListener('resize', updateOpenPickerPositions);
    window.addEventListener('scroll', updateOpenPickerPositions, true);

    if (needsContentPicker.value) {
        searchContent();
    }
});

onBeforeUnmount(() => {
    document.removeEventListener('mousedown', onDocumentClick);
    window.removeEventListener('resize', updateOpenPickerPositions);
    window.removeEventListener('scroll', updateOpenPickerPositions, true);
    clearTimeout(contentSearchTimer);
});

function updateFloatingStyle(triggerEl) {
    if (!triggerEl) {
        return {};
    }

    const rect = triggerEl.getBoundingClientRect();

    return {
        top: `${Math.round(rect.bottom + 4)}px`,
        left: `${Math.round(rect.left)}px`,
        width: `${Math.round(rect.width)}px`,
    };
}

function updateOpenPickerPositions() {
    if (pickerOpen.value) {
        pickerStyle.value = updateFloatingStyle(pickerRoot.value);
    }

    if (contentPickerOpen.value) {
        contentPickerStyle.value = updateFloatingStyle(contentSearchRoot.value);
    }
}

function onDocumentClick(event) {
    const target = event.target;

    if (
        pickerOpen.value
        && !pickerRoot.value?.contains(target)
        && !pickerPanel.value?.contains(target)
    ) {
        pickerOpen.value = false;
    }

    if (
        contentPickerOpen.value
        && !contentSearchRoot.value?.contains(target)
        && !contentPickerPanel.value?.contains(target)
    ) {
        contentPickerOpen.value = false;
    }
}

function openPicker() {
    contentPickerOpen.value = false;
    pickerOpen.value = true;
    pickerQuery.value = '';
    nextTick(() => {
        updateOpenPickerPositions();
        pickerPanel.value?.querySelector('input[data-picker-search]')?.focus();
    });
}

function openContentPicker() {
    pickerOpen.value = false;
    contentPickerOpen.value = true;
    contentQuery.value = '';
    nextTick(() => {
        updateOpenPickerPositions();
        contentPickerPanel.value?.querySelector('input[data-content-search]')?.focus();
        searchContent();
    });
}

function selectPickerOption(option) {
    form.picker_key = option.key;
    form.specific_item = Boolean(option.supports_item) && !option.supports_section;
    form.content_id = null;
    form.selected_content_title = '';
    form.url = '';
    form.branch_ids = [];
    form.calendar_ids = [];
    form.allow_client_choice = true;
    form.duration_minutes = 30;
    contentQuery.value = '';
    contentPickerOpen.value = false;
    contentResults.value = [];
    pickerOpen.value = false;
    pickerQuery.value = '';
    delete errors.content_id;
    delete errors.link_type;
    delete errors.branch_ids;
    delete errors.calendar_ids;
    delete errors.duration_minutes;

    if (option.key === 'external') {
        useCustomCopy.value = true;
        return;
    }

    if (option.key === 'booking') {
        useCustomCopy.value = true;
        form.title = option.section_title || 'حجز موعد';
        if (props.showDescription) {
            form.description = option.section_description || '';
        }
        return;
    }

    useCustomCopy.value = false;
    form.title = form.specific_item
        ? ''
        : (option.section_title || option.label.replace(/^رابط\s+/, ''));
    if (props.showDescription) {
        form.description = form.specific_item
            ? (option.item_description || '')
            : (option.section_description || '');
    }

    if (form.specific_item) {
        nextTick(() => {
            openContentPicker();
        });
    }
}

async function searchContent() {
    if (!needsContentPicker.value || !linkType.value.startsWith('item:')) {
        return;
    }

    const params = new URLSearchParams();
    params.set('link_type', linkType.value);

    const query = contentQuery.value.trim();
    if (query.length >= 1) {
        params.set('search', query);
    }

    contentSearching.value = true;

    try {
        const payload = await api(`/page/link-content?${params.toString()}`);
        contentResults.value = Array.isArray(payload?.data) ? payload.data : [];

        if (
            mustPickSpecificItem.value
            && !form.content_id
            && !query
            && !contentResults.value.length
        ) {
            errors.content_id = 'لا توجد مواد في هذا القسم. أنشئ محتوى أولاً ثم أعد المحاولة.';
        } else if (errors.content_id?.includes?.('لا توجد مواد')) {
            delete errors.content_id;
        }
    } catch {
        contentResults.value = [];
    } finally {
        contentSearching.value = false;
    }
}

function selectContent(item) {
    form.content_id = item.id;
    form.selected_content_title = item.title;
    contentQuery.value = '';
    contentPickerOpen.value = false;
    delete errors.content_id;

    if (!useCustomCopy.value || !form.title || form.title === selectedOption.value?.section_title) {
        form.title = item.title;
    }
}

function clearContent(event) {
    event?.stopPropagation();
    form.content_id = null;
    form.selected_content_title = '';
    contentQuery.value = '';

    if (!useCustomCopy.value) {
        applyDefaultCopy();
    }
}

function onSearchKeydown(event) {
    if (event.key === 'Enter') {
        event.preventDefault();
        event.stopPropagation();
    }

    if (event.key === 'Escape') {
        contentPickerOpen.value = false;
        pickerOpen.value = false;
    }
}

async function submit() {
    if (!hasLinkType.value || saving.value) {
        return;
    }

    Object.keys(errors).forEach((key) => delete errors[key]);

    if (isExternal.value && !form.url.trim()) {
        errors.url = 'أدخل الرابط الكامل.';
        return;
    }

    if (isBooking.value) {
        if (!form.title.trim()) {
            errors.title = 'أدخل عنوان زر الحجز.';
            return;
        }

        if (form.branch_ids.length === 0 && form.calendar_ids.length === 0) {
            errors.branch_ids = 'اختر فرعاً أو تقويماً واحداً على الأقل.';
            return;
        }

        if (!form.duration_minutes || form.duration_minutes < 5) {
            errors.duration_minutes = 'أدخل مدة الموعد بالدقائق (5 على الأقل).';
            return;
        }
    }

    if (mustPickSpecificItem.value && !form.content_id) {
        errors.content_id = contentResults.value.length
            ? 'اختر مادة من نتائج البحث.'
            : 'لا توجد مواد في هذا القسم. أنشئ محتوى أولاً ثم أعد المحاولة.';
        contentPickerOpen.value = true;
        await searchContent();
        return;
    }

    if (needsContentPicker.value && !form.content_id) {
        errors.content_id = 'اختر مادة من نتائج البحث.';
        contentPickerOpen.value = true;
        await searchContent();
        return;
    }

    saving.value = true;

    try {
        let payload;

        if (isNestedLink.value) {
            const body = new FormData();
            body.append('link_type', linkType.value);
            body.append('label', form.title ?? '');

            if (isExternal.value) {
                body.append('url', form.url ?? '');
            }

            if (isBooking.value) {
                form.branch_ids.forEach((id) => body.append('branch_ids[]', String(id)));
                form.calendar_ids.forEach((id) => body.append('calendar_ids[]', String(id)));
                body.append('allow_client_choice', form.allow_client_choice ? '1' : '0');
                body.append('duration_minutes', String(form.duration_minutes || 30));
            }

            if (needsContentPicker.value && form.content_id) {
                body.append('content_id', String(form.content_id));
            }

            const mark = brandMark.value ?? {};

            if (mark.type === 'image' && mark.file) {
                body.append('logo', mark.file);
                body.append('brand_mark_type', 'image');
            } else if (mark.type === 'emoji' || mark.type === 'icon') {
                body.append('brand_mark_type', mark.type);
                body.append('brand_mark_value', mark.value ?? '');
                if (mark.type === 'icon') {
                    body.append('brand_mark_color', mark.color ?? '');
                }
            } else if (mark.type === 'none') {
                body.append('brand_mark_type', 'none');
                body.append('remove_logo', '1');
            }

            const path = props.linkId
                ? `/page/blocks/${props.blockId}/links/${props.linkId}`
                : `/page/blocks/${props.blockId}/links`;

            payload = await api(path, {
                method: 'POST',
                body,
            });
        } else {
            const body = new FormData();
            body.append('link_type', linkType.value);
            body.append('title', form.title ?? '');
            if (props.showDescription) {
                body.append('description', form.description ?? '');
            }

            if (isExternal.value) {
                body.append('url', form.url ?? '');
            }

            if (isBooking.value) {
                form.branch_ids.forEach((id) => body.append('branch_ids[]', String(id)));
                form.calendar_ids.forEach((id) => body.append('calendar_ids[]', String(id)));
                body.append('allow_client_choice', form.allow_client_choice ? '1' : '0');
                body.append('duration_minutes', String(form.duration_minutes || 30));
            }

            if (needsContentPicker.value && form.content_id) {
                body.append('content_id', String(form.content_id));
            }

            const mark = brandMark.value ?? {};

            if (mark.type === 'image' && mark.file) {
                body.append('logo', mark.file);
                body.append('brand_mark_type', 'image');
            } else if (mark.type === 'emoji' || mark.type === 'icon') {
                body.append('brand_mark_type', mark.type);
                body.append('brand_mark_value', mark.value ?? '');
                if (mark.type === 'icon') {
                    body.append('brand_mark_color', mark.color ?? '');
                }
            } else if (mark.type === 'none') {
                body.append('brand_mark_type', 'none');
                body.append('remove_logo', '1');
            }

            if (isNew.value) {
                payload = await store.createBlock('block-link', body);
            } else {
                const updater = blockActions?.updateBlock
                    ?? ((id, fields) => store.updateBlock(id, fields));
                payload = await updater(props.blockId, body);
            }

            brandMark.value = brandMarkFromEditor(payload?.editor ?? props.editor);
        }

        emit('saved', payload);
    } catch (error) {
        if (error instanceof ApiError) {
            Object.assign(errors, Object.fromEntries(
                Object.entries(error.errors || {}).map(([key, value]) => [key, value?.[0] ?? null]),
            ));
            if (errors.label && !errors.title) {
                errors.title = errors.label;
            }
        }
        notifyApiError(error, 'تعذر الحفظ.');
    } finally {
        saving.value = false;
    }
}
</script>

<template>
    <Form class="!rounded-none !p-4" @submit="submit">
        <div class="space-y-2">
            <Field v-if="isNestedLink || isNew" name="link_type" label="نوع الرابط" :error="errors.link_type">
                <div ref="pickerRoot" class="relative w-full">
                    <button
                        type="button"
                        class="flex w-full cursor-pointer items-center gap-2 rounded-md border border-transparent bg-white px-3 py-2 text-start text-sm text-stone-700 transition hover:border-stone-200 focus:border-primary-400 focus:outline-none"
                        @click="pickerOpen ? (pickerOpen = false) : openPicker()"
                    >
                        <img
                            v-if="selectedOption"
                            :src="selectedOption.icon_url"
                            alt=""
                            class="h-5 w-5 shrink-0 rounded bg-stone-50 p-0.5"
                        >
                        <Icon v-else name="link" class="h-4 w-4 shrink-0 text-stone-300" />
                        <span class="min-w-0 flex-1 truncate" :class="selectedOption ? 'text-stone-800' : 'text-stone-400'">
                            {{ selectedLabel }}
                        </span>
                        <Icon name="chevron-down" class="h-4 w-4 shrink-0 text-stone-400" />
                    </button>

                    <Teleport to="body">
                        <div
                            v-if="pickerOpen"
                            ref="pickerPanel"
                            class="fixed z-[100] overflow-hidden overscroll-contain rounded-lg border border-stone-200 bg-white shadow-lg"
                            :style="pickerStyle"
                        >
                            <div class="border-b border-stone-100 p-2">
                                <input
                                    v-model="pickerQuery"
                                    data-picker-search
                                    type="search"
                                    placeholder="ابحث عن نوع المحتوى..."
                                    class="w-full rounded-md border border-stone-100 bg-stone-50 px-3 py-2 text-sm text-stone-700 placeholder:text-stone-400 focus:border-primary-400 focus:outline-none"
                                    @keydown="onSearchKeydown"
                                >
                            </div>

                            <ul class="max-h-56 overflow-y-auto overscroll-contain p-1">
                                <li v-for="option in filteredContentOptions" :key="option.key">
                                    <button
                                        type="button"
                                        class="flex w-full cursor-pointer items-center gap-2 rounded-md px-2 py-2 text-start text-sm transition hover:bg-stone-50"
                                        :class="{ 'bg-primary-50 text-primary-700': form.picker_key === option.key }"
                                        @click="selectPickerOption(option)"
                                    >
                                        <img :src="option.icon_url" alt="" class="h-6 w-6 shrink-0 rounded-md bg-stone-100 p-1">
                                        <span class="truncate font-medium">{{ option.label }}</span>
                                    </button>
                                </li>

                                <li v-if="!filteredContentOptions.length" class="px-3 py-4 text-center text-xs text-stone-400">
                                    لا توجد نتائج مطابقة
                                </li>
                            </ul>

                            <template v-if="(bookingOption || externalOption) && !pickerQuery.trim()">
                                <div class="mx-2 border-t border-dotted border-stone-200" />
                                <div class="p-1">
                                    <button
                                        v-if="bookingOption"
                                        type="button"
                                        class="flex w-full cursor-pointer items-center gap-2 rounded-md px-2 py-2 text-start text-sm transition hover:bg-stone-50"
                                        :class="{ 'bg-primary-50 text-primary-700': form.picker_key === 'booking' }"
                                        @click="selectPickerOption(bookingOption)"
                                    >
                                        <img :src="bookingOption.icon_url" alt="" class="h-6 w-6 shrink-0 rounded-md bg-stone-100 p-1">
                                        <span class="truncate font-medium">{{ bookingOption.label }}</span>
                                    </button>
                                    <button
                                        v-if="externalOption"
                                        type="button"
                                        class="flex w-full cursor-pointer items-center gap-2 rounded-md px-2 py-2 text-start text-sm transition hover:bg-stone-50"
                                        :class="{ 'bg-primary-50 text-primary-700': form.picker_key === 'external' }"
                                        @click="selectPickerOption(externalOption)"
                                    >
                                        <img :src="externalOption.icon_url" alt="" class="h-6 w-6 shrink-0 rounded-md bg-stone-100 p-1">
                                        <span class="truncate font-medium">{{ externalOption.label }}</span>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </Teleport>
                </div>
            </Field>

            <template v-if="hasLinkType">
                <Input
                    v-if="isExternal"
                    v-model="form.url"
                    name="url"
                    label="الرابط"
                    placeholder="https://..."
                    dir="ltr"
                    :error="errors.url"
                />

                <template v-if="isBooking">
                    <Field
                        name="branch_ids"
                        label="الفروع"
                        :error="errors.branch_ids"
                        info="اختر الفروع التي يمكن الحجز فيها"
                    >
                        <div class="max-h-40 space-y-1 overflow-y-auto rounded-md border border-stone-100 bg-white p-2">
                            <p v-if="!bookingBranches.length" class="px-1 py-2 text-xs text-stone-400">لا توجد فروع نشطة.</p>
                            <label
                                v-for="branch in bookingBranches"
                                :key="`branch-${branch.id}`"
                                class="flex cursor-pointer items-center gap-2 rounded-md px-2 py-1.5 text-sm text-stone-700 hover:bg-stone-50"
                            >
                                <input
                                    v-model="form.branch_ids"
                                    type="checkbox"
                                    class="rounded border-stone-300 text-primary-600 focus:ring-primary-500"
                                    :value="branch.id"
                                >
                                <span class="min-w-0 truncate">{{ branch.name }}<span v-if="branch.city" class="text-stone-400"> · {{ branch.city }}</span></span>
                            </label>
                        </div>
                    </Field>

                    <Field
                        name="calendar_ids"
                        label="التقاويم"
                        :error="errors.calendar_ids"
                        info="اختر تقاويم محددة بالإضافة إلى الفروع أو بدلًا منها"
                    >
                        <div class="max-h-40 space-y-1 overflow-y-auto rounded-md border border-stone-100 bg-white p-2">
                            <p v-if="!bookingCalendars.length" class="px-1 py-2 text-xs text-stone-400">لا توجد تقاويم نشطة.</p>
                            <label
                                v-for="calendar in bookingCalendars"
                                :key="`calendar-${calendar.id}`"
                                class="flex cursor-pointer items-center gap-2 rounded-md px-2 py-1.5 text-sm text-stone-700 hover:bg-stone-50"
                            >
                                <input
                                    v-model="form.calendar_ids"
                                    type="checkbox"
                                    class="rounded border-stone-300 text-primary-600 focus:ring-primary-500"
                                    :value="calendar.id"
                                >
                                <span class="min-w-0 truncate">
                                    {{ calendar.name }}
                                    <span v-if="calendar.branch_name" class="text-stone-400"> · {{ calendar.branch_name }}</span>
                                </span>
                            </label>
                        </div>
                    </Field>

                    <Toggle
                        v-model="form.allow_client_choice"
                        name="allow_client_choice"
                        label="السماح للعميل باختيار الفرع أو التقويم"
                        info="عند الإيقاف يختار النظام أقرب تقويم متاح تلقائياً"
                    />

                    <Input
                        v-model.number="form.duration_minutes"
                        name="duration_minutes"
                        type="number"
                        label="مدة الموعد (دقائق)"
                        min="5"
                        max="480"
                        :error="errors.duration_minutes"
                    />
                </template>

                <Toggle
                    v-if="isNew && !isManagedSection && canPickSpecificItem && !isExternal && !isBooking && !mustPickSpecificItem"
                    v-model="form.specific_item"
                    name="specific_item"
                    label="مادة محددة"
                    info="فعّل لاختيار مادة داخل القسم بدل رابط القسم نفسه"
                />

                <Field
                    v-if="needsContentPicker"
                    name="content_id"
                    :label="mustPickSpecificItem ? 'اختر المادة' : 'المادة'"
                    :error="errors.content_id"
                >
                    <div ref="contentSearchRoot" class="relative w-full">
                        <button
                            type="button"
                            class="flex w-full cursor-pointer items-center gap-2 rounded-md border bg-white px-3 py-2 text-start text-sm transition hover:border-stone-200 focus:border-primary-400 focus:outline-none"
                            :class="errors.content_id ? 'border-red-300' : 'border-transparent'"
                            :aria-expanded="contentPickerOpen"
                            aria-haspopup="listbox"
                            @click="contentPickerOpen ? (contentPickerOpen = false) : openContentPicker()"
                        >
                            <span
                                v-if="form.content_id && form.selected_content_title"
                                class="inline-flex min-w-0 max-w-full items-center gap-1.5 rounded-md bg-primary-50 px-2 py-0.5 text-sm text-primary-800"
                            >
                                <span class="min-w-0 truncate">{{ form.selected_content_title }}</span>
                                <button
                                    type="button"
                                    class="shrink-0 cursor-pointer rounded text-primary-500 transition hover:text-primary-700"
                                    aria-label="إزالة المادة"
                                    @click="clearContent"
                                >
                                    <Icon name="x" class="h-3.5 w-3.5" />
                                </button>
                            </span>
                            <span
                                v-else
                                class="min-w-0 flex-1 truncate text-stone-400"
                            >
                                {{ contentSelectedLabel }}
                            </span>
                            <Icon name="chevron-down" class="ms-auto h-4 w-4 shrink-0 text-stone-400" />
                        </button>

                        <Teleport to="body">
                            <div
                                v-if="contentPickerOpen"
                                ref="contentPickerPanel"
                                class="fixed z-[100] overflow-hidden overscroll-contain rounded-lg border border-stone-200 bg-white shadow-lg"
                                :style="contentPickerStyle"
                            >
                                <div
                                    v-if="form.content_id && form.selected_content_title"
                                    class="flex items-center gap-2 border-b border-stone-100 px-3 py-2"
                                >
                                    <span class="inline-flex min-w-0 max-w-full items-center gap-1.5 rounded-md bg-primary-50 px-2 py-0.5 text-sm text-primary-800">
                                        <span class="min-w-0 truncate">{{ form.selected_content_title }}</span>
                                        <button
                                            type="button"
                                            class="shrink-0 cursor-pointer rounded text-primary-500 transition hover:text-primary-700"
                                            aria-label="إزالة المادة"
                                            @click="clearContent"
                                        >
                                            <Icon name="x" class="h-3.5 w-3.5" />
                                        </button>
                                    </span>
                                </div>

                                <div class="border-b border-stone-100 p-2">
                                    <input
                                        v-model="contentQuery"
                                        data-content-search
                                        type="search"
                                        autocomplete="off"
                                        placeholder="ابحث عن مادة..."
                                        class="w-full rounded-md border border-stone-100 bg-stone-50 px-3 py-2 text-sm text-stone-700 placeholder:text-stone-400 focus:border-primary-400 focus:outline-none"
                                        @keydown="onSearchKeydown"
                                        @click.stop
                                    >
                                </div>

                                <ul class="max-h-48 overflow-y-auto overscroll-contain p-1" role="listbox">
                                    <li v-if="contentSearching" class="px-3 py-3 text-center text-xs text-stone-400">
                                        جاري البحث...
                                    </li>
                                    <template v-else>
                                        <li v-for="item in contentResults" :key="item.id">
                                            <button
                                                type="button"
                                                class="w-full cursor-pointer rounded-md px-3 py-2 text-start text-sm transition hover:bg-stone-50"
                                                :class="{ 'bg-primary-50 text-primary-700': form.content_id === item.id }"
                                                @click="selectContent(item)"
                                            >
                                                {{ item.title }}
                                            </button>
                                        </li>
                                        <li
                                            v-if="!contentResults.length"
                                            class="px-3 py-4 text-center text-xs text-stone-400"
                                        >
                                            {{ contentQuery.trim()
                                                ? 'لا توجد نتائج.'
                                                : (mustPickSpecificItem
                                                    ? 'لا توجد مواد في هذا القسم. أنشئ محتوى أولاً.'
                                                    : 'أحدث المواد — أو اكتب للبحث.') }}
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </Teleport>
                    </div>
                </Field>

                <Toggle
                    v-model="useCustomCopy"
                    name="use_custom_copy"
                    :label="showDescription ? 'عنوان ووصف مخصص' : 'عنوان مخصص'"
                    :info="showDescription ? 'فعّل لتعديل العنوان والوصف يدوياً' : 'فعّل لتعديل العنوان يدوياً'"
                />

                <template v-if="useCustomCopy">
                    <Input v-model="form.title" name="title" label="العنوان" :error="errors.title" />
                    <Textarea
                        v-if="showDescription"
                        v-model="form.description"
                        name="description"
                        label="الوصف"
                        
                        :error="errors.description"
                    />
                </template>

                <BrandMarkField
                    v-model="brandMark"
                    name="logo"
                    label="الأيقونة"
                    pick-label="اختيار أيقونة"
                    :error="errors.logo || errors.brand_mark_value || errors.brand_mark_type"
                />
            </template>
        </div>

        <template #footer>
            <div class="flex items-center gap-2">
                <Button type="button" variant="ghost" label="إلغاء" :disabled="saving" @click="emit('close')" />
                <Button type="submit" label="حفظ" :loading="saving" :disabled="!canSaveLink" />
            </div>
        </template>
    </Form>
</template>
