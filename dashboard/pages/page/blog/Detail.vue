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
import NotFound from '../../NotFound.vue';
import { useBlogStore } from '../../../stores/blog.js';
import { ApiError } from '../../../lib/api.js';
import { notifySuccess, notifyApiError } from '../../../lib/notify.js';

const route = useRoute();
const router = useRouter();
const store = useBlogStore();
const formTab = ref('edit');
const uploadingImage = ref(false);
const notFound = ref(false);
const bodyEditor = ref(null);
const featuredInput = ref(null);
const bodySeed = ref('');

const form = reactive({
    title: '',
    subtitle: '',
    slug: '',
    categoryIds: [],
    published: false,
    featuredImage: null,
});

const errors = reactive({
    title: null,
    slug: null,
    form: null,
});

const uuid = computed(() => String(route.params.id));
const editorUploadUrl = computed(() => `/api/blog/${uuid.value}/editor-images`);
const categories = computed(() => store.detail?.category_options ?? []);
const slugPrefix = computed(() => store.detail?.slug_prefix ?? '/blog/');

function switchTab(tab) {
    formTab.value = tab;
}

function loadForm(post, { syncEditor = true } = {}) {
    if (!post) {
        return;
    }

    form.title = post.title ?? '';
    form.subtitle = post.subtitle ?? '';
    form.slug = post.slug ?? '';
    form.categoryIds = [...(post.category_ids ?? [])].map(String);
    form.published = Boolean(post.published);
    form.featuredImage = post.featured_image ?? null;
    errors.title = null;
    errors.slug = null;
    errors.form = null;

    if (syncEditor) {
        bodySeed.value = post.body ?? '';
        nextTick(() => {
            bodyEditor.value?.setData?.(bodySeed.value);
        });
    }
}

