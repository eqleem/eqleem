<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { storeToRefs } from 'pinia';
import Icon from '../ui/Icon.vue';
import Badge from '../ui/Badge.vue';
import Button from '../ui/Button.vue';
import Form from '../ui/Form.vue';
import ThemeOptionField from './editors/ThemeOptionField.vue';
import { usePageDesignStore } from '../../stores/pageDesign.js';
import { notifySuccess, notifyError } from '../../lib/notify.js';

const store = usePageDesignStore();
const {
    themes,
    selectedTheme,
    options,
    optionPreviews,
    loading,
    error,
    saving,
    activating,
    message,
    hasOptions,
    themesEmpty,
    schemaEntries,
} = storeToRefs(store);

const activeTab = ref('customize');
const galleryIndex = ref(0);
const uploads = reactive({});
const fieldErrors = reactive({});
onMounted(() => {
    store.fetchDesign();
});

watch(selectedTheme, (theme) => {
    activeTab.value = theme?.is_active ? 'customize' : 'info';
    galleryIndex.value = 0;
    Object.keys(uploads).forEach((key) => delete uploads[key]);
    Object.keys(fieldErrors).forEach((key) => delete fieldErrors[key]);
}, { immediate: true });

const selectedBorderClass = computed(() => 'border-primary-500   ');

const galleryImages = computed(() => {
    const theme = selectedTheme.value;
    if (!theme) {
        return [];
    }

    const images = Array.isArray(theme.gallery) && theme.gallery.length
        ? theme.gallery
        : [theme.preview_url || theme.image_path].filter(Boolean);

    return [...new Set(images)];
});

const activeGalleryImage = computed(() => galleryImages.value[galleryIndex.value] ?? galleryImages.value[0] ?? null);

const themeMetaRows = computed(() => {
    const theme = selectedTheme.value;
    if (!theme) {
        return [];
    }

    return [
        { label: 'المصمم', value: theme.designer || '—', icon: 'user' },
        { label: 'الإصدار', value: theme.version || '—', icon: 'history' },
        { label: 'المعرّف', value: theme.slug || '—', icon: 'link' },
        { label: 'النوع', value: theme.type || '—', icon: 'package' },
        { label: 'التطبيق', value: theme.app || '—', icon: 'store' },
        {
            label: 'السعر',
            value: theme.is_free ? 'مجاني' : theme.price_label,
            icon: 'coin',
            isFree: Boolean(theme.is_free),
            isPrice: true,
        },
    ];
});

function selectGalleryImage(index) {
    galleryIndex.value = index;
}

async function selectTheme(themeId) {
    try {
        await store.selectTheme(themeId);
    } catch {
        // error surfaced via store
    }
}

async function setDefaultTheme() {
    try {
        await store.setDefaultTheme();
    } catch {
        // error surfaced via store
    }
}

function onUpload({ key, file }) {
    uploads[key] = file;
    fieldErrors[key] = null;
}

async function saveOptions() {
    Object.keys(fieldErrors).forEach((key) => delete fieldErrors[key]);

    const result = await store.saveOptions(uploads);

    if (!result.ok) {
        Object.entries(result.errors ?? {}).forEach(([key, messages]) => {
            const match = key.match(/^uploads\.(.+)$/);
            if (match) {
                fieldErrors[match[1]] = messages?.[0] ?? null;
            }
        });
        notifyError(result.message ?? 'تعذر الحفظ');
        return;
    }

    notifySuccess('Settings updated successfully.');
    Object.keys(uploads).forEach((key) => delete uploads[key]);
}
</script>

