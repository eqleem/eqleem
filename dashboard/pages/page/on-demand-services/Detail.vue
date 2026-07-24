<script setup>
import { computed, reactive } from 'vue';
import { useRouter } from 'vue-router';
import ManageLayout from '../../../components/page/ManageLayout.vue';
import { CkEditor, MediaGallery } from '../../../components/ui/asyncHeavy.js';
import Form from '../../../components/ui/Form.vue';
import Field from '../../../components/ui/Field.vue';
import Input from '../../../components/ui/Input.vue';
import Textarea from '../../../components/ui/Textarea.vue';
import Button from '../../../components/ui/Button.vue';
import PageFormMetaSection from '../../../components/page/pages/PageFormMetaSection.vue';
import NotFound from '../../NotFound.vue';
import { useOnDemandServicesStore } from '../../../stores/on-demand-services.js';
import { usePageAdvancedOpen } from '../../../composables/usePageAdvancedOpen.js';
import { useContentDetailEditor } from '../../../composables/useContentDetailEditor.js';
import { useMediaGalleryActions } from '../../../composables/useMediaGalleryActions.js';
import { ApiError } from '../../../lib/api.js';
import { notifySuccess, notifyApiError } from '../../../lib/notify.js';

const router = useRouter();
const store = useOnDemandServicesStore();
const { expand: expandAdvanced } = usePageAdvancedOpen();

const form = reactive({
    title: '',
    subtitle: '',
    slug: '',
    price: '',
    comparePrice: '',
    unitType: 'square_meter',
    unitLabel: '',
    active: false,
    images: [],
});

const errors = reactive({
    title: null,
    slug: null,
    unitType: null,
    unitLabel: null,
    form: null,
});

function loadForm(service, { syncEditor: shouldSync = true } = {}) {
    if (!service) {
        return;
    }

    form.title = service.title ?? '';
    form.subtitle = service.subtitle ?? '';
    form.slug = service.slug ?? '';
    form.price = service.price ?? '';
    form.comparePrice = service.compare_price ?? '';
    form.unitType = service.unit_type || 'square_meter';
    form.unitLabel = service.unit_label ?? '';
    form.active = Boolean(service.active ?? service.published);
    form.images = [...(service.images ?? [])];
    errors.title = null;
    errors.slug = null;
    errors.unitType = null;
    errors.unitLabel = null;
    errors.form = null;

    if (shouldSync) {
        syncEditor(service.body ?? '');
    }
}

const { uuid, notFound, bodyEditor, bodySeed, readBody, syncEditor } = useContentDetailEditor({
    fetchItem: (id) => store.fetchOnDemandService(id),
    onLoaded: (service, opts) => loadForm(service, opts),
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

const editorUploadUrl = computed(() => `/api/on-demand-services/${uuid.value}/editor-images`);
const unitOptions = computed(() => store.detail?.unit_options ?? []);
const slugPrefix = computed(() => store.detail?.slug_prefix ?? '/on-demand-services/');
const isOtherUnit = computed(() => form.unitType === 'other');

async function persist({ close = false } = {}) {
    const body = readBody();
    bodySeed.value = body;

    const title = form.title.trim();
    const slug = form.slug.trim();
    const unitLabel = form.unitLabel.trim();

    errors.title = title ? null : 'اسم الخدمة مطلوب.';
    errors.slug = slug ? null : 'نص الرابط مطلوب.';
    errors.unitType = form.unitType ? null : 'وحدة التسعير مطلوبة.';
    errors.unitLabel = (form.unitType === 'other' && !unitLabel) ? 'اكتب اسم الوحدة.' : null;
    errors.form = null;

    if (errors.title || errors.slug || errors.unitType || errors.unitLabel) {
        if (errors.slug) {
            expandAdvanced();
        }
        return;
    }

    const payload = {
        title,
        subtitle: form.subtitle.trim(),
        body,
        slug,
        unit_type: form.unitType,
        unit_label: form.unitType === 'other' ? unitLabel : '',
        active: Boolean(form.active),
        editor_mode: 'html',
    };

    if (form.price !== '') {
        payload.price = Number(form.price);
    }

    if (form.comparePrice !== '') {
        payload.compare_price = Number(form.comparePrice);
    }

    try {
        const service = await store.updateOnDemandService(uuid.value, payload);

        if (close) {
            router.push('/manage/on-demand-services');
            return;
        }

        loadForm(service);
        notifySuccess('Saved');
    } catch (error) {
        if (error instanceof ApiError) {
            errors.title = error.errors?.title?.[0] ?? null;
            errors.slug = error.errors?.slug?.[0] ?? null;
            errors.unitType = error.errors?.unit_type?.[0] ?? null;
            errors.unitLabel = error.errors?.unit_label?.[0] ?? null;
            errors.form = (!errors.title && !errors.slug && !errors.unitType && !errors.unitLabel)
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
                        to="/manage/on-demand-services"
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

                        <Field
                            name="price"
                            label="السعر"
                            dir="ltr"
                            info-dir="rtl"
                            :error="errors.unitType"
                        >
                            <template #prefix>
                                <select
                                    id="unitType"
                                    v-model="form.unitType"
                                    class="min-w-28 shrink-0 rounded-md border border-transparent bg-white py-1.5 pe-6 ps-2 text-sm text-stone-700 focus:border-primary-400 focus:outline-none"
                                >
                                    <option
                                        v-for="option in unitOptions"
                                        :key="option.id"
                                        :value="option.id"
                                    >
                                        {{ option.label }}
                                    </option>
                                </select>
                                <span class="shrink-0 text-stone-400" aria-hidden="true">\</span>
                            </template>
                            <input
                                id="price"
                                v-model="form.price"
                                type="number"
                                step="0.01"
                                min="0"
                                placeholder="0.00"
                                class="block w-full rounded-md border-2 border-transparent bg-white px-3 py-1.5 text-sm text-stone-600 placeholder:text-sm focus:border-primary-500 focus:bg-stone-100/50 focus:text-stone-700 focus:outline-none"
                            >
                        </Field>

                        <Input
                            v-if="isOtherUnit"
                            v-model="form.unitLabel"
                            name="unitLabel"
                            label="اسم الوحدة"
                            placeholder="مثال : متر طولي، لتر، طقم .."
                            :error="errors.unitLabel"
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
                            info="عنوان فرعي يظهر تحت اسم الخدمة في صفحة العرض وقائمة الخدمات."
                            :rows="2"
                        />

                        <Input
                            v-model="form.comparePrice"
                            name="comparePrice"
                            label="سعر المقارنة"
                            type="number"
                            dir="ltr"
                            info-dir="rtl"
                            step="0.01"
                            min="0"
                            placeholder="0.00"
                            info="السعر الأصلي قبل الخصم؛ يظهر مشطوباً بجانب سعر البيع لإبراز التخفيض."
                        />
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
