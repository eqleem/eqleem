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
    editor: { type: Object, required: true },
});

const emit = defineEmits(['saved', 'close']);
const store = usePageStructureStore();
const blockActions = inject('blockActions', null);
const isNew = computed(() => !props.blockId);

const pickerOptions = computed(() => props.editor.link_type_picker_options ?? []);

function parseInitialPickerKey(linkType) {
    const value = String(linkType ?? '');

    if (value === 'external') {
        return 'external';
    }

    if (value.startsWith('section:') || value.startsWith('item:')) {
        return value.slice(value.indexOf(':') + 1);
    }

    return '';
}

function parseInitialSpecificItem(linkType, option) {
    if (String(linkType ?? '').startsWith('item:')) {
        return true;
    }

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

const form = reactive({
    picker_key: initialPickerKey,
    specific_item: parseInitialSpecificItem(props.editor.link_type, initialOption),
    title: props.editor.title ?? '',
    description: props.editor.description ?? '',
    url: props.editor.url ?? '',
    content_id: props.editor.content_id ?? null,
    content_search: props.editor.selected_content_title ?? '',
    selected_content_title: props.editor.selected_content_title ?? '',
});

const brandMark = ref(brandMarkFromEditor(props.editor));

const pickerOpen = ref(false);
const pickerQuery = ref('');
const pickerRoot = ref(null);
const contentSearchRoot = ref(null);
const contentResults = ref([]);
const showContentResults = ref(false);
const contentSearching = ref(false);
const errors = reactive({});
const saving = ref(false);
let contentSearchTimer = null;

const selectedOption = computed(() => (
    pickerOptions.value.find((option) => option.key === form.picker_key) ?? null
));

const isExternal = computed(() => form.picker_key === 'external');
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
    && form.specific_item
    && canPickSpecificItem.value
));

const linkType = computed(() => {
    if (!form.picker_key) {
        return '';
    }

    if (form.picker_key === 'external') {
        return 'external';
    }

    return form.specific_item && canPickSpecificItem.value
        ? `item:${form.picker_key}`
        : `section:${form.picker_key}`;
});

const contentOptions = computed(() => pickerOptions.value.filter((option) => option.group === 'content'));
const externalOption = computed(() => pickerOptions.value.find((option) => option.group === 'external') ?? null);

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

watch(() => form.specific_item, async (enabled) => {
    form.content_id = null;
    form.content_search = '';
    form.selected_content_title = '';
    showContentResults.value = false;
    contentResults.value = [];

    if (enabled && selectedOption.value) {
        if (!form.description || form.description === selectedOption.value.section_description) {
            form.description = selectedOption.value.item_description || form.description;
        }

        await nextTick();
        await searchContent();
    } else if (selectedOption.value) {
        if (!form.description || form.description === selectedOption.value.item_description) {
            form.description = selectedOption.value.section_description || '';
        }
    }
});