<template>
    <div class="space-y-3">
        <div v-if="loading && !themes.length" class="rounded-2xl bg-white py-8 shadow-sm flex items-center justify-center">
            <LoadingSpinner />
        </div>
        <p v-else-if="error && !themes.length" class="rounded-2xl bg-white py-8 text-center text-sm text-red-600 shadow-sm">{{ error }}</p>
        <div v-else-if="themesEmpty" class="rounded-2xl bg-white p-6 text-center text-sm text-stone-500 shadow-sm">
            لا توجد قوالب متاحة حالياً.
        </div>

        <template v-else>
            <div class="rounded-xl bg-stone-300/80 p-2">
                <div class="no-scrollbar flex gap-2 overflow-x-auto">
                    <button
                        v-for="theme in themes"
                        :key="theme.id"
                        type="button"
                        class="group relative w-24 shrink-0 rounded-lg border-2 bg-transparent text-start transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500/40 sm:w-40"
                        :class="selectedTheme?.id === theme.id
                            ? selectedBorderClass
                            : 'border-transparent bg-white hover:border-stone-200 hover:shadow-sm'"
                        @click="selectTheme(theme.id)"
                    >
                        <span
                            v-if="theme.is_active"
                            class="absolute start-1.5 top-1.5 z-10 inline-flex items-center gap-0.5 rounded-full bg-green-500 px-1.5 py-0.5 text-[9px] font-semibold text-white shadow-sm"
                        >
                            <Icon name="check" class="h-2.5 w-2.5" />
                            نشط
                        </span>

                        <div class="overflow-hidden rounded-t-md bg-stone-100">
                            <img
                                :src="theme.image_path"
                                :alt="theme.name"
                                class="aspect-square w-full object-cover object-top transition duration-300 group-hover:scale-[1.02]"
                                loading="lazy"
                            >
                        </div>

                        <div class="rounded-b-lg bg-white px-2 py-1.5">
                            <div class="flex items-center justify-between gap-2">
                                <span class="min-w-0 truncate text-[11px] font-medium text-stone-700">{{ theme.name }}</span>
                                <span
                                    v-if="theme.is_free"
                                    class="shrink-0 rounded-full bg-emerald-50 px-1.5 py-0.5 text-[10px] font-semibold text-emerald-700"
                                >مجاني</span>
                                <Money v-else :formatted="theme.price_label" class="shrink-0 text-[11px] font-semibold text-stone-800" />
                            </div>
                        </div>
                    </button>
                </div>
            </div>

            <div
                v-if="selectedTheme"
                :key="`${selectedTheme.id}-${selectedTheme.is_active ? 'active' : 'inactive'}`"
                class="overflow-hidden rounded-2xl bg-white shadow-sm"
            >
                <div class="border-b border-stone-100">
                    <div class="flex flex-col gap-2 px-3 py-2.5 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex min-w-0 items-center justify-end gap-1.5">
                                    <h3 class="truncate text-base font-semibold text-stone-900">{{ selectedTheme.name }}</h3>
                                    <Badge v-if="selectedTheme.is_active" color="green" size="sm">القالب النشط</Badge>
                                </div>
                                <span
                                    v-if="selectedTheme.is_free"
                                    class="shrink-0 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700"
                                >مجاني</span>
                                <Money v-else :formatted="selectedTheme.price_label" class="shrink-0 text-sm font-semibold text-stone-800" />
                            </div>
                        </div>

                        <div class="shrink-0">
                            <div
                                v-if="selectedTheme.is_active"
                                class="inline-flex items-center gap-1.5 rounded-md border border-green-200 bg-green-50 px-2.5 py-1.5 text-xs font-medium text-green-700"
                            >
                                <Icon name="check" class="h-3.5 w-3.5" />
                                مُفعّل على صفحتك
                            </div>
                            <Button
                                v-else
                                label="تعيين كقالب افتراضي"
                                :loading="activating"
                                @click="setDefaultTheme"
                            >
                                <template #icon>
                                    <Icon name="palette" class="h-4 w-4" />
                                </template>
                            </Button>
                        </div>
                    </div>

                    <nav class="flex items-center gap-0.5 border-b border-stone-200 bg-stone-100">
                        <template v-if="selectedTheme.is_active">
                            <button
                                type="button"
                                class="-mb-px px-3 py-2 text-xs transition sm:text-sm"
                                :class="activeTab === 'customize'
                                    ? 'border-b-2 border-primary-500 font-semibold text-stone-900'
                                    : 'border-b-2 border-transparent text-stone-500 hover:text-stone-700'"
                                @click="activeTab = 'customize'"
                            >
                                تخصيص القالب
                            </button>
                            <button
                                type="button"
                                class="-mb-px px-3 py-2 text-xs transition sm:text-sm"
                                :class="activeTab === 'info'
                                    ? 'border-b-2 border-primary-500 font-semibold text-stone-900'
                                    : 'border-b-2 border-transparent text-stone-500 hover:text-stone-700'"
                                @click="activeTab = 'info'"
                            >
                                معلومات
                            </button>
                        </template>
                        <template v-else>
                            <button
                                type="button"
                                class="-mb-px px-3 py-2 text-xs transition sm:text-sm"
                                :class="activeTab === 'info'
                                    ? 'border-b-2 border-primary-500 font-semibold text-stone-900'
                                    : 'border-b-2 border-transparent text-stone-500 hover:text-stone-700'"
                                @click="activeTab = 'info'"
                            >
                                معلومات
                            </button>
                            <button
                                type="button"
                                class="-mb-px px-3 py-2 text-xs transition sm:text-sm"
                                :class="activeTab === 'customize'
                                    ? 'border-b-2 border-primary-500 font-semibold text-stone-900'
                                    : 'border-b-2 border-transparent text-stone-500 hover:text-stone-700'"
                                @click="activeTab = 'customize'"
                            >
                                تخصيص القالب
                            </button>
                        </template>
                    </nav>
                </div>

                    <div class="p-3 sm:p-4">
                        <div v-show="activeTab === 'info'" class="space-y-5">
                            <div class="grid gap-5 lg:grid-cols-[minmax(0,1.15fr)_minmax(0,1fr)] lg:items-start">
                                <div class="space-y-2.5">
                                    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-b from-stone-100 to-stone-50 ring-1 ring-stone-200/80">
                                        <div class="flex aspect-[4/3] items-center justify-center p-3 sm:aspect-[16/11] sm:p-4">
                                            <img
                                                v-if="activeGalleryImage"
                                                :key="activeGalleryImage"
                                                :src="activeGalleryImage"
                                                :alt="selectedTheme.name"
                                                class="max-h-full max-w-full rounded-lg object-contain object-top shadow-sm transition duration-300"
                                            >
                                        </div>
                                        <div class="pointer-events-none absolute inset-x-0 bottom-0 h-16 bg-gradient-to-t from-stone-900/10 to-transparent" />
                                        <div class="absolute bottom-3 start-3 flex items-center gap-1.5">
                                            <span
                                                v-if="selectedTheme.is_free"
                                                class="rounded-full bg-white/95 px-2.5 py-1 text-xs font-semibold text-emerald-700 shadow-sm backdrop-blur"
                                            >مجاني</span>
                                            <span
                                                v-else
                                                class="rounded-full bg-white/95 px-2.5 py-1 text-xs font-semibold text-stone-800 shadow-sm backdrop-blur"
                                            >
                                                <Money :formatted="selectedTheme.price_label" />
                                            </span>
                                        </div>
                                        <span
                                            v-if="galleryImages.length > 1"
                                            class="absolute bottom-3 end-3 rounded-full bg-stone-900/70 px-2 py-0.5 text-[10px] font-medium text-white backdrop-blur"
                                        >
                                            {{ galleryIndex + 1 }} / {{ galleryImages.length }}
                                        </span>
                                    </div>

                                    <div
                                        v-if="galleryImages.length > 1"
                                        class="no-scrollbar flex gap-2 overflow-x-auto pb-0.5"
                                    >
                                        <button
                                            v-for="(image, index) in galleryImages"
                                            :key="`${selectedTheme.id}-thumb-${index}`"
                                            type="button"
                                            class="group relative shrink-0 overflow-hidden rounded-xl bg-stone-100 ring-2 transition focus-visible:outline-none focus-visible:ring-primary-500/50"
                                            :class="galleryIndex === index
                                                ? 'ring-primary-500 shadow-sm'
                                                : 'ring-transparent hover:ring-stone-300'"
                                            @click="selectGalleryImage(index)"
                                        >
                                            <img
                                                :src="image"
                                                :alt="`${selectedTheme.name} — ${index + 1}`"
                                                class="h-16 w-14 object-cover object-top transition duration-200 group-hover:scale-[1.03] sm:h-20 sm:w-16"
                                                loading="lazy"
                                            >
                                        </button>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <div class="space-y-2">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h4 class="text-lg font-semibold tracking-tight text-stone-900">{{ selectedTheme.name }}</h4>
                                            <Badge v-if="selectedTheme.is_active" color="green" size="sm">مُفعّل</Badge>
                                            <Badge v-else color="gray" size="sm">غير مُفعّل</Badge>
                                        </div>
                                        <p
                                            v-if="selectedTheme.description"
                                            class="text-sm leading-7 text-stone-600"
                                        >
                                            {{ selectedTheme.description }}
                                        </p>
                                        <p
                                            v-else
                                            class="text-sm leading-7 text-stone-400"
                                        >
                                            لا يوجد وصف لهذا القالب حالياً.
                                        </p>
                                    </div>

                                    <div
                                        v-if="selectedTheme.features?.length"
                                        class="rounded-2xl bg-stone-50/80 p-3.5 ring-1 ring-stone-200/70"
                                    >
                                        <p class="mb-2.5 text-xs font-semibold uppercase tracking-wide text-stone-400">المزايا</p>
                                        <ul class="space-y-2">
                                            <li
                                                v-for="(feature, index) in selectedTheme.features"
                                                :key="`${selectedTheme.id}-feature-${index}`"
                                                class="flex items-start gap-2.5 text-sm text-stone-700"
                                            >
                                                <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                                                    <Icon name="check" class="h-3 w-3" />
                                                </span>
                                                <span class="leading-6">{{ feature }}</span>
                                            </li>
                                        </ul>
                                    </div>

                                    <div class="overflow-hidden rounded-2xl ring-1 ring-stone-200/70">
                                        <div class="border-b border-stone-100 bg-stone-50/80 px-3.5 py-2">
                                            <p class="text-xs font-semibold uppercase tracking-wide text-stone-400">معلومات القالب</p>
                                        </div>
                                        <dl class="divide-y divide-stone-100 bg-white">
                                            <div
                                                v-for="row in themeMetaRows"
                                                :key="row.label"
                                                class="flex items-center justify-between gap-3 px-3.5 py-2.5"
                                            >
                                                <dt class="flex min-w-0 items-center gap-2 text-xs text-stone-500">
                                                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-stone-100 text-stone-400">
                                                        <Icon :name="row.icon" class="h-3.5 w-3.5" />
                                                    </span>
                                                    <span>{{ row.label }}</span>
                                                </dt>
                                                <dd class="shrink-0 text-xs font-medium text-stone-800" dir="auto">
                                                    <span
                                                        v-if="row.isPrice && row.isFree"
                                                        class="inline-flex rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700"
                                                    >مجاني</span>
                                                    <Money
                                                        v-else-if="row.isPrice"
                                                        :formatted="row.value"
                                                        class="text-xs font-semibold text-stone-800"
                                                    />
                                                    <template v-else>{{ row.value }}</template>
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-show="activeTab === 'customize'">
                            <Form
                                v-if="hasOptions"
                                class="!p-0"
                                form-class="!space-y-2"
                                @submit="saveOptions"
                            >
                                <ThemeOptionField
                                    v-for="[key, field] in schemaEntries"
                                    :key="`${selectedTheme.id}-${key}`"
                                    :field-key="key"
                                    :field="field"
                                    v-model="options[key]"
                                    :preview="optionPreviews[key] ?? null"
                                    :error="fieldErrors[key] ?? null"
                                    @upload="onUpload"
                                />

                                <template #footer>
                                    <Button type="submit" label="حفظ" :loading="saving" />
                                </template>
                            </Form>

                            <div v-else class="rounded-lg bg-stone-50 px-4 py-6 text-center text-sm text-stone-500">
                                لا توجد خيارات متاحة لهذا القالب
                            </div>
                        </div>
                    </div>
                </div>
        </template>
    </div>
</template>
