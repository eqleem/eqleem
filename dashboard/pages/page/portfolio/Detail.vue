<script setup>
import { computed, reactive, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import ManageLayout from '../../../components/page/ManageLayout.vue';
import Form from '../../../components/ui/Form.vue';
import Input from '../../../components/ui/Input.vue';
import Textarea from '../../../components/ui/Textarea.vue';
import Button from '../../../components/ui/Button.vue';
import NotFound from '../../NotFound.vue';
import {
    findProject,
    updateProject,
    categoryOptions,
    portfolioType,
} from '../../../data/portfolio.js';

// Port of resources/views/admin/page/content/portfolio/detail.blade.php (dummy data).
const route = useRoute();
const router = useRouter();
const formTab = ref('edit');
const saved = ref(false);

const project = computed(() => findProject(route.params.id));

const form = reactive({
    title: '',
    subtitle: '',
    body: '',
    slug: '',
    categoryIds: [],
    published: false,
});

const errors = reactive({
    title: null,
    slug: null,
});

const categories = computed(() => categoryOptions());

function loadForm() {
    if (!project.value) {
        return;
    }

    form.title = project.value.title;
    form.subtitle = project.value.subtitle ?? '';
    form.body = project.value.body ?? '';
    form.slug = project.value.slug;
    form.categoryIds = [...(project.value.categoryIds ?? [])];
    form.published = project.value.status === 'published';
    errors.title = null;
    errors.slug = null;
    saved.value = false;
}

watch(project, loadForm, { immediate: true });

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

function persist({ close = false } = {}) {
    const title = form.title.trim();
    const slug = form.slug.trim();

    errors.title = title ? null : 'عنوان المشروع مطلوب.';
    errors.slug = slug ? null : 'نص الرابط مطلوب.';

    if (errors.title || errors.slug) {
        formTab.value = errors.title ? 'edit' : 'advanced';
        return;
    }

    const selectable = new Set(categories.value.filter((item) => item.selectable).map((item) => item.id));
    const categoryIds = form.categoryIds.filter((id) => selectable.has(String(id)));

    updateProject(route.params.id, {
        title,
        subtitle: form.subtitle.trim(),
        body: form.body,
        slug,
        categoryIds,
        status: form.published ? 'published' : 'draft',
        published_at: form.published
            ? (project.value.published_at || new Date().toLocaleDateString('ar-SA', { day: 'numeric', month: 'long', year: 'numeric' }))
            : null,
    });

    if (close) {
        router.push('/manage/portfolio');
        return;
    }

    saved.value = true;
    setTimeout(() => {
        saved.value = false;
    }, 2000);
}

function save() {
    persist({ close: false });
}

function saveAndClose() {
    persist({ close: true });
}
</script>

<template>
    <ManageLayout v-if="project">
        <div class="overflow-hidden rounded-2xl bg-white">
            <div class="flex items-center justify-between gap-4 border-b border-stone-200 bg-stone-200/70 px-4 py-3">
                <div class="flex min-w-0 items-center gap-3">
                    <RouterLink
                        to="/manage/portfolio"
                        title="رجوع"
                        class="flex shrink-0 items-center justify-center rounded-lg bg-white p-2 shadow-sm hover:bg-gray-50"
                    >
                        <svg class="h-5 w-5 text-gray-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </RouterLink>
                    <div class="flex min-w-0 items-center gap-2 text-sm text-gray-700">
                        <img :src="`/${portfolioType.icon}`" class="h-5 w-5 shrink-0" alt="">
                        <span class="truncate font-semibold">{{ portfolioType.name }}</span>
                        <span class="text-gray-400">/</span>
                        <span class="truncate text-gray-600">تحرير المشروع</span>
                    </div>
                </div>

                <nav class="flex shrink-0 items-center gap-1 rounded-xl bg-gray-300/40 p-0.5">
                    <button
                        type="button"
                        class="flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm transition"
                        :class="formTab === 'edit' ? 'bg-white font-semibold text-gray-900 shadow-sm' : 'text-gray-600 hover:bg-white/60 hover:text-gray-800'"
                        @click="formTab = 'edit'"
                    >
                        تحرير
                    </button>
                    <button
                        type="button"
                        class="flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm transition"
                        :class="formTab === 'advanced' ? 'bg-white font-semibold text-gray-900 shadow-sm' : 'text-gray-600 hover:bg-white/60 hover:text-gray-800'"
                        @click="formTab = 'advanced'"
                    >
                        متقدم
                    </button>
                </nav>
            </div>

            <Form class="!rounded-none !p-4 md:!p-6" @submit="save">
                <div v-show="formTab === 'edit'" class="space-y-2">
                    <Input
                        v-model="form.title"
                        name="title"
                        placeholder="عنوان المشروع"
                        :error="errors.title"
                    />

                    <div class="rounded-md bg-gray-100/75 p-3">
                        <p class="mb-2 text-sm font-semibold text-gray-500">صور المشروع</p>
                        <div class="flex flex-wrap gap-2">
                            <div
                                v-if="project.image"
                                class="h-20 w-20 overflow-hidden rounded-lg bg-gray-200"
                            >
                                <img :src="project.image" :alt="project.title" class="h-full w-full object-cover">
                            </div>
                            <div class="flex h-20 w-20 items-center justify-center rounded-lg border border-dashed border-gray-300 bg-white text-center text-xs text-gray-400">
                                رفع الصور<br>(قريباً)
                            </div>
                        </div>
                    </div>

                    <Textarea
                        v-model="form.body"
                        name="body"
                        label="محتوى المشروع"
                        placeholder="اكتب وصف المشروع…"
                        :rows="8"
                        block
                    />
                </div>

                <div v-show="formTab === 'advanced'" class="space-y-2">
                    <Textarea
                        v-model="form.subtitle"
                        name="subtitle"
                        placeholder="عنوان فرعي"
                        info="عنوان فرعي يظهر تحت العنوان الرئيسي في صفحة المشروع وقائمة الأعمال."
                        :rows="2"
                    />

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
                                    :checked="form.categoryIds.includes(option.id)"
                                    @change="toggleCategory(option.id, $event.target.checked)"
                                >
                                <span>{{ option.label }}</span>
                            </label>
                        </div>
                    </div>

                    <Input
                        v-model="form.slug"
                        name="slug"
                        label="نص الرابط"
                        dir="ltr"
                        prefix="/portfolio/"
                        :error="errors.slug"
                    />

                    <div class="relative flex items-center gap-x-2 rounded-md bg-gray-100/75 p-1">
                        <span class="inline-block w-36 flex-shrink-0 p-2 text-sm font-semibold text-gray-500">حالة النشر</span>
                        <label class="flex items-center gap-2 p-2 text-sm text-gray-700">
                            <input v-model="form.published" type="checkbox" class="h-4 w-4 rounded border-gray-300">
                            منشور
                        </label>
                    </div>
                </div>

                <template #footer>
                    <div class="flex items-center gap-2">
                        <span v-if="saved" class="me-auto text-sm text-emerald-600">تم الحفظ.</span>
                        <Button type="button" variant="secondary" label="حفظ وإغلاق" @click="saveAndClose" />
                        <Button type="submit" label="حفظ" />
                    </div>
                </template>
            </Form>
        </div>
    </ManageLayout>
    <NotFound v-else />
</template>