watch(() => form.content_search, (value) => {
    if (!needsContentPicker.value) {
        return;
    }

    // Keep selection if the field still matches the chosen title.
    if (form.content_id && value !== form.selected_content_title) {
        form.content_id = null;
        form.selected_content_title = '';
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

onMounted(() => {
    document.addEventListener('click', onDocumentClick);

    if (needsContentPicker.value) {
        searchContent();
    }
});

onBeforeUnmount(() => {
    document.removeEventListener('click', onDocumentClick);
    clearTimeout(contentSearchTimer);
});

function onDocumentClick(event) {
    if (pickerRoot.value && !pickerRoot.value.contains(event.target)) {
        pickerOpen.value = false;
    }

    if (contentSearchRoot.value && !contentSearchRoot.value.contains(event.target)) {
        showContentResults.value = false;
    }
}

function openPicker() {
    pickerOpen.value = true;
    pickerQuery.value = '';
    nextTick(() => {
        pickerRoot.value?.querySelector('input[data-picker-search]')?.focus();
    });
}

function selectPickerOption(option) {
    form.picker_key = option.key;
    form.specific_item = !option.supports_section && option.supports_item;
    form.content_id = null;
    form.content_search = '';
    form.selected_content_title = '';
    form.url = '';
    showContentResults.value = false;
    contentResults.value = [];
    pickerOpen.value = false;
    pickerQuery.value = '';

    if (option.key === 'external') {
        form.title = form.title || '';
        form.description = form.description || '';
        return;
    }

    form.title = option.section_title || option.label.replace(/^رابط\s+/, '');
    form.description = form.specific_item
        ? (option.item_description || '')
        : (option.section_description || '');
}

async function searchContent() {
    if (!needsContentPicker.value || !linkType.value.startsWith('item:')) {
        return;
    }

    const params = new URLSearchParams();
    params.set('link_type', linkType.value);

    const query = form.content_search.trim();
    if (query.length >= 1) {
        params.set('search', query);
    }

    contentSearching.value = true;

    try {
        const payload = await api(`/page/link-content?${params.toString()}`);
        contentResults.value = Array.isArray(payload?.data) ? payload.data : [];
        showContentResults.value = true;
    } catch {
        contentResults.value = [];
        showContentResults.value = true;
    } finally {
        contentSearching.value = false;
    }
}

function selectContent(item) {
    form.content_id = item.id;
    form.selected_content_title = item.title;
    form.content_search = item.title;
    showContentResults.value = false;
    delete errors.content_id;

    if (!form.title || form.title === selectedOption.value?.section_title) {
        form.title = item.title;
    }
}

function onSearchKeydown(event) {
    if (event.key === 'Enter') {
        event.preventDefault();
        event.stopPropagation();
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

    if (needsContentPicker.value && !form.content_id) {
        errors.content_id = 'اختر مادة من نتائج البحث.';
        showContentResults.value = true;
        await searchContent();
        return;
    }

    saving.value = true;

    const body = new FormData();
    body.append('link_type', linkType.value);
    body.append('title', form.title ?? '');
    body.append('description', form.description ?? '');

    if (isExternal.value) {
        body.append('url', form.url ?? '');
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

    try {
        let payload;

        if (isNew.value) {
            payload = await store.createBlock('block-link', body);
        } else {
            const updater = blockActions?.updateBlock
                ?? ((id, fields) => store.updateBlock(id, fields));
            payload = await updater(props.blockId, body);
        }

        brandMark.value = brandMarkFromEditor(payload?.editor ?? props.editor);
        emit('saved', payload);
    } catch (error) {
        if (error instanceof ApiError) {
            Object.assign(errors, Object.fromEntries(
                Object.entries(error.errors || {}).map(([key, value]) => [key, value?.[0] ?? null]),
            ));
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
            <Field name="link_type" label="نوع الرابط" :error="errors.link_type">
                <div ref="pickerRoot" class="relative w-full">
                    <button
                        type="button"
                        class="flex w-full items-center gap-2 rounded-md border border-transparent bg-white px-3 py-2 text-start text-sm text-stone-700 transition hover:border-stone-200 focus:border-primary-400 focus:outline-none"
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

                    <div
                        v-if="pickerOpen"
                        class="absolute inset-x-0 z-50 mt-1 overflow-hidden rounded-lg border border-stone-200 bg-white shadow-lg"
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

                        <ul class="max-h-56 overflow-y-auto p-1">
                            <li v-for="option in filteredContentOptions" :key="option.key">
                                <button
                                    type="button"
                                    class="flex w-full items-center gap-2 rounded-md px-2 py-2 text-start text-sm transition hover:bg-stone-50"
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

                        <template v-if="externalOption && !pickerQuery.trim()">
                            <div class="mx-2 border-t border-dotted border-stone-200" />
                            <div class="p-1">
                                <button
                                    type="button"
                                    class="flex w-full items-center gap-2 rounded-md px-2 py-2 text-start text-sm transition hover:bg-stone-50"
                                    :class="{ 'bg-primary-50 text-primary-700': form.picker_key === 'external' }"
                                    @click="selectPickerOption(externalOption)"
                                >
                                    <img :src="externalOption.icon_url" alt="" class="h-6 w-6 shrink-0 rounded-md bg-stone-100 p-1">
                                    <span class="truncate font-medium">{{ externalOption.label }}</span>
                                </button>
                            </div>
                        </template>
                    </div>
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

                <Toggle
                    v-if="canPickSpecificItem && !isExternal"
                    v-model="form.specific_item"
                    name="specific_item"
                    label="مادة محددة"
                    :info="mustPickSpecificItem ? 'هذا النوع يتطلب اختيار مادة محددة' : 'فعّل لاختيار مادة داخل القسم بدل رابط القسم نفسه'"
                    :disabled="mustPickSpecificItem"
                />

                <div v-if="needsContentPicker" ref="contentSearchRoot" class="space-y-2">
                    <Field name="content_search" label="بحث المحتوى" :error="errors.content_id">
                        <div class="relative w-full">
                            <input
                                v-model="form.content_search"
                                type="search"
                                autocomplete="off"
                                placeholder="اكتب حرفاً أو أكثر للبحث..."
                                class="block w-full rounded-md border-2 bg-white px-3 py-1.5 text-sm text-stone-600 placeholder:text-sm focus:border-primary-500 focus:bg-stone-100/50 focus:text-stone-700 focus:outline-none"
                                :class="errors.content_id ? 'border-red-300' : 'border-transparent'"
                                @focus="searchContent"
                                @keydown="onSearchKeydown"
                            >
                            <p v-if="contentSearching" class="mt-1 text-xs text-stone-400">جاري البحث...</p>
                        </div>
                    </Field>

                    <ul
                        v-if="showContentResults && contentResults.length"
                        class="max-h-40 overflow-y-auto rounded-lg border border-stone-100 bg-white"
                    >
                        <li v-for="item in contentResults" :key="item.id">
                            <button
                                type="button"
                                class="w-full px-3 py-2 text-start text-sm transition hover:bg-stone-50"
                                :class="{ 'bg-primary-50 text-primary-700': form.content_id === item.id }"
                                @click="selectContent(item)"
                            >
                                {{ item.title }}
                            </button>
                        </li>
                    </ul>
                    <p
                        v-else-if="showContentResults && !contentSearching && form.content_search.trim().length >= 1"
                        class="text-xs text-stone-400"
                    >
                        لا توجد نتائج.
                    </p>
                    <p v-else-if="showContentResults && !contentSearching && !form.content_search.trim()" class="text-xs text-stone-400">
                        أحدث المواد — أو اكتب للبحث.
                    </p>
                    <p v-if="form.selected_content_title" class="text-xs text-stone-400">المحدد: {{ form.selected_content_title }}</p>
                </div>

                <Input v-model="form.title" name="title" label="العنوان" :error="errors.title" />
                <Textarea v-model="form.description" name="description" label="الوصف" :rows="3" :error="errors.description" />

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
                <Button type="submit" label="حفظ" :loading="saving" :disabled="!hasLinkType" />
            </div>
        </template>
    </Form>
</template>
