<script setup>
import { computed, reactive, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import ManageLayout from '../../../components/page/ManageLayout.vue';
import { CkEditor, MediaGallery, FileGallery } from '../../../components/ui/asyncHeavy.js';
import Form from '../../../components/ui/Form.vue';
import Input from '../../../components/ui/Input.vue';
import Price from '../../../components/ui/Price.vue';
import Textarea from '../../../components/ui/Textarea.vue';
import Button from '../../../components/ui/Button.vue';
import Alert from '../../../components/ui/Alert.vue';
import PageFormMetaSection from '../../../components/page/pages/PageFormMetaSection.vue';
import NotFound from '../../NotFound.vue';
import { useDigitalProductsStore } from '../../../stores/digital-products.js';
import { usePageAdvancedOpen } from '../../../composables/usePageAdvancedOpen.js';
import { useContentDetailEditor } from '../../../composables/useContentDetailEditor.js';
import { useMediaGalleryActions } from '../../../composables/useMediaGalleryActions.js';
import { useIdChecklist } from '../../../composables/useIdChecklist.js';
import { ApiError } from '../../../lib/api.js';
import { notifySuccess, notifyApiError } from '../../../lib/notify.js';

const route = useRoute();
const router = useRouter();
const store = useDigitalProductsStore();
const { expand: expandAdvanced } = usePageAdvancedOpen();
const formTab = ref('edit');
const uploadingDownloads = ref(false);

const form = reactive({
    title: '',
    subtitle: '',
    slug: '',
    price: '',
    comparePrice: '',
    categoryIds: [],
    active: false,
    images: [],
    downloads: [],
});

const errors = reactive({
    title: null,
    slug: null,
    form: null,
});

function switchTab(tab) {
    formTab.value = tab;
}

function loadForm(product, { syncEditor: shouldSync = true } = {}) {
    if (!product) {
        return;
    }

    form.title = product.title ?? '';
    form.subtitle = product.subtitle ?? '';
    form.slug = product.slug ?? '';
    form.price = product.price ?? '';
    form.comparePrice = product.compare_price ?? '';
    form.categoryIds = [...(product.category_ids ?? [])].map(String);
    form.active = Boolean(product.active ?? product.published);
    form.images = [...(product.images ?? [])];
    form.downloads = [...(product.downloads ?? [])];
    errors.title = null;
    errors.slug = null;
    errors.form = null;

    if (shouldSync) {
        syncEditor(product.body ?? '');
    }
}

const { uuid, notFound, bodyEditor, bodySeed, readBody, syncEditor } = useContentDetailEditor({
    fetchItem: (id) => store.fetchDigitalProduct(id),
    onLoaded: (product, opts) => loadForm(product, opts),
});

watch(() => route.params.id, (id) => {
    if (id) {
        formTab.value = 'edit';
    }
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

const editorUploadUrl = computed(() => `/api/digital-products/${uuid.value}/editor-images`);
const categories = computed(() => store.detail?.category_options ?? []);
const slugPrefix = computed(() => store.detail?.slug_prefix ?? '/digital-products/');
const priceCurrency = computed(() => store.detail?.currency_symbol ?? '');

async function uploadDownloads(files) {
    uploadingDownloads.value = true;

    try {
        for (const file of files) {
            form.downloads = await store.uploadDownload(uuid.value, file);
        }
    } catch (error) {
        errors.form = error instanceof ApiError ? error.message : 'تعذر رفع الملف.';
    } finally {
        uploadingDownloads.value = false;
    }
}

async function reorderDownloads(order) {
    try {
        form.downloads = await store.reorderDownloads(uuid.value, order);
    } catch (error) {
        errors.form = error instanceof ApiError ? error.message : 'تعذر إعادة ترتيب الملفات.';
        form.downloads = [...(store.detail?.downloads ?? [])];
    }
}

async function removeDownload(mediaId) {
    try {
        form.downloads = await store.deleteDownload(uuid.value, mediaId);
    } catch (error) {
        errors.form = error instanceof ApiError ? error.message : 'تعذر حذف الملف.';
    }
}

async function persist({ close = false } = {}) {
    const body = readBody();
    bodySeed.value = body;

    const title = form.title.trim();
    const slug = form.slug.trim();

    errors.title = title ? null : 'اسم المنتج مطلوب.';
    errors.slug = slug ? null : 'نص الرابط مطلوب.';
    errors.form = null;

    if (errors.title || errors.slug) {
        if (errors.slug) {
            switchTab('edit');
            expandAdvanced();
        }
        return;
    }

    const selectable = new Set(categories.value.filter((item) => item.selectable).map((item) => String(item.id)));
    const categoryIds = form.categoryIds
        .filter((id) => selectable.has(String(id)))
        .map((id) => Number(id))
        .filter((id) => Number.isFinite(id) && id > 0);

    const payload = {
        title,
        subtitle: form.subtitle.trim(),
        body,
        slug,
        category_ids: categoryIds,
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
        const product = await store.updateDigitalProduct(uuid.value, payload);

        if (close) {
            router.push('/manage/digital-products');
            return;
        }

        loadForm(product);
        notifySuccess('Saved');
    } catch (error) {
        if (error instanceof ApiError) {
            errors.title = error.errors?.title?.[0] ?? null;
            errors.slug = error.errors?.slug?.[0] ?? null;
            errors.form = (!errors.title && !errors.slug)
                ? (error.message || 'تعذر حفظ المنتج.')
                : null;

            if (errors.slug) {
                switchTab('edit');
                expandAdvanced();
            }
        } else {
            errors.form = 'تعذر حفظ المنتج.';
        }

        notifyApiError(error, 'تعذر حفظ المنتج.');
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
                        to="/manage/digital-products"
                        title="رجوع"
                        class="flex shrink-0 items-center justify-center rounded-lg bg-white p-2 shadow-sm hover:bg-stone-50"
                    >
                        <svg class="h-5 w-5 text-stone-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </RouterLink>
                    <div class="flex min-w-0 items-center gap-2 text-sm text-stone-700">
                        <img v-if="store.type?.icon" :src="`/${store.type.icon}`" class="h-5 w-5 shrink-0" alt="">
                        <span class="truncate font-semibold">{{ store.type?.name }}</span>
                        <span class="text-stone-400 hidden md:inline">/</span>
                        <span class="truncate text-stone-600 hidden md:inline">تحرير المنتج الرقمي</span>
                    </div>
                </div>

                <nav class="relative z-20 flex shrink-0 items-center gap-1 rounded-xl bg-stone-300/40 p-0.5">
                    <button
                        type="button"
                        class="flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm transition"
                        :class="formTab === 'edit' ? 'bg-white font-semibold text-stone-900 shadow-sm' : 'text-stone-600 hover:bg-white/60 hover:text-stone-800'"
                        @click.prevent.stop="switchTab('edit')"
                    >
                        تحرير
                    </button>
                    <button
                        type="button"
                        class="flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm transition"
                        :class="formTab === 'downloads' ? 'bg-white font-semibold text-stone-900 shadow-sm' : 'text-stone-600 hover:bg-white/60 hover:text-stone-800'"
                        @click.prevent.stop="switchTab('downloads')"
                    >
                        ملفات التحميل
                    </button>
                </nav>
            </div>

            <Form class="!rounded-none !p-4 md:!p-6" @submit="save">
                <p v-if="errors.form" class="mb-3 text-sm text-red-600">{{ errors.form }}</p>

                <div
                    class="space-y-4"
                    :class="formTab === 'edit' ? 'relative z-0 block' : 'hidden'"
                >
                    <div class="space-y-2">
                        <Input
                            v-model="form.title"
                            name="title"
                            placeholder="اسم المنتج"
                            :error="errors.title"
                        />

                        <MediaGallery
                            v-model="form.images"
                            label="صور المنتج"
                            :uploading="uploading"
                            :disabled="store.saving"
                            @upload="uploadFiles"
                            @remove="removeImage"
                            @reorder="reorderImages"
                        />

                        <Price v-model="form.price" name="price" :currency="priceCurrency" />

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
                            info="عنوان فرعي يظهر تحت اسم المنتج في صفحة العرض وقائمة المنتجات."
                            :rows="2"
                        />

                        <Price
                            v-model="form.comparePrice"
                            name="comparePrice"
                            label="سعر المقارنة"
                            :currency="priceCurrency"
                            info="السعر الأصلي قبل الخصم؛ يظهر مشطوباً بجانب سعر البيع لإبراز التخفيض."
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
                    </PageFormMetaSection>
                </div>

                <div
                    class="space-y-2"
                    :class="formTab === 'downloads' ? 'relative z-0 block' : 'hidden'"
                >
                    <Alert
                        color="blue"
                        heading="ملفات التحميل"
                        text="ارفع الملفات التي سيحصل عليها العميل بعد إتمام عملية الشراء — مثل PDF، ZIP، فيديو، أو أي ملف رقمي."
                    />

                    <FileGallery
                        v-model="form.downloads"
                        label="ملفات التحميل"
                        :uploading="uploadingDownloads"
                        :disabled="store.saving"
                        @upload="uploadDownloads"
                        @remove="removeDownload"
                        @reorder="reorderDownloads"
                    />
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