onMounted(async () => {
    try {
        const post = await store.fetchPost(uuid.value);
        loadForm(post);
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
        const post = await store.fetchPost(String(id));
        loadForm(post);
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

function openFeaturedPicker() {
    featuredInput.value?.click();
}

async function onFeaturedSelected(event) {
    const file = event.target.files?.[0];

    if (!file) {
        return;
    }

    uploadingImage.value = true;
    errors.form = null;

    try {
        form.featuredImage = await store.uploadFeaturedImage(uuid.value, file);
    } catch (error) {
        errors.form = error instanceof ApiError ? error.message : 'تعذر رفع الصورة.';
    } finally {
        uploadingImage.value = false;
        event.target.value = '';
    }
}

async function removeFeaturedImage() {
    try {
        form.featuredImage = await store.deleteFeaturedImage(uuid.value);
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

    errors.title = title ? null : 'عنوان التدوينة مطلوب.';
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
        const post = await store.updatePost(uuid.value, {
            title,
            subtitle: form.subtitle.trim(),
            body,
            slug,
            category_ids: categoryIds,
            published: Boolean(form.published),
            editor_mode: 'html',
        });

        if (close) {
            router.push('/manage/blog');
            return;
        }

        loadForm(post);
        notifySuccess('Saved');
    } catch (error) {
        if (error instanceof ApiError) {
            errors.title = error.errors?.title?.[0] ?? null;
            errors.slug = error.errors?.slug?.[0] ?? null;
            errors.form = (!errors.title && !errors.slug)
                ? (error.message || 'تعذر حفظ التدوينة.')
                : null;

            if (errors.title) {
                switchTab('edit');
            } else if (errors.slug) {
                switchTab('advanced');
            }
        } else {
            errors.form = 'تعذر حفظ التدوينة.';
        }

        notifyApiError(error, 'تعذر حفظ التدوينة.');
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
                        to="/manage/blog"
                        title="رجوع"
                        class="flex shrink-0 items-center justify-center rounded-lg bg-white p-2 shadow-sm hover:bg-gray-50"
                    >
                        <svg class="h-5 w-5 text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </RouterLink>
                    <div class="flex min-w-0 items-center gap-2 text-sm text-gray-700">
                        <img v-if="store.type?.icon" :src="`/${store.type.icon}`" class="h-5 w-5 shrink-0" alt="">
                        <span class="truncate font-semibold">{{ store.type?.name }}</span>
                        <span class="text-gray-400 hidden md:inline">/</span>
                        <span class="truncate text-gray-600 hidden md:inline">تحرير التدوينة</span>
                    </div>
                </div>

                <nav class="relative z-20 flex shrink-0 items-center gap-1 rounded-xl bg-gray-300/40 p-0.5">
                    <button
                        type="button"
                        class="flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm transition"
                        :class="formTab === 'edit' ? 'bg-white font-semibold text-gray-900 shadow-sm' : 'text-gray-600 hover:bg-white/60 hover:text-gray-800'"
                        @click.prevent.stop="switchTab('edit')"
                    >
                        تحرير
                    </button>
                    <button
                        type="button"
                        class="flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm transition"
                        :class="formTab === 'advanced' ? 'bg-white font-semibold text-gray-900 shadow-sm' : 'text-gray-600 hover:bg-white/60 hover:text-gray-800'"
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
                        placeholder="عنوان التدوينة"
                        :error="errors.title"
                    />

                    <Textarea
                        v-model="form.subtitle"
                        name="subtitle"
                        placeholder="عنوان فرعي"
                        info="عنوان فرعي يظهر تحت العنوان الرئيسي في الصفحة الرئيسية وعند عرض التدوينات."
                        :rows="2"
                    />

                    <div class="relative rounded-md bg-gray-100/75 p-1 lg:flex lg:items-start lg:gap-x-2">
                        <span class="inline-block w-36 flex-shrink-0 p-2 text-sm font-semibold text-gray-500">الصورة الرئيسية</span>
                        <div class="w-full space-y-2 p-2">
                            <img
                                v-if="form.featuredImage"
                                :src="form.featuredImage"
                                class="mb-2 w-full max-w-sm rounded-xl object-cover"
                                alt=""
                            >
                            <div class="flex flex-wrap items-center gap-2">
                                <input
                                    ref="featuredInput"
                                    type="file"
                                    accept="image/*"
                                    class="hidden"
                                    @change="onFeaturedSelected"
                                >
                                <Button
                                    type="button"
                                    variant="secondary"
                                    :label="uploadingImage ? 'جاري الرفع…' : 'رفع الصورة'"
                                    :disabled="uploadingImage || store.saving"
                                    @click="openFeaturedPicker"
                                />
                                <Button
                                    v-if="form.featuredImage"
                                    type="button"
                                    variant="secondary"
                                    label="حذف الصورة"
                                    :disabled="store.saving"
                                    @click="removeFeaturedImage"
                                />
                            </div>
                        </div>
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

                <div
                    class="space-y-2"
                    :class="formTab === 'advanced' ? 'relative z-10 block' : 'hidden'"
                >
                    <div class="relative rounded-md bg-gray-100/75 p-1 lg:flex lg:items-start lg:gap-x-2">
                        <span class="inline-block w-36 flex-shrink-0 p-2 text-sm font-semibold text-gray-500">القسم</span>
                        <div class="w-full space-y-1.5 p-2">
                            <label
                                v-for="option in categories"
                                :key="option.id"
                                class="flex items-center gap-2 text-sm"
                                :class="option.selectable ? 'text-gray-700' : 'text-gray-400'"
                            >
                                <input
                                    type="checkbox"
                                    class="h-4 w-4 rounded border-gray-300"
                                    :disabled="!option.selectable"
                                    :checked="form.categoryIds.includes(String(option.id))"
                                    @change="toggleCategory(option.id, $event.target.checked)"
                                >
                                <span>{{ option.label }}</span>
                            </label>
                            <p v-if="categories.length === 0" class="text-xs text-gray-400">لا توجد تصنيفات بعد.</p>
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
