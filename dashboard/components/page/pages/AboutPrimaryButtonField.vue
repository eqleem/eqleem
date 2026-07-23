<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue';
import Field from '../../ui/Field.vue';
import Input from '../../ui/Input.vue';
import Toggle from '../../ui/Toggle.vue';
import Icon from '../../ui/Icon.vue';
import { api } from '../../../lib/api.js';

const props = defineProps({
    modelValue: { type: Object, default: () => ({}) },
    pickerOptions: { type: Array, default: () => [] },
    bookingTargets: { type: Object, default: () => ({ branches: [], calendars: [] }) },
});

const emit = defineEmits(['update:modelValue']);

function parsePickerKey(linkType) {
    const value = String(linkType ?? '');

    if (value === 'external' || value === 'booking') {
        return value;
    }

    if (value.startsWith('section:') || value.startsWith('item:')) {
        return value.slice(value.indexOf(':') + 1);
    }

    return '';
}

function parseSpecificItem(linkType, option) {
    const value = String(linkType ?? '');

    if (value.startsWith('item:')) {
        return true;
    }

    if (value.startsWith('section:')) {
        return false;
    }

    if (option && !option.supports_section && option.supports_item) {
        return true;
    }

    return false;
}

function emptyButton() {
    return {
        label: '',
        link_type: 'external',
        content_id: null,
        selected_content_title: '',
        url: '',
        branch_ids: [],
        calendar_ids: [],
        allow_client_choice: true,
        duration_minutes: 30,
    };
}

const form = reactive({
    ...emptyButton(),
    picker_key: '',
    specific_item: false,
});

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
let contentSearchTimer = null;
let syncing = false;

function buttonSnapshot(value) {
    const source = value && typeof value === 'object' ? value : {};

    return JSON.stringify({
        label: source.label ?? '',
        link_type: source.link_type ?? 'external',
        content_id: source.content_id ?? null,
        selected_content_title: source.selected_content_title ?? '',
        url: source.url ?? '',
        branch_ids: [...(source.branch_ids ?? [])].map((id) => Number(id)),
        calendar_ids: [...(source.calendar_ids ?? [])].map((id) => Number(id)),
        allow_client_choice: source.allow_client_choice ?? true,
        duration_minutes: Number(source.duration_minutes ?? 30) || 30,
    });
}

function applyModel(value) {
    const source = value && typeof value === 'object' ? value : {};
    const pickerKey = parsePickerKey(source.link_type);
    const option = props.pickerOptions.find((item) => item.key === pickerKey) ?? null;

    syncing = true;
    form.label = source.label ?? '';
    form.link_type = source.link_type ?? 'external';
    form.content_id = source.content_id ?? null;
    form.selected_content_title = source.selected_content_title ?? '';
    form.url = source.url ?? '';
    form.branch_ids = [...(source.branch_ids ?? [])].map((id) => Number(id));
    form.calendar_ids = [...(source.calendar_ids ?? [])].map((id) => Number(id));
    form.allow_client_choice = source.allow_client_choice ?? true;
    form.duration_minutes = Number(source.duration_minutes ?? 30) || 30;
    form.picker_key = pickerKey;
    form.specific_item = parseSpecificItem(source.link_type, option);
    nextTick(() => {
        syncing = false;
    });
}

applyModel(props.modelValue);

watch(() => buttonSnapshot(props.modelValue), () => {
    applyModel(props.modelValue);
});

const contentOptions = computed(() => props.pickerOptions.filter((option) => option.group === 'content'));
const bookingOption = computed(() => props.pickerOptions.find((option) => option.key === 'booking') ?? null);
const externalOption = computed(() => props.pickerOptions.find((option) => option.key === 'external') ?? null);

const filteredContentOptions = computed(() => {
    const query = pickerQuery.value.trim().toLowerCase();

    if (!query) {
        return contentOptions.value;
    }

    return contentOptions.value.filter((option) => String(option.label ?? '').toLowerCase().includes(query));
});

const selectedOption = computed(() => {
    if (form.picker_key === 'booking') {
        return bookingOption.value;
    }

    if (form.picker_key === 'external') {
        return externalOption.value;
    }

    return props.pickerOptions.find((option) => option.key === form.picker_key) ?? null;
});

