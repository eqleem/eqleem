<script setup>
import { computed, nextTick, onMounted, reactive, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import ManageLayout from '../../../components/page/ManageLayout.vue';
import Form from '../../../components/ui/Form.vue';
import Input from '../../../components/ui/Input.vue';
import Textarea from '../../../components/ui/Textarea.vue';
import Button from '../../../components/ui/Button.vue';
import CkEditor from '../../../components/ui/CkEditor.vue';
import MediaGallery from '../../../components/ui/MediaGallery.vue';
import PageFormMetaSection from '../../../components/page/pages/PageFormMetaSection.vue';
import NotFound from '../../NotFound.vue';
import { useServicesStore } from '../../../stores/services.js';
import { usePageAdvancedOpen } from '../../../composables/usePageAdvancedOpen.js';
import { ApiError } from '../../../lib/api.js';
import { notifySuccess, notifyApiError } from '../../../lib/notify.js';

const route = useRoute();
const router = useRouter();
const store = useServicesStore();
const { expand: expandAdvanced } = usePageAdvancedOpen();
const uploading = ref(false);
const notFound = ref(false);
const bodyEditor = ref(null);
/** Body HTML loaded from the server — pushed into the editor only on load/save refresh. */
const bodySeed = ref('');

const form = reactive({
    title: '',
    subtitle: '',
    slug: '',
    price: '',
    durationMinutes: '',
    categoryIds: [],
    calendarIds: [],
    active: false,
    images: [],
});

const errors = reactive({
    title: null,
    slug: null,
    form: null,
});

const uuid = computed(() => String(route.params.id));
const editorUploadUrl = computed(() => `/api/services/${uuid.value}/editor-images`);
const categories = computed(() => store.detail?.category_options ?? []);
const calendars = computed(() => store.detail?.calendar_options ?? []);
const slugPrefix = computed(() => store.detail?.slug_prefix ?? '/services/');
const priceCurrencySuffix = computed(() => store.detail?.currency_symbol ?? '');

function loadForm(service, { syncEditor = true } = {}) {
    if (!service) {
        return;
    }

    form.title = service.title ?? '';
    form.subtitle = service.subtitle ?? '';
    form.slug = service.slug ?? '';
    form.price = service.price ?? '';
    form.durationMinutes = service.duration_minutes ?? '';
    form.categoryIds = [...(service.category_ids ?? [])].map(String);
    form.calendarIds = [...(service.calendar_ids ?? [])].map(String);
    form.active = Boolean(service.active ?? service.published);
    form.images = [...(service.images ?? [])];
    errors.title = null;
    errors.slug = null;
    errors.form = null;

    if (syncEditor) {
        bodySeed.value = service.body ?? '';
        nextTick(() => {
            bodyEditor.value?.setData?.(bodySeed.value);
        });
    }
}

onMounted(async () => {
    try {
        const service = await store.fetchService(uuid.value);
        loadForm(service);
    } catch (error) {
        notFound.value = error instanceof ApiError && error.status === 404;
    }
});

watch(() => route.params.id, async (id) => {
    if (!id) {
        return;
    }

    notFound.value = false;

    try {
        const service = await store.fetchService(String(id));
        loadForm(service);
    } catch (error) {
        notFound.value = error instanceof ApiError && error.status === 404;
    }
});

function toggleCategory(id, checked) {
    const key = String(id);

    if (checked) {
        if (!form.categoryIds.includes(key)) {
            form.categoryIds.push(key);
        }
        return;
    }

    form.categoryIds = form.categoryIds.filter((item) => item !== key);
}

function toggleCalendar(id, checked) {
    const key = String(id);

    if (checked) {
        if (!form.calendarIds.includes(key)) {
            form.calendarIds.push(key);
        }
        return;
    }

    form.calendarIds = form.calendarIds.filter((item) => item !== key);
}

async function uploadFiles(files) {
    uploading.value = true;

    try {
        for (const file of files) {
            form.images = await store.uploadImage(uuid.value, file);
        }
    } catch (error) {
        errors.form = error instanceof ApiError ? error.message : 'تعذر رفع الصورة.';
    } finally {
        uploading.value = false;
    }
}

async function reorderImages(order) {
    try {
        form.images = await store.reorderImages(uuid.value, order);
    } catch (error) {
        errors.form = error instanceof ApiError ? error.message : 'تعذر إعادة ترتيب الصور.';
        form.images = [...(store.detail?.images ?? [])];
    }
}

async function removeImage(mediaId) {
    try {
        form.images = await store.deleteImage(uuid.value, mediaId);
    } catch (error) {
        errors.form = error instanceof ApiError ? error.message : 'تعذر حذف الصورة.';
    }
}

function readBody() {
    try {
        return bodyEditor.value?.getData?.() ?? bodySeed.value ?? '';
    } catch {
        return bodySeed.value ?? '';
    }
}

async function persist({ close = false } = {}) {
    const body = readBody();
    bodySeed.value = body;

    const title = form.title.trim();
    const slug = form.slug.trim();

    errors.title = title ? null : 'اسم الخدمة مطلوب.';
    errors.slug = slug ? null : 'نص الرابط مطلوب.';
    errors.form = null;

    if (errors.title || errors.slug) {
        if (errors.slug) {
            expandAdvanced();
        }
        return;
    }

    const selectableCategories = new Set(
        categories.value.filter((item) => item.selectable).map((item) => String(item.id)),
    );
    const categoryIds = form.categoryIds
        .filter((id) => selectableCategories.has(String(id)))
        .map((id) => Number(id))
        .filter((id) => Number.isFinite(id) && id > 0);

    const selectableCalendars = new Set(
        calendars.value.filter((item) => item.selectable).map((item) => String(item.id)),
    );
    const calendarIds = form.calendarIds
        .filter((id) => selectableCalendars.has(String(id)))
        .map((id) => Number(id))
        .filter((id) => Number.isFinite(id) && id > 0);

    const payload = {
        title,
        subtitle: form.subtitle.trim(),
        body,
        slug,
        category_ids: categoryIds,
        calendar_ids: calendarIds,
        active: Boolean(form.active),
        editor_mode: 'html',
    };

    if (form.price !== '') {
        payload.price = Number(form.price);
    }

    if (form.durationMinutes !== '') {
        payload.duration_minutes = Number(form.durationMinutes);
    }

    try {
        const service = await store.updateService(uuid.value, payload);

        if (close) {
            router.push('/manage/services');
            return;
        }

        loadForm(service);
        notifySuccess('Saved');
    } catch (error) {
        if (error instanceof ApiError) {
            errors.title = error.errors?.title?.[0] ?? null;
            errors.slug = error.errors?.slug?.[0] ?? null;
            errors.form = (!errors.title && !errors.slug)
                ? (error.message || 'تعذر حفظ الخدمة.')
                : null;

            if (errors.slug) {
                expandAdvanced();
            }
        } else {
            errors.form = 'تعذر حفظ الخدمة.';
        }

        notifyApiError(error, 'تعذر حفظ الخدمة.');
    }
}

function save() {
    persist({ close: false });
}

function saveAndClose() {
    persist({ close: true });
}
</script>

<template>
    <ManageLayout v-if="store.detail && !notFound">
        <div class="overflow-hidden rounded-2xl bg-white">
            <div class="relative z-20 flex items-center justify-between gap-4 border-b border-stone-200 bg-stone-200/70 px-4 py-3">
                <div class="flex min-w-0 items-center gap-3">
                    <RouterLink
                        to="/manage/services"
                        title="رجوع"
                        class="flex shrink-0 items-center justify-center rounded-lg bg-white p-2 shadow-sm hover:bg-stone-50"
                    >
                        <svg class="h-5 w-5 text-stone-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </RouterLink>
                    <div class="flex min-w-0 items-center gap-2 text-sm text-stone-700">
                        <img v-if="store.type?.icon" :src="`/${store.type.icon}`" class="h-5 w-5 shrink-0" alt="">
                        <span class="truncate font-semibold">{{ store.type?.name }}</span>
                        <span class="text-stone-400 hidden md:inline">/</span>
                        <span class="truncate text-stone-600 hidden md:inline">تحرير الخدمة</span>
                    </div>
                </div>
            </div>

            <Form class="!rounded-none !p-4 md:!p-6" @submit="save">
                <p v-if="errors.form" class="mb-3 text-sm text-red-600">{{ errors.form }}</p>

                <div class="space-y-4">
                    <div class="space-y-2">
                        <Input
                            v-model="form.title"
                            name="title"
                            placeholder="اسم الخدمة"
                            :error="errors.title"
                        />

                        <MediaGallery
                            v-model="form.images"
                            label="صور الخدمة"
                            :uploading="uploading"
                            :disabled="store.saving"
                            @upload="uploadFiles"
                            @remove="removeImage"
                            @reorder="reorderImages"
                        />

                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                            <Input
                                v-model="form.price"
                                name="price"
                                label="السعر"
                                type="number"
                                dir="ltr"
                                info-dir="rtl"
                                step="0.01"
                                min="0"
                                placeholder="0.00"
                                :prefix="priceCurrencySuffix"
                            />
                            <Input
                                v-model="form.durationMinutes"
                                name="durationMinutes"
                                label="مدة الخدمة"
                                type="number"
                                dir="ltr"
                                info-dir="rtl"
                                min="1"
                                step="1"
                                placeholder="60"
                                prefix="دقائق"
                            />
                        </div>

                        <CkEditor
                            v-if="editorUploadUrl"
                            ref="bodyEditor"
                            :key="uuid"
                            :model-value="bodySeed"
                            name="body"
                            :upload-url="editorUploadUrl"
                        />
                    </div>

                    <PageFormMetaSection
                        v-model:published="form.active"
                        v-model:slug="form.slug"
                        :slug-prefix="slugPrefix"
                        :slug-error="errors.slug"
                    >
                        <Textarea
                            v-model="form.subtitle"
                            name="subtitle"
                            label="عنوان فرعي"
                            placeholder="عنوان فرعي"
                            info="عنوان فرعي يظهر تحت اسم الخدمة في صفحة الحجز وقائمة الخدمات."
                            :rows="2"
                        />

                        <div class="space-y-1.5">
                            <span class="block text-sm font-semibold text-stone-500">القسم</span>
                            <div class="space-y-1.5">
                                <label
                                    v-for="option in categories"
                                    :key="option.id"
                                    class="flex items-center gap-2 text-sm"
                                    :class="option.selectable ? 'text-stone-700' : 'text-stone-400'"
                                >
                                    <input
                                        type="checkbox"
                                        class="h-4 w-4 rounded border-stone-300"
                                        :disabled="!option.selectable"
                                        :checked="form.categoryIds.includes(String(option.id))"
                                        @change="toggleCategory(option.id, $event.target.checked)"
                                    >
                                    <span>{{ option.label }}</span>
                                </label>
                                <p v-if="categories.length === 0" class="text-xs text-stone-400">لا توجد تصنيفات بعد.</p>
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <span class="block text-sm font-semibold text-stone-500">مقدمو الخدمات</span>
                            <div class="space-y-1.5">
                                <label
                                    v-for="option in calendars"
                                    :key="option.id"
                                    class="flex items-center gap-2 text-sm"
                                    :class="option.selectable ? 'text-stone-700' : 'text-stone-400'"
                                >
                                    <input
                                        type="checkbox"
                                        class="h-4 w-4 rounded border-stone-300"
                                        :disabled="!option.selectable"
                                        :checked="form.calendarIds.includes(String(option.id))"
                                        @change="toggleCalendar(option.id, $event.target.checked)"
                                    >
                                    <span>{{ option.label }}</span>
                                </label>
                                <p v-if="calendars.length === 0" class="text-xs text-stone-400">لا يوجد مقدمو خدمات بعد.</p>
                                <p class="text-xs text-stone-400">اربط الخدمة بمقدمي الخدمات المتاحين لحجز المواعيد حسب جداولهم.</p>
                            </div>
                        </div>
                    </PageFormMetaSection>
                </div>

                <template #footer>
                    <div class="flex items-center gap-2">
                        <Button type="button" variant="secondary" label="حفظ وإغلاق" :disabled="store.saving" @click="saveAndClose" />
                        <Button type="submit" label="حفظ" :disabled="store.saving" />
                    </div>
                </template>
            </Form>
        </div>
    </ManageLayout>
    <ManageLayout v-else-if="store.detailLoading">
        <div class="flex items-center justify-center rounded-2xl bg-white p-10"><LoadingSpinner size="lg" /></div>
    </ManageLayout>
    <NotFound v-else-if="notFound" />
</template>
