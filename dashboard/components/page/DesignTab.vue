<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { storeToRefs } from 'pinia';
import MainBox from '../ui/MainBox.vue';
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
const uploads = reactive({});
const fieldErrors = reactive({});
onMounted(() => {
    store.fetchDesign();
});

watch(selectedTheme, (theme) => {
    activeTab.value = theme?.is_active ? 'customize' : 'info';
    Object.keys(uploads).forEach((key) => delete uploads[key]);
    Object.keys(fieldErrors).forEach((key) => delete fieldErrors[key]);
}, { immediate: true });

const selectedBorderClass = computed(() => 'border-primary-500 border-primary-500/15 shadow-md');

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
    <MainBox title="تصميم الصفحة" subtitle="تخصيص الألوان والخطوط والمظهر العام للصفحة.">
        <template #icon>
            <img :src="'/assets/icons/tabler/color-swatch.svg'" class="h-7 w-7" alt="">
        </template>

        <div class="space-y-3 p-4">
            <p v-if="loading && !themes.length" class="py-8 text-center text-sm text-stone-500">جاري التحميل…</p>
            <p v-else-if="error && !themes.length" class="py-8 text-center text-sm text-red-600">{{ error }}</p>
            <div v-else-if="themesEmpty" class="rounded-xl bg-stone-100/50 p-6 text-center text-sm text-stone-500">
                لا توجد قوالب متاحة حالياً.
            </div>

            <template v-else>
                <div class="rounded-xl bg-stone-300/30 p-2">
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
                                    <span class="shrink-0 text-[11px] font-semibold text-green-600">{{ theme.price_label }}</span>
                                </div>
                            </div>
                        </button>
                    </div>
                </div>

                <div
                    v-if="selectedTheme"
                    :key="`${selectedTheme.id}-${selectedTheme.is_active ? 'active' : 'inactive'}`"
                    class="overflow-hidden rounded-xl bg-white shadow-sm"
                >
                    <div class="border-b border-stone-100">
                        <div class="flex flex-col gap-2 px-3 py-2.5 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex min-w-0 items-center justify-end gap-1.5">
                                        <h3 class="truncate text-base font-semibold text-stone-900">{{ selectedTheme.name }}</h3>
                                        <Badge v-if="selectedTheme.is_active" color="green" size="sm">القالب النشط</Badge>
                                    </div>
                                    <span class="shrink-0 text-sm font-semibold text-green-600">{{ selectedTheme.price_label }}</span>
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

                    <div class="p-3">
                        <div v-show="activeTab === 'info'" class="space-y-3">
                            <div v-if="selectedTheme.gallery?.length" class="no-scrollbar flex gap-2 overflow-x-auto">
                                <div
                                    v-for="(image, index) in selectedTheme.gallery"
                                    :key="`${selectedTheme.id}-gallery-${index}`"
                                    class="shrink-0 overflow-hidden rounded-lg border border-stone-200 bg-stone-50"
                                >
                                    <img
                                        :src="image"
                                        :alt="selectedTheme.name"
                                        class="h-28 w-20 object-cover object-top sm:h-32 sm:w-24"
                                        loading="lazy"
                                    >
                                </div>
                            </div>

                            <div class="grid gap-3 lg:grid-cols-2">
                                <div class="overflow-hidden rounded-lg border border-stone-200 bg-stone-50/60">
                                    <div class="border-b border-stone-200 bg-white px-3 py-1.5">
                                        <p class="text-[11px] font-medium text-stone-400">معاينة القالب</p>
                                    </div>
                                    <div class="flex justify-center p-3">
                                        <img
                                            :src="selectedTheme.preview_url"
                                            :alt="selectedTheme.name"
                                            class="max-h-56 w-auto rounded-md border border-stone-200 bg-white shadow-sm sm:max-h-64"
                                        >
                                    </div>
                                </div>

                                <div class="overflow-hidden rounded-lg border border-stone-200 bg-stone-50/60">
                                    <div class="border-b border-stone-200 bg-white px-3 py-1.5">
                                        <p class="text-[11px] font-medium text-stone-400">معلومات القالب</p>
                                    </div>

                                    <dl class="divide-y divide-stone-200/80 px-3 py-1">
                                        <div class="flex items-center justify-between gap-3 py-1.5">
                                            <dt class="text-xs text-stone-500">المعرّف</dt>
                                            <dd class="text-xs font-medium text-stone-800">{{ selectedTheme.slug }}</dd>
                                        </div>
                                        <div class="flex items-center justify-between gap-3 py-1.5">
                                            <dt class="text-xs text-stone-500">النوع</dt>
                                            <dd class="text-xs font-medium text-stone-800">{{ selectedTheme.type }}</dd>
                                        </div>
                                        <div class="flex items-center justify-between gap-3 py-1.5">
                                            <dt class="text-xs text-stone-500">التطبيق</dt>
                                            <dd class="text-xs font-medium text-stone-800">{{ selectedTheme.app }}</dd>
                                        </div>
                                        <div class="flex items-center justify-between gap-3 py-1.5">
                                            <dt class="text-xs text-stone-500">المصمم</dt>
                                            <dd class="text-xs font-medium text-stone-800">{{ selectedTheme.designer }}</dd>
                                        </div>
                                        <div class="flex items-center justify-between gap-3 py-1.5">
                                            <dt class="text-xs text-stone-500">السعر</dt>
                                            <dd class="text-xs font-semibold text-green-600">{{ selectedTheme.price_label }}</dd>
                                        </div>
                                        <div class="flex items-center justify-between gap-3 py-1.5">
                                            <dt class="text-xs text-stone-500">الحالة</dt>
                                            <dd>
                                                <Badge v-if="selectedTheme.is_active" color="green" size="sm">مُفعّل على الصفحة</Badge>
                                                <Badge v-else color="gray" size="sm">غير مُفعّل</Badge>
                                            </dd>
                                        </div>
                                    </dl>
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
    </MainBox>
</template>
