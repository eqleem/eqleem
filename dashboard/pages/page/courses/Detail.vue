<script setup>
import { computed, nextTick, onMounted, reactive, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import ManageLayout from '../../../components/page/ManageLayout.vue';
import Form from '../../../components/ui/Form.vue';
import Input from '../../../components/ui/Input.vue';
import Textarea from '../../../components/ui/Textarea.vue';
import Button from '../../../components/ui/Button.vue';
import Toggle from '../../../components/ui/Toggle.vue';
import Alert from '../../../components/ui/Alert.vue';
import CkEditor from '../../../components/ui/CkEditor.vue';
import MediaGallery from '../../../components/ui/MediaGallery.vue';
import NotFound from '../../NotFound.vue';
import { useCoursesStore } from '../../../stores/courses.js';
import { ApiError } from '../../../lib/api.js';
import { notifySuccess, notifyApiError } from '../../../lib/notify.js';

const route = useRoute(); 
const router = useRouter();
const store = useCoursesStore();
const formTab = ref('edit');
const uploading = ref(false);
const uploadingLesson = ref(null);
const notFound = ref(false);
const bodyEditor = ref(null);
const bodySeed = ref('');
const openChapters = ref({});
const openLessons = ref({});

const form = reactive({
    title: '',
    subtitle: '',
    slug: '',
    price: '',
    comparePrice: '',
    hours: '0',
    level: 'beginner',
    courseType: 'recorded',
    categoryIds: [],
    published: false,
    images: [],
    chapters: [],
});

const errors = reactive({
    title: null,
    slug: null,
    form: null,
});

const uuid = computed(() => String(route.params.id));
const editorUploadUrl = computed(() => `/api/courses/${uuid.value}/editor-images`);
const categories = computed(() => store.detail?.category_options ?? []);
const slugPrefix = computed(() => store.detail?.slug_prefix ?? '/courses/');
const levelOptions = computed(() => store.detail?.level_options ?? {});
const courseTypeOptions = computed(() => store.detail?.course_type_options ?? {});

const totalLessons = computed(() => form.chapters.reduce((sum, chapter) => sum + (chapter.lessons?.length ?? 0), 0));

function newLessonId() {
    return crypto.randomUUID();
}

function newChapterId() {
    return crypto.randomUUID();
}

function blankLesson() {
    return {
        id: newLessonId(),
        title: '',
        description: '',
        source: 'file',
        link: '',
        media_id: null,
        file_name: '',
        file_url: '',
    };
}

function blankChapter() {
    return {
        id: newChapterId(),
        title: '',
        description: '',
        lessons: [],
    };
}

function switchTab(tab) {
    formTab.value = tab;
}

function cloneChapters(chapters) {
    return (chapters ?? []).map((chapter) => ({
        id: chapter.id ?? newChapterId(),
        title: chapter.title ?? '',
        description: chapter.description ?? '',
        lessons: (chapter.lessons ?? []).map((lesson) => ({
            id: lesson.id ?? newLessonId(),
            title: lesson.title ?? '',
            description: lesson.description ?? '',
            source: lesson.source === 'link' ? 'link' : 'file',
            link: lesson.link ?? '',
            media_id: lesson.media_id ?? null,
            file_name: lesson.file_name ?? '',
            file_url: lesson.file_url ?? '',
        })),
    }));
}

function loadForm(course, { syncEditor = true } = {}) {
    if (!course) {
        return;
    }

    form.title = course.title ?? '';
    form.subtitle = course.subtitle ?? '';
    form.slug = course.slug ?? '';
    form.price = course.price ?? '';
    form.comparePrice = course.compare_price ?? '';
    form.hours = course.hours ?? '0';
    form.level = course.level ?? 'beginner';
    form.courseType = course.course_type ?? 'recorded';
    form.categoryIds = [...(course.category_ids ?? [])].map(String);
    form.published = Boolean(course.published);
    form.images = [...(course.images ?? [])];
    form.chapters = cloneChapters(course.chapters);
    errors.title = null;
    errors.slug = null;
    errors.form = null;
    openChapters.value = {};
    openLessons.value = {};

    if (syncEditor) {
        bodySeed.value = course.body ?? '';
        nextTick(() => {
            bodyEditor.value?.setData?.(bodySeed.value);
        });
    }
}

onMounted(async () => {
    try {
        const course = await store.fetchCourse(uuid.value);
        loadForm(course);
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
        const course = await store.fetchCourse(String(id));
        loadForm(course);
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

function addChapter() {
    form.chapters.push(blankChapter());
    openChapters.value[form.chapters.length - 1] = true;
}

function removeChapter(index) {
    if (!confirm('هل أنت متأكد من حذف هذا الفصل وجميع دروسه؟')) {
        return;
    }

    form.chapters.splice(index, 1);
}

function addLesson(chapterIndex) {
    const chapter = form.chapters[chapterIndex];

    if (!chapter) {
        return;
    }

    chapter.lessons.push(blankLesson());
    openLessons.value[`${chapterIndex}-${chapter.lessons.length - 1}`] = true;
}

async function removeLesson(chapterIndex, lessonIndex) {
    if (!confirm('هل أنت متأكد من حذف هذا الدرس؟')) {
        return;
    }

    const lesson = form.chapters[chapterIndex]?.lessons?.[lessonIndex];

    if (lesson?.media_id) {
        try {
            await store.deleteLessonFile(uuid.value, lesson.media_id);
        } catch (error) {
            errors.form = error instanceof ApiError ? error.message : 'تعذر حذف ملف الدرس.';
            return;
        }
    }

    form.chapters[chapterIndex].lessons.splice(lessonIndex, 1);
}

async function uploadCover(files) {
    uploading.value = true;

    try {
        for (const file of files.slice(0, 1)) {
            form.images = await store.uploadCoverImage(uuid.value, file);
        }
    } catch (error) {
        errors.form = error instanceof ApiError ? error.message : 'تعذر رفع الصورة.';
    } finally {
        uploading.value = false;
    }
}

async function removeCover(mediaId) {
    try {
        form.images = await store.deleteCoverImage(uuid.value, mediaId);
    } catch (error) {
        errors.form = error instanceof ApiError ? error.message : 'تعذر حذف الصورة.';
    }
}

async function uploadLessonFile(chapterIndex, lessonIndex, event) {
    const file = event.target.files?.[0];
    event.target.value = '';

    if (!file) {
        return;
    }

    const chapter = form.chapters[chapterIndex];
    const lesson = chapter?.lessons?.[lessonIndex];

    if (!chapter || !lesson) {
        return;
    }

    const key = `${chapterIndex}-${lessonIndex}`;
    uploadingLesson.value = key;

    try {
        const result = await store.uploadLessonFile(uuid.value, {
            chapterId: chapter.id,
            lessonId: lesson.id,
            file,
        });

        if (result) {
            lesson.source = 'file';
            lesson.media_id = result.media_id;
            lesson.file_name = result.file_name;
            lesson.file_url = result.file_url;
            lesson.link = '';
        }
    } catch (error) {
        errors.form = error instanceof ApiError ? error.message : 'تعذر رفع ملف الدرس.';
    } finally {
        uploadingLesson.value = null;
    }
}

async function removeLessonFile(chapterIndex, lessonIndex) {
    const lesson = form.chapters[chapterIndex]?.lessons?.[lessonIndex];

    if (!lesson?.media_id) {
        lesson.file_name = '';
        lesson.file_url = '';
        return;
    }

    try {
        await store.deleteLessonFile(uuid.value, lesson.media_id);
        lesson.media_id = null;
        lesson.file_name = '';
        lesson.file_url = '';
    } catch (error) {
        errors.form = error instanceof ApiError ? error.message : 'تعذر حذف ملف الدرس.';
    }
}

function readBody() {
    try {
        return bodyEditor.value?.getData?.() ?? bodySeed.value ?? '';
    } catch {
        return bodySeed.value ?? '';
    }
}

function serializeChaptersPayload() {
    return form.chapters.map((chapter) => ({
        id: chapter.id,
        title: chapter.title.trim(),
        description: chapter.description.trim(),
        lessons: (chapter.lessons ?? []).map((lesson) => ({
            id: lesson.id,
            title: lesson.title.trim(),
            description: lesson.description.trim(),
            source: lesson.source === 'link' ? 'link' : 'file',
            link: lesson.source === 'link' ? lesson.link.trim() : '',
            media_id: lesson.media_id ?? null,
            file_name: lesson.file_name ?? '',
            file_url: lesson.file_url ?? '',
        })),
    }));
}

async function persist({ close = false } = {}) {
    const body = readBody();
    bodySeed.value = body;

    const title = form.title.trim();
    const slug = form.slug.trim();

    errors.title = title ? null : 'اسم الدورة مطلوب.';
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

    const payload = {
        title,
        subtitle: form.subtitle.trim(),
        body,
        slug,
        hours: form.hours !== '' ? Number(form.hours) : 0,
        level: form.level,
        course_type: form.courseType,
        category_ids: categoryIds,
        published: Boolean(form.published),
        editor_mode: 'html',
        chapters: serializeChaptersPayload(),
    };

    if (form.price !== '') {
        payload.price = Number(form.price);
    }

    if (form.comparePrice !== '') {
        payload.compare_price = Number(form.comparePrice);
    }

    try {
        const course = await store.updateCourse(uuid.value, payload);

        if (close) {
            router.push('/manage/courses');
            return;
        }

        loadForm(course);
        notifySuccess('Saved');
    } catch (error) {
        if (error instanceof ApiError) {
            errors.title = error.errors?.title?.[0] ?? null;
            errors.slug = error.errors?.slug?.[0] ?? null;
            errors.form = (!errors.title && !errors.slug)
                ? (error.message || 'تعذر حفظ الدورة.')
                : null;

            if (errors.title) {
                switchTab('edit');
            } else if (errors.slug) {
                switchTab('advanced');
            }
        } else {
            errors.form = 'تعذر حفظ الدورة.';
        }

        notifyApiError(error, 'تعذر حفظ الدورة.');
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
                        to="/manage/courses"
                        title="رجوع"
                        class="flex shrink-0 items-center justify-center rounded-lg bg-white p-2 shadow-sm hover:bg-stone-50"
                    >
                        <svg class="h-5 w-5 text-stone-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </RouterLink>
                    <div class="flex min-w-0 items-center gap-2 text-sm text-stone-700">
                        <img v-if="store.type?.icon" :src="`/${store.type.icon}`" class="h-5 w-5 shrink-0" alt="">
                        <span class="truncate font-semibold">{{ store.type?.name }}</span>
                        <span class="text-stone-400 hidden md:inline">/</span>
                        <span class="truncate text-stone-600 hidden md:inline">تحرير الدورة</span>
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
                        :class="formTab === 'curriculum' ? 'bg-white font-semibold text-stone-900 shadow-sm' : 'text-stone-600 hover:bg-white/60 hover:text-stone-800'"
                        @click.prevent.stop="switchTab('curriculum')"
                    >
                        المحتوى التعليمي
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

                <div class="space-y-2" :class="formTab === 'edit' ? 'relative z-0 block' : 'hidden'">
                    <Input v-model="form.title" name="title" label="اسم الدورة" placeholder="مثال: مهارات المحادثة باللغة الإنجليزية" :error="errors.title" />

                    <Textarea
                        v-model="form.subtitle"
                        name="subtitle"
                        label="عنوان ترويجي"
                        placeholder="مثال: مقدمة شاملة تأخذك من ما قبل البداية إلى مرحلة الإنطلاقة"
                        info="عنوان فرعي لا يزيد عن 300 حرف."
                        maxlength="300"
                        :rows="2"
                    />

                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                        <Input v-model="form.price" name="price" label="السعر" type="number" dir="ltr" step="0.01" min="0" placeholder="0.00" />
                        <Input v-model="form.comparePrice" name="comparePrice" label="سعر المقارنة" type="number" dir="ltr" step="0.01" min="0" placeholder="0.00" />
                    </div>

                    <MediaGallery
                        v-model="form.images"
                        label="الصورة"
                        :max-files="1"
                        :sortable="false"
                        :uploading="uploading"
                        :disabled="store.saving"
                        @upload="uploadCover"
                        @remove="removeCover"
                    />

                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                        <div class="rounded-md bg-stone-100/75 p-1 lg:flex lg:items-start lg:gap-x-2">
                            <span class="inline-block w-36 flex-shrink-0 p-2 text-sm font-semibold text-stone-500">نوع الدورة</span>
                            <div class="w-full p-2">
                                <select v-model="form.courseType" class="block w-full rounded-lg border-stone-300 text-sm">
                                    <option v-for="(label, key) in courseTypeOptions" :key="key" :value="key">{{ label }}</option>
                                </select>
                            </div>
                        </div>
                        <Input v-model="form.hours" name="hours" label="عدد ساعات الدورة" type="number" dir="ltr" step="0.5" min="0" placeholder="0" />
                    </div>

                    <div class="rounded-md bg-stone-100/75 p-1 lg:flex lg:items-start lg:gap-x-2">
                        <span class="inline-block w-36 flex-shrink-0 p-2 text-sm font-semibold text-stone-500">المستوى</span>
                        <div class="flex w-full flex-wrap gap-3 p-2">
                            <label v-for="(label, key) in levelOptions" :key="key" class="flex items-center gap-2 text-sm text-stone-700">
                                <input v-model="form.level" type="radio" name="level" :value="key" class="h-4 w-4 border-stone-300">
                                <span>{{ label }}</span>
                            </label>
                        </div>
                    </div>

                    <CkEditor v-if="editorUploadUrl" ref="bodyEditor" :key="uuid" :model-value="bodySeed" name="body" :upload-url="editorUploadUrl" />
                </div>

                <div class="space-y-4" :class="formTab === 'curriculum' ? 'relative z-0 block' : 'hidden'">
                    <Alert
                        color="blue"
                        heading="المحتوى التعليمي"
                        text="نظّم الدورة في فصول (أقسام) ودروس. لكل درس يمكنك رفع ملف أو إضافة رابط خارجي."
                    />

                    <div class="flex items-center justify-between gap-3">
                        <p class="text-sm text-stone-600">
                            {{ form.chapters.length }} {{ form.chapters.length === 1 ? 'فصل' : 'فصول' }}
                            — {{ totalLessons }} {{ totalLessons === 1 ? 'درس' : 'دروس' }}
                        </p>
                        <Button type="button" variant="secondary" label="إضافة فصل" @click="addChapter" />
                    </div>

                    <div v-if="form.chapters.length === 0" class="rounded-xl border border-dashed border-stone-200 p-8 text-center text-sm text-stone-500">
                        لا يوجد محتوى تعليمي بعد. أضف فصولاً ودروساً لبناء محتوى الدورة.
                    </div>

                    <div v-else class="space-y-3">
                        <div
                            v-for="(chapter, chapterIndex) in form.chapters"
                            :key="chapter.id"
                            class="overflow-hidden rounded-xl border border-stone-200 bg-stone-50/60"
                        >
                            <div class="flex items-center justify-between gap-3 border-b border-stone-200 bg-white px-4 py-3">
                                <button type="button" class="flex min-w-0 items-center gap-2 text-start" @click="openChapters[chapterIndex] = !openChapters[chapterIndex]">
                                    <span class="truncate text-sm font-semibold text-stone-800">
                                        فصل {{ chapterIndex + 1 }}
                                        <template v-if="chapter.title"> — {{ chapter.title }}</template>
                                    </span>
                                    <span class="shrink-0 text-xs text-stone-500">{{ chapter.lessons.length }} دروس</span>
                                </button>
                                <div class="flex shrink-0 items-center gap-1">
                                    <button type="button" class="rounded-lg px-2.5 py-1.5 text-sm text-stone-600 hover:bg-primary-50 hover:text-primary-600" @click="addLesson(chapterIndex)">
                                        إضافة درس
                                    </button>
                                    <button type="button" class="rounded-lg p-1.5 text-stone-400 hover:bg-red-50 hover:text-red-600" @click="removeChapter(chapterIndex)">
                                        حذف
                                    </button>
                                </div>
                            </div>

                            <div v-show="openChapters[chapterIndex] !== false" class="space-y-3 p-4">
                                <Input v-model="chapter.title" :name="`chapter-title-${chapterIndex}`" label="عنوان الفصل" placeholder="مثال: الأساسيات والتحضير" />
                                <Textarea v-model="chapter.description" :name="`chapter-desc-${chapterIndex}`" label="وصف الفصل" :rows="2" />

                                <div v-if="chapter.lessons.length === 0" class="text-sm text-stone-500">لا توجد دروس في هذا الفصل بعد.</div>

                                <div v-else class="space-y-2">
                                    <div
                                        v-for="(lesson, lessonIndex) in chapter.lessons"
                                        :key="lesson.id"
                                        class="overflow-hidden rounded-lg border border-stone-200 bg-white"
                                    >
                                        <div class="flex items-center justify-between gap-3 border-b border-stone-100 bg-stone-50 px-3 py-2.5">
                                            <button type="button" class="flex min-w-0 flex-1 items-center gap-2 text-start" @click="openLessons[`${chapterIndex}-${lessonIndex}`] = !openLessons[`${chapterIndex}-${lessonIndex}`]">
                                                <span class="truncate text-sm font-semibold text-stone-700">
                                                    درس {{ lessonIndex + 1 }}
                                                    <template v-if="lesson.title"> — {{ lesson.title }}</template>
                                                </span>
                                                <span v-if="lesson.source === 'link' && lesson.link" class="shrink-0 rounded-md bg-primary-50 px-2 py-0.5 text-xs text-primary-600">رابط</span>
                                                <span v-else-if="lesson.file_name" class="shrink-0 rounded-md bg-emerald-50 px-2 py-0.5 text-xs text-emerald-700">ملف</span>
                                            </button>
                                            <button type="button" class="shrink-0 rounded-lg p-1 text-stone-400 hover:bg-red-50 hover:text-red-600" @click="removeLesson(chapterIndex, lessonIndex)">
                                                حذف
                                            </button>
                                        </div>

                                        <div v-show="openLessons[`${chapterIndex}-${lessonIndex}`]" class="space-y-3 p-4">
                                            <Input v-model="lesson.title" :name="`lesson-title-${chapterIndex}-${lessonIndex}`" label="عنوان الدرس" />
                                            <Textarea v-model="lesson.description" :name="`lesson-desc-${chapterIndex}-${lessonIndex}`" label="وصف الدرس" :rows="2" />

                                            <div class="rounded-md bg-stone-100/75 p-1 lg:flex lg:items-start lg:gap-x-2">
                                                <span class="inline-block w-36 flex-shrink-0 p-2 text-sm font-semibold text-stone-500">مصدر المحتوى</span>
                                                <div class="w-full p-2">
                                                    <select v-model="lesson.source" class="block w-full rounded-lg border-stone-300 text-sm">
                                                        <option value="file">رفع ملف</option>
                                                        <option value="link">رابط خارجي</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <Input
                                                v-if="lesson.source === 'link'"
                                                v-model="lesson.link"
                                                :name="`lesson-link-${chapterIndex}-${lessonIndex}`"
                                                label="رابط الدرس"
                                                placeholder="https://www.youtube.com/watch?v=..."
                                                dir="ltr"
                                            />

                                            <div v-else class="space-y-2">
                                                <div v-if="lesson.file_name" class="flex items-center gap-3 rounded-lg border border-stone-200 bg-stone-50 px-3 py-2">
                                                    <div class="min-w-0 flex-1">
                                                        <p class="truncate text-sm font-medium text-stone-800">{{ lesson.file_name }}</p>
                                                        <a v-if="lesson.file_url" :href="lesson.file_url" target="_blank" class="text-xs text-primary-600 hover:underline">معاينة الملف</a>
                                                    </div>
                                                    <button type="button" class="rounded-lg p-1 text-stone-400 hover:bg-red-50 hover:text-red-600" @click="removeLessonFile(chapterIndex, lessonIndex)">
                                                        حذف الملف
                                                    </button>
                                                </div>
                                                <p v-else class="text-sm text-stone-500">لم يتم رفع ملف بعد.</p>
                                                <label class="inline-flex cursor-pointer items-center gap-2 rounded-lg border bg-white px-3 py-2 text-sm text-stone-700 shadow-sm hover:bg-primary-50">
                                                    <span>{{ lesson.file_name ? 'استبدال الملف' : 'رفع ملف الدرس' }}</span>
                                                    <input
                                                        type="file"
                                                        class="hidden"
                                                        :disabled="uploadingLesson === `${chapterIndex}-${lessonIndex}`"
                                                        @change="uploadLessonFile(chapterIndex, lessonIndex, $event)"
                                                    >
                                                </label>
                                                <p v-if="uploadingLesson === `${chapterIndex}-${lessonIndex}`" class="text-xs text-stone-500">جاري الرفع…</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <Button type="button" variant="secondary" label="إضافة درس" @click="addLesson(chapterIndex)" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-2" :class="formTab === 'advanced' ? 'relative z-10 block' : 'hidden'">
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

                    <Input v-model="form.slug" name="slug" label="نص الرابط" dir="ltr" :prefix="slugPrefix" :error="errors.slug" />
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
