<script setup>
import { computed, nextTick, onMounted, reactive, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import ManageLayout from '../../../components/page/ManageLayout.vue';
import Form from '../../../components/ui/Form.vue';
import Input from '../../../components/ui/Input.vue';
import Textarea from '../../../components/ui/Textarea.vue';
import Button from '../../../components/ui/Button.vue';
import Toggle from '../../../components/ui/Toggle.vue';
import CkEditor from '../../../components/ui/CkEditor.vue';
import MediaGallery from '../../../components/ui/MediaGallery.vue';
import NotFound from '../../NotFound.vue';
import { usePortfolioStore } from '../../../stores/portfolio.js';
import { ApiError } from '../../../lib/api.js';
import { notifySuccess, notifyApiError } from '../../../lib/notify.js';

const route = useRoute(); 
const router = useRouter();
const store = usePortfolioStore();
const formTab = ref('edit');
const uploading = ref(false);
const notFound = ref(false);
const bodyEditor = ref(null);
/** Body HTML loaded from the server — pushed into the editor only on load/save refresh. */
const bodySeed = ref('');

const form = reactive({
    title: '',
    subtitle: '',
    slug: '',
    categoryIds: [],
    published: false,
    images: [],
});

const errors = reactive({
    title: null,
    slug: null,
    form: null,
});

const uuid = computed(() => String(route.params.id));
const editorUploadUrl = computed(() => `/api/portfolio/${uuid.value}/editor-images`);
const categories = computed(() => store.detail?.category_options ?? []);
const slugPrefix = computed(() => store.detail?.slug_prefix ?? '/portfolio/');

function switchTab(tab) {
    formTab.value = tab;
}

function loadForm(project, { syncEditor = true } = {}) {
    if (!project) {
        return;
    }

    form.title = project.title ?? '';
    form.subtitle = project.subtitle ?? '';
    form.slug = project.slug ?? '';
    form.categoryIds = [...(project.category_ids ?? [])].map(String);
    form.published = Boolean(project.published);
    form.images = [...(project.images ?? [])];
    errors.title = null;
    errors.slug = null;
    errors.form = null;

    if (syncEditor) {
        bodySeed.value = project.body ?? '';
        nextTick(() => {
            bodyEditor.value?.setData?.(bodySeed.value);
        });
    }
}

onMounted(async () => {
    try {
        const project = await store.fetchProject(uuid.value);
        loadForm(project);
    } catch (error) {
        notFound.value = error instanceof ApiError && error.status === 404;
    }
});

watch(() => route.params.id, async (id) => {
    if (!id) {
        return;
    }

    notFound.value = false;
    formTab.value = 'edit';

    try {
        const project = await store.fetchProject(String(id));
        loadForm(project);
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
    // Capture editor HTML before any navigation/unmount can destroy CKEditor.
    const body = readBody();
    bodySeed.value = body;

    const title = form.title.trim();
    const slug = form.slug.trim();

    errors.title = title ? null : 'عنوان المشروع مطلوب.';
    errors.slug = slug ? null : 'نص الرابط مطلوب.';
    errors.form = null;

    if (errors.title || errors.slug) {
        switchTab(errors.title ? 'edit' : 'advanced');
        return;
    }

    const selectable = new Set(categories.value.filter((item) => item.selectable).map((item) => String(item.id)));
    const categoryIds = form.categoryIds
        .filter((id) => selectable.has(String(id)))
        .map((id) => Number(id))
        .filter((id) => Number.isFinite(id) && id > 0);

    try {
        const project = await store.updateProject(uuid.value, {
            title,
            subtitle: form.subtitle.trim(),
            body,
            slug,
            category_ids: categoryIds,
            published: Boolean(form.published),
            editor_mode: 'html',
        });

        if (close) {
            router.push('/manage/portfolio');
            return;
        }

        loadForm(project);
        notifySuccess('Saved');
    } catch (error) {
        if (error instanceof ApiError) {
            errors.title = error.errors?.title?.[0] ?? null;
            errors.slug = error.errors?.slug?.[0] ?? null;
            errors.form = (!errors.title && !errors.slug)
                ? (error.message || 'تعذر حفظ المشروع.')
                : null;

            if (errors.title) {
                switchTab('edit');
            } else if (errors.slug) {
                switchTab('advanced');
            }
        } else {
            errors.form = 'تعذر حفظ المشروع.';
        }

        notifyApiError(error, 'تعذر حفظ المشروع.');
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
                        to="/manage/portfolio"
                        title="رجوع"
                        class="flex shrink-0 items-center justify-center rounded-lg bg-white p-2 shadow-sm hover:bg-stone-50"
                    >
                        <svg class="h-5 w-5 text-stone-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </RouterLink>
                    <div class="flex min-w-0 items-center gap-2 text-sm text-stone-700">
                        <img v-if="store.type?.icon" :src="`/${store.type.icon}`" class="h-5 w-5 shrink-0" alt="">
                        <span class="truncate font-semibold">{{ store.type?.name }}</span>
                        <span class="text-stone-400 hidden md:inline">/</span>
                        <span class="truncate text-stone-600 hidden md:inline">تحرير المشروع</span>
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
                        :class="formTab === 'advanced' ? 'bg-white font-semibold text-stone-900 shadow-sm' : 'text-stone-600 hover:bg-white/60 hover:text-stone-800'"
                        @click.prevent.stop="switchTab('advanced')"
                    >
                        متقدم
                    </button>
                </nav>
            </div>

            <Form class="!rounded-none !p-4 md:!p-6" @submit="save">
                <p v-if="errors.form" class="mb-3 text-sm text-red-600">{{ errors.form }}</p>

                <div
                    class="space-y-2"
                    :class="formTab === 'edit' ? 'relative z-0 block' : 'hidden'"
                >
                    <Input
                        v-model="form.title"
                        name="title"
                        placeholder="عنوان المشروع"
                        :error="errors.title"
                    />

                    <MediaGallery
                        v-model="form.images"
                        :uploading="uploading"
                        :disabled="store.saving"
                        @upload="uploadFiles"
                        @remove="removeImage"
                        @reorder="reorderImages"
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

                <div
                    class="space-y-2"
                    :class="formTab === 'advanced' ? 'relative z-10 block' : 'hidden'"
                >                    <Textarea
                        v-model="form.subtitle"
                        name="subtitle"
                        placeholder="عنوان فرعي"
                        info="عنوان فرعي يظهر تحت العنوان الرئيسي في صفحة المشروع وقائمة الأعمال."
                        :rows="2"
                    />

                    <div class="relative rounded-md bg-stone-100/75 p-1 lg:flex lg:items-start lg:gap-x-2">
                        <span class="inline-block w-36 flex-shrink-0 p-2 text-sm font-semibold text-stone-500">القسم</span>
                        <div class="w-full space-y-1.5 p-2">
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

                    <Input
                        v-model="form.slug"
                        name="slug"
                        label="نص الرابط"
                        dir="ltr"
                        :prefix="slugPrefix"
                        :error="errors.slug"
                    />

                    <Toggle v-model="form.published" name="published" label="حالة النشر" />
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
