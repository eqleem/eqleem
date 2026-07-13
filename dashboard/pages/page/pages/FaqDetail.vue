<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import ManageLayout from '../../../components/page/ManageLayout.vue';
import Form from '../../../components/ui/Form.vue';
import Input from '../../../components/ui/Input.vue';
import Textarea from '../../../components/ui/Textarea.vue';
import Button from '../../../components/ui/Button.vue';
import Toggle from '../../../components/ui/Toggle.vue';
import NotFound from '../../NotFound.vue';
import { usePagesStore } from '../../../stores/pages.js';
import { ApiError } from '../../../lib/api.js';
import { notifySuccess, notifyApiError } from '../../../lib/notify.js';

function makeFaq(overrides = {}) {
    return {
        id: overrides.id || `faq_${Date.now()}_${Math.random().toString(36).slice(2, 8)}`,
        question: overrides.question || '',
        answer: overrides.answer || '',
    };
}

const route = useRoute();
const router = useRouter();
const store = usePagesStore();
const formTab = ref('edit');
const notFound = ref(false);

const form = reactive({
    title: '',
    subtitle: '',
    slug: '',
    published: false,
    faqs: [],
});

const errors = reactive({
    title: null,
    slug: null,
    faqs: null,
    form: null,
});

const uuid = computed(() => String(route.params.id));
const slugPrefix = computed(() => store.detail?.slug_prefix ?? '/');

function switchFormTab(tab) {
    formTab.value = tab;
}

function loadForm(page) {
    if (!page) {
        return;
    }

    form.title = page.title ?? '';
    form.subtitle = page.subtitle ?? '';
    form.slug = page.slug ?? '';
    form.published = Boolean(page.published);
    form.faqs = Array.isArray(page.faqs)
        ? page.faqs.map((faq) => makeFaq(faq))
        : [];

    errors.title = null;
    errors.slug = null;
    errors.faqs = null;
    errors.form = null;
}

async function loadPage() {
    try {
        const page = await store.fetchPage(uuid.value);

        if (page?.template && page.template !== 'faq') {
            notFound.value = true;
            return;
        }

        loadForm(page);
    } catch (error) {
        notFound.value = error instanceof ApiError && error.status === 404;
    }
}

onMounted(loadPage);

watch(() => route.params.id, async (id) => {
    if (!id) {
        return;
    }

    notFound.value = false;
    formTab.value = 'edit';
    await loadPage();
});

function addFaq() {
    form.faqs.push(makeFaq());
}

function removeFaq(id) {
    form.faqs = form.faqs.filter((faq) => faq.id !== id);
}

async function persist({ close = false } = {}) {
    const title = form.title.trim();
    const slug = form.slug.trim();
    const faqs = form.faqs
        .map((faq) => ({
            id: faq.id,
            question: faq.question.trim(),
            answer: faq.answer.trim(),
        }))
        .filter((faq) => faq.question !== '');

    errors.title = title ? null : 'عنوان الصفحة مطلوب.';
    errors.slug = slug ? null : 'نص الرابط مطلوب.';
    errors.faqs = null;
    errors.form = null;

    if (errors.title || errors.slug) {
        switchFormTab(errors.title ? 'edit' : 'advanced');
        return;
    }

    try {
        const page = await store.updatePage(uuid.value, {
            title,
            subtitle: form.subtitle.trim(),
            slug,
            published: Boolean(form.published),
            faqs,
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
            errors.faqs = error.errors?.faqs?.[0] ?? null;
            errors.form = (!errors.title && !errors.slug && !errors.faqs)
                ? (error.message || 'تعذر حفظ الصفحة.')
                : null;

            if (errors.title || errors.faqs) {
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
                        <span class="truncate text-stone-600 hidden md:inline">تحرير الأسئلة المتكررة</span>
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
                <p v-if="errors.faqs" class="mb-3 text-sm text-red-600">{{ errors.faqs }}</p>

                <div
                    class="space-y-5"
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
                            label="الوصف"
                            placeholder="وصف قصير يظهر أعلى الصفحة"
                            :rows="3"
                        />
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-sm font-medium text-stone-700">الأسئلة والأجوبة</p>
                            <Button type="button" variant="secondary" label="إضافة سؤال" @click="addFaq">
                                <template #icon>
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg>
                                </template>
                            </Button>
                        </div>

                        <div v-if="form.faqs.length === 0" class="rounded-xl border border-dashed border-stone-200 p-8 text-center text-sm text-stone-500">
                            لا توجد أسئلة بعد. أضف سؤالاً للبدء.
                        </div>

                        <div
                            v-for="(faq, index) in form.faqs"
                            :key="faq.id"
                            class="space-y-3 rounded-xl border border-stone-200 p-4"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <p class="text-sm font-medium text-stone-600">سؤال {{ index + 1 }}</p>
                                <button
                                    type="button"
                                    class="rounded-lg px-2 py-1 text-xs text-red-600 hover:bg-red-50"
                                    @click="removeFaq(faq.id)"
                                >
                                    حذف
                                </button>
                            </div>

                            <Input
                                v-model="faq.question"
                                :name="`faq_question_${faq.id}`"
                                label="السؤال"
                                placeholder="اكتب السؤال"
                            />

                            <Textarea
                                v-model="faq.answer"
                                :name="`faq_answer_${faq.id}`"
                                label="الجواب"
                                placeholder="اكتب الجواب"
                                :rows="3"
                            />
                        </div>
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
