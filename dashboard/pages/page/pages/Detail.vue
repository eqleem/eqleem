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
// import PageBlocksPanel from '../../../components/page/pages/PageBlocksPanel.vue'; // temporarily hidden
import NotFound from '../../NotFound.vue';
import { usePagesStore } from '../../../stores/pages.js';
import { ApiError } from '../../../lib/api.js';
import { notifySuccess, notifyApiError } from '../../../lib/notify.js';

const route = useRoute();
const router = useRouter();
const store = usePagesStore();
const formTab = ref('edit');
// const contentTab = ref('text'); // temporarily unused (text/blocks tabs hidden)
const notFound = ref(false);
const bodyEditor = ref(null);
const bodySeed = ref('');
const editorSeeded = ref(false);

const form = reactive({
    title: '',
    subtitle: '',
    slug: '',
    published: false,
});

const errors = reactive({
    title: null,
    slug: null,
    form: null,
});

const uuid = computed(() => String(route.params.id));
const editorUploadUrl = computed(() => `/api/pages/${uuid.value}/editor-images`);
const slugPrefix = computed(() => store.detail?.slug_prefix ?? '/');

function switchFormTab(tab) {
    formTab.value = tab;
}

// function switchContentTab(tab) {
//     contentTab.value = tab;
// }

function loadForm(page, { syncEditor = true } = {}) {
    if (!page) {
        return;
    }

    form.title = page.title ?? '';
    form.subtitle = page.subtitle ?? '';
    form.slug = page.slug ?? '';
    form.published = Boolean(page.published);
    errors.title = null;
    errors.slug = null;
    errors.form = null;

    if (syncEditor) {
        bodySeed.value = page.body ?? '';
        editorSeeded.value = true;
        nextTick(() => {
            bodyEditor.value?.setData?.(bodySeed.value);
        });
    }
}

onMounted(async () => {
    try {
        const page = await store.fetchPage(uuid.value);

        if (page?.template === 'contact') {
            router.replace(`/manage/pages/contact/${page.uuid}`);
            return;
        }

        if (page?.template === 'faq') {
            router.replace(`/manage/pages/faq/${page.uuid}`);
            return;
        }

        loadForm(page);
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
    editorSeeded.value = false;

    try {
        const page = await store.fetchPage(String(id));

        if (page?.template === 'contact') {
            router.replace(`/manage/pages/contact/${page.uuid}`);
            return;
        }

        if (page?.template === 'faq') {
            router.replace(`/manage/pages/faq/${page.uuid}`);
            return;
        }

        loadForm(page);
    } catch (error) {
        notFound.value = error instanceof ApiError && error.status === 404;
    }
});

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

    errors.title = title ? null : 'عنوان الصفحة مطلوب.';
    errors.slug = slug ? null : 'نص الرابط مطلوب.';
    errors.form = null;

    if (errors.title || errors.slug) {
        switchFormTab(errors.title ? 'edit' : 'advanced');
        return;
    }

    try {
        const page = await store.updatePage(uuid.value, {
            title,
            subtitle: form.subtitle.trim(),
            body,
            slug,
            published: Boolean(form.published),
            editor_mode: 'html',
        });

        if (close) {
            router.push('/manage/pages');
            return;
        }

        loadForm(page);
        notifySuccess('Saved');
    } catch (error) {
        if (error instanceof ApiError) {
            errors.title = error.errors?.title?.[0] ?? null;
            errors.slug = error.errors?.slug?.[0] ?? null;
            errors.form = (!errors.title && !errors.slug)
                ? (error.message || 'تعذر حفظ الصفحة.')
                : null;

            if (errors.title) {
                switchFormTab('edit');
            } else if (errors.slug) {
                switchFormTab('advanced');
            }
        } else {
            errors.form = 'تعذر حفظ الصفحة.';
        }

        notifyApiError(error, 'تعذر حفظ الصفحة.');
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
                        to="/manage/pages"
                        title="رجوع"
                        class="flex shrink-0 items-center justify-center rounded-lg bg-white p-2 shadow-sm hover:bg-stone-50"
                    >
                        <svg class="h-5 w-5 text-stone-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </RouterLink>
                    <div class="flex min-w-0 items-center gap-2 text-sm text-stone-700">
                        <img v-if="store.type?.icon" :src="`/${store.type.icon}`" class="h-5 w-5 shrink-0" alt="">
                        <span class="truncate font-semibold">{{ store.type?.name }}</span>
                        <span class="text-stone-400 hidden md:inline">/</span>
                        <span class="truncate text-stone-600 hidden md:inline">تحرير الصفحة</span>
                    </div>
                </div>

                <nav class="relative z-20 flex shrink-0 items-center gap-1 rounded-xl bg-stone-300/40 p-0.5">
                    <button
                        type="button"
                        class="flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm transition"
                        :class="formTab === 'edit' ? 'bg-white font-semibold text-stone-900 shadow-sm' : 'text-stone-600 hover:bg-white/60 hover:text-stone-800'"
                        @click.prevent.stop="switchFormTab('edit')"
                    >
                        تحرير
                    </button>
                    <button
                        type="button"
                        class="flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm transition"
                        :class="formTab === 'advanced' ? 'bg-white font-semibold text-stone-900 shadow-sm' : 'text-stone-600 hover:bg-white/60 hover:text-stone-800'"
                        @click.prevent.stop="switchFormTab('advanced')"
                    >
                        متقدم
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
                            placeholder="عنوان الصفحة"
                            :error="errors.title"
                        />

                        <Textarea
                            v-model="form.subtitle"
                            name="subtitle"
                            placeholder="عنوان فرعي"
                            info="عنوان فرعي يظهر تحت العنوان الرئيسي في الصفحة."
                            :rows="2"
                        />
                    </div>

                    <div class="space-y-3">
                        <!-- Temporarily hide text/blocks tabs; editor only -->
                        <CkEditor
                            v-if="editorUploadUrl && editorSeeded"
                            ref="bodyEditor"
                            :key="uuid"
                            :model-value="bodySeed"
                            name="body"
                            :upload-url="editorUploadUrl"
                        />
                    </div>
                </div>

                <div
                    class="space-y-2"
                    :class="formTab === 'advanced' ? 'relative z-10 block' : 'hidden'"
                >
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