const selectedLabel = computed(() => selectedOption.value?.label || 'اختر نوع الرابط');
const isExternal = computed(() => form.picker_key === 'external');
const isBooking = computed(() => form.picker_key === 'booking');
const hasLinkType = computed(() => Boolean(form.picker_key));
const mustPickSpecificItem = computed(() => {
    if (!selectedOption.value) {
        return false;
    }

    if (!selectedOption.value.supports_section && selectedOption.value.supports_item) {
        return true;
    }

    return form.specific_item && selectedOption.value.supports_item;
});

const resolvedLinkType = computed(() => {
    if (form.picker_key === 'external' || form.picker_key === 'booking') {
        return form.picker_key;
    }

    if (!selectedOption.value) {
        return 'external';
    }

    if (mustPickSpecificItem.value) {
        return `item:${form.picker_key}`;
    }

    return `section:${form.picker_key}`;
});

const bookingBranches = computed(() => props.bookingTargets?.branches ?? []);
const bookingCalendars = computed(() => props.bookingTargets?.calendars ?? []);

function emitValue() {
    if (syncing) {
        return;
    }

    const next = {
        label: form.label,
        link_type: resolvedLinkType.value,
        content_id: mustPickSpecificItem.value ? form.content_id : null,
        selected_content_title: mustPickSpecificItem.value ? form.selected_content_title : '',
        url: isExternal.value ? form.url : '',
        branch_ids: isBooking.value ? [...form.branch_ids] : [],
        calendar_ids: isBooking.value ? [...form.calendar_ids] : [],
        allow_client_choice: isBooking.value ? Boolean(form.allow_client_choice) : true,
        duration_minutes: isBooking.value ? (Number(form.duration_minutes) || 30) : 30,
    };

    if (buttonSnapshot(next) === buttonSnapshot(props.modelValue)) {
        return;
    }

    emit('update:modelValue', next);
}

watch([
    () => form.label,
    () => form.picker_key,
    () => form.specific_item,
    () => form.content_id,
    () => form.selected_content_title,
    () => form.url,
    () => form.branch_ids.slice(),
    () => form.calendar_ids.slice(),
    () => form.allow_client_choice,
    () => form.duration_minutes,
], emitValue);

function placePanel(triggerEl, panelEl, styleRef) {
    if (!triggerEl || !panelEl) {
        return;
    }

    const rect = triggerEl.getBoundingClientRect();
    const width = Math.max(rect.width, 280);
    let left = rect.left;
    let top = rect.bottom + 6;

    if (left + width > window.innerWidth - 12) {
        left = Math.max(12, window.innerWidth - width - 12);
    }

    if (top + 280 > window.innerHeight - 12) {
        top = Math.max(12, rect.top - 286);
    }

    styleRef.value = {
        top: `${top}px`,
        left: `${left}px`,
        width: `${width}px`,
    };
}

async function openPicker() {
    pickerOpen.value = true;
    pickerQuery.value = '';
    await nextTick();
    placePanel(pickerRoot.value, pickerPanel.value, pickerStyle);
}

function selectPickerOption(option) {
    form.picker_key = option.key;
    form.specific_item = parseSpecificItem('', option);
    form.content_id = null;
    form.selected_content_title = '';
    form.url = '';
    form.branch_ids = [];
    form.calendar_ids = [];
    pickerOpen.value = false;
}

async function openContentPicker() {
    contentPickerOpen.value = true;
    contentQuery.value = '';
    await nextTick();
    placePanel(contentSearchRoot.value, contentPickerPanel.value, contentPickerStyle);
    await searchContent('');
}

async function searchContent(query) {
    if (!mustPickSpecificItem.value || !form.picker_key) {
        contentResults.value = [];
        return;
    }

    contentSearching.value = true;

    try {
        const params = new URLSearchParams();
        params.set('link_type', `item:${form.picker_key}`);

        if (query.trim()) {
            params.set('search', query.trim());
        }

        const payload = await api(`/page/link-content?${params.toString()}`);
        contentResults.value = Array.isArray(payload?.data) ? payload.data : [];
    } catch {
        contentResults.value = [];
    } finally {
        contentSearching.value = false;
    }
}

watch(contentQuery, (value) => {
    clearTimeout(contentSearchTimer);
    contentSearchTimer = setTimeout(() => {
        searchContent(value);
    }, 250);
});

function selectContent(item) {
    form.content_id = item.id;
    form.selected_content_title = item.title ?? '';
    contentPickerOpen.value = false;
}

