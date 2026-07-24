<script setup>
import { computed, reactive } from 'vue';
import { useRouter } from 'vue-router';
import ManageLayout from '../../../components/page/ManageLayout.vue';
import CkEditor from '../../../components/ui/CkEditor.vue';
import MediaGallery from '../../../components/ui/MediaGallery.vue';
import Form from '../../../components/ui/Form.vue';
import Input from '../../../components/ui/Input.vue';
import Textarea from '../../../components/ui/Textarea.vue';
import Button from '../../../components/ui/Button.vue';
import PageFormMetaSection from '../../../components/page/pages/PageFormMetaSection.vue';
import NotFound from '../../NotFound.vue';
import { useUnitRentalStore } from '../../../stores/unit-rental.js';
import { usePageAdvancedOpen } from '../../../composables/usePageAdvancedOpen.js';
import { useContentDetailEditor } from '../../../composables/useContentDetailEditor.js';
import { useMediaGalleryActions } from '../../../composables/useMediaGalleryActions.js';
import { useIdChecklist } from '../../../composables/useIdChecklist.js';
import { ApiError } from '../../../lib/api.js';
import { notifySuccess, notifyApiError } from '../../../lib/notify.js';

const router = useRouter();
const store = useUnitRentalStore();
const { expand: expandAdvanced } = usePageAdvancedOpen();

const form = reactive({
    title: '',
    subtitle: '',
    slug: '',
    price: '',
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

function loadForm(unit, { syncEditor: shouldSync = true } = {}) {
    if (!unit) {
        return;
    }

    form.title = unit.title ?? '';
    form.subtitle = unit.subtitle ?? '';
    form.slug = unit.slug ?? '';
    form.price = unit.price ?? '';
    form.categoryIds = [...(unit.category_ids ?? [])].map(String);
    form.calendarIds = [...(unit.calendar_ids ?? [])].map(String);
    form.active = Boolean(unit.active ?? unit.published);
    form.images = [...(unit.images ?? [])];
    errors.title = null;
    errors.slug = null;
    errors.form = null;

    if (shouldSync) {
        syncEditor(unit.body ?? '');
    }
}

const { uuid, notFound, bodyEditor, bodySeed, readBody, syncEditor } = useContentDetailEditor({
    fetchItem: (id) => store.fetchUnit(id),
    onLoaded: (unit, opts) => loadForm(unit, opts),
});

const { uploading, uploadFiles, reorderImages, removeImage } = useMediaGalleryActions({
    uuid,
    form,
    errors,
    uploadImage: (id, file) => store.uploadImage(id, file),
    reorderImages: (id, order) => store.reorderImages(id, order),
    deleteImage: (id, mediaId) => store.deleteImage(id, mediaId),
    fallbackImages: () => store.detail?.images ?? [],
});

const { toggle: toggleCategory } = useIdChecklist(form);
const { toggle: toggleCalendar } = useIdChecklist(form, 'calendarIds');

const editorUploadUrl = computed(() => `/api/unit-rental/${uuid.value}/editor-images`);
const categories = computed(() => store.detail?.category_options ?? []);
const calendars = computed(() => store.detail?.calendar_options ?? []);
const slugPrefix = computed(() => store.detail?.slug_prefix ?? '/units/');

async function persist({ close = false } = {}) {
    const body = readBody();
    bodySeed.value = body;

    const title = form.title.trim();
    const slug = form.slug.trim();

    errors.title = title ? null : 'اسم نوع الوحدة مطلوب.';
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
        published: Boolean(form.active),
        editor_mode: 'html',
    };

    if (form.price !== '') {
        payload.price = Number(form.price);
    }

    try {
        const unit = await store.updateUnit(uuid.value, payload);

        if (close) {
            router.push('/manage/unit-rental');
            return;
        }

        loadForm(unit);
        notifySuccess('Saved');
    } catch (error) {
        if (error instanceof ApiError) {
            errors.title = error.errors?.title?.[0] ?? null;
            errors.slug = error.errors?.slug?.[0] ?? null;
            errors.form = (!errors.title && !errors.slug)
                ? (error.message || 'تعذر حفظ نوع الوحدة.')
                : null;

            if (errors.slug) {
                expandAdvanced();
            }
        } else {
            errors.form = 'تعذر حفظ نوع الوحدة.';
        }

        notifyApiError(error, 'تعذر حفظ نوع الوحدة.');
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
                        to="/manage/unit-rental"
                        title="رجوع"
                        class="flex shrink-0 items-center justify-center rounded-lg bg-white p-2 shadow-sm hover:bg-stone-50"
                    >
                        <svg class="h-5 w-5 text-stone-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </RouterLink>
                    <div class="flex min-w-0 items-center gap-2 text-sm text-stone-700">
                        <img v-if="store.type?.icon" :src="`/${store.type.icon}`" class="h-5 w-5 shrink-0" alt="">
                        <span class="truncate font-semibold">{{ store.type?.name }}</span>
                        <span class="text-stone-400 hidden md:inline">/</span>
                        <span class="truncate text-stone-600 hidden md:inline">تحرير نوع الوحدة</span>
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
                            placeholder="اسم نوع الوحدة"
                            :error="errors.title"
                        />

                        <MediaGallery
                            v-model="form.images"
                            label="صور الوحدة"
                            :uploading="uploading"
                            :disabled="store.saving"
                            @upload="uploadFiles"
                            @remove="removeImage"
                            @reorder="reorderImages"
                        />

                        <Input
                            v-model="form.price"
                            name="price"
                            label="السعر"
                            type="number"
                            info-dir="rtl"
                            step="0.01"
                            min="0"
                            placeholder="0.00"
                            suffix="ليلة"
                        />

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
                            info="عنوان فرعي يظهر تحت اسم نوع الوحدة في صفحة الحجز وقائمة الوحدات."
                            :rows="2"
                        />

                        <div class="space-y-1.5">
                            <span class="block text-sm font-semibold text-stone-500">التصنيف</span>
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
                            <span class="block text-sm font-semibold text-stone-500">مخزون الوحدات</span>
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
                                <p v-if="calendars.length === 0" class="text-xs text-stone-400">لا توجد وحدات مخزون بعد.</p>
                                <p class="text-xs text-stone-400">اربط نوع الوحدة بوحدات المخزون المتاحة لحجز المواعيد حسب جداولها.</p>
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