function onDocumentClick(event) {
    if (pickerOpen.value && pickerRoot.value && !pickerRoot.value.contains(event.target) && !pickerPanel.value?.contains(event.target)) {
        pickerOpen.value = false;
    }

    if (contentPickerOpen.value && contentSearchRoot.value && !contentSearchRoot.value.contains(event.target) && !contentPickerPanel.value?.contains(event.target)) {
        contentPickerOpen.value = false;
    }
}

onMounted(() => {
    document.addEventListener('mousedown', onDocumentClick);
});

onBeforeUnmount(() => {
    document.removeEventListener('mousedown', onDocumentClick);
    clearTimeout(contentSearchTimer);
});
</script>

<template>
    <div class="space-y-3 rounded-xl border border-stone-200 p-4">
        <p class="text-sm font-medium text-stone-700">الزر الرئيسي</p>

        <Input
            v-model="form.label"
            name="primary_button_label"
            label="نص الزر"
            placeholder="ابدأ الآن"
        />

        <Field name="primary_button_link_type" label="نوع الرابط">
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
                                type="search"
                                placeholder="ابحث عن نوع المحتوى..."
                                class="w-full rounded-md border border-stone-100 bg-stone-50 px-3 py-2 text-sm text-stone-700 placeholder:text-stone-400 focus:border-primary-400 focus:outline-none"
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
                name="primary_button_url"
                label="الرابط"
                placeholder="https://..."
                dir="ltr"
            />

            <template v-if="isBooking">
                <Field name="primary_button_branch_ids" label="الفروع" info="اختر الفروع التي يمكن الحجز فيها">
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

                <Field name="primary_button_calendar_ids" label="التقاويم" info="اختر تقاويم محددة بالإضافة إلى الفروع أو بدلًا منها">
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
                            <span class="min-w-0 truncate">{{ calendar.name }}</span>
                        </label>
                    </div>
                </Field>

                <Toggle
                    v-model="form.allow_client_choice"
                    name="primary_button_allow_client_choice"
                    label="السماح للعميل باختيار التقويم"
                />

                <Input
                    v-model="form.duration_minutes"
                    name="primary_button_duration_minutes"
                    type="number"
                    label="مدة الحجز (دقيقة)"
                    min="1"
                />
            </template>

            <template v-if="selectedOption?.supports_section && selectedOption?.supports_item">
                <Toggle
                    v-model="form.specific_item"
                    name="primary_button_specific_item"
                    label="ربط بعنصر محدد"
                    info="بدلاً من صفحة القسم كاملة"
                />
            </template>

            <Field v-if="mustPickSpecificItem" name="primary_button_content_id" label="العنصر">
                <div ref="contentSearchRoot" class="relative w-full">
                    <button
                        type="button"
                        class="flex w-full cursor-pointer items-center gap-2 rounded-md border border-transparent bg-white px-3 py-2 text-start text-sm text-stone-700 transition hover:border-stone-200 focus:border-primary-400 focus:outline-none"
                        @click="contentPickerOpen ? (contentPickerOpen = false) : openContentPicker()"
                    >
                        <span class="min-w-0 flex-1 truncate" :class="form.selected_content_title ? 'text-stone-800' : 'text-stone-400'">
                            {{ form.selected_content_title || 'اختر عنصراً...' }}
                        </span>
                        <Icon name="chevron-down" class="h-4 w-4 shrink-0 text-stone-400" />
                    </button>

                    <Teleport to="body">
                        <div
                            v-if="contentPickerOpen"
                            ref="contentPickerPanel"
                            class="fixed z-[100] overflow-hidden rounded-lg border border-stone-200 bg-white shadow-lg"
                            :style="contentPickerStyle"
                        >
                            <div class="border-b border-stone-100 p-2">
                                <input
                                    v-model="contentQuery"
                                    type="search"
                                    placeholder="ابحث..."
                                    class="w-full rounded-md border border-stone-100 bg-stone-50 px-3 py-2 text-sm text-stone-700 placeholder:text-stone-400 focus:border-primary-400 focus:outline-none"
                                >
                            </div>
                            <ul class="max-h-48 overflow-y-auto p-1">
                                <li v-if="contentSearching" class="px-3 py-3 text-center text-xs text-stone-400">جاري البحث...</li>
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
                                    <li v-if="!contentResults.length" class="px-3 py-4 text-center text-xs text-stone-400">
                                        لا توجد نتائج.
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </Teleport>
                </div>
            </Field>
        </template>

        <p class="text-xs text-stone-500">اترك نص الزر فارغاً لإخفائه من الصفحة.</p>
    </div>
</template>
