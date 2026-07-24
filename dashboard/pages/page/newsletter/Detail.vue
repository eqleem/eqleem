<script setup>
import { computed, nextTick, onMounted, reactive, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import ManageLayout from '../../../components/page/ManageLayout.vue';
import { CkEditor } from '../../../components/ui/asyncHeavy.js';
import Form from '../../../components/ui/Form.vue';
import Input from '../../../components/ui/Input.vue';
import Textarea from '../../../components/ui/Textarea.vue';
import Button from '../../../components/ui/Button.vue';
import Toggle from '../../../components/ui/Toggle.vue';
import Select from '../../../components/ui/Select.vue';
import Alert from '../../../components/ui/Alert.vue';
import NotFound from '../../NotFound.vue';
import { useNewsletterStore } from '../../../stores/newsletter.js';
import { ApiError } from '../../../lib/api.js';
import { notifySuccess, notifyApiError } from '../../../lib/notify.js';

const route = useRoute();
const router = useRouter();
const store = useNewsletterStore();
const formTab = ref('edit');
const uploadingImage = ref(false);
const notFound = ref(false);
const bodyEditor = ref(null);
const featuredInput = ref(null);
const bodySeed = ref('');

const form = reactive({
    title: '',
    subject: '',
    subtitle: '',
    slug: '',
    mailStatus: 'draft',
    scheduledDate: '',
    scheduledTime: '',
    recipientsCount: '',
    published: false,
    featuredImage: null,
    sentAtLabel: null,
});

const errors = reactive({
    title: null,
    slug: null,
    form: null,
});

const uuid = computed(() => String(route.params.id));
const editorUploadUrl = computed(() => `/api/newsletter/${uuid.value}/editor-images`);
const slugPrefix = computed(() => store.detail?.slug_prefix ?? '/newsletter/');
const mailStatusOptions = computed(() => store.detail?.mail_status_options ?? {});

function switchTab(tab) {
    formTab.value = tab;
}

function loadForm(issue, { syncEditor = true } = {}) {
    if (!issue) {
        return;
    }

    form.title = issue.title ?? '';
    form.subject = issue.subject ?? '';
    form.subtitle = issue.subtitle ?? '';
    form.slug = issue.slug ?? '';
    form.mailStatus = issue.mail_status ?? 'draft';
    form.scheduledDate = issue.scheduled_date ?? '';
    form.scheduledTime = issue.scheduled_time ?? '';
    form.recipientsCount = issue.recipients_count > 0 ? String(issue.recipients_count) : '';
    form.published = Boolean(issue.published);
    form.featuredImage = issue.featured_image ?? null;
    form.sentAtLabel = issue.sent_at_label ?? null;
    errors.title = null;
    errors.slug = null;
    errors.form = null;

    if (syncEditor) {
        bodySeed.value = issue.body ?? '';
        nextTick(() => {
            bodyEditor.value?.setData?.(bodySeed.value);
        });
    }
}

onMounted(async () => {
    try {
        const issue = await store.fetchIssue(uuid.value);
        loadForm(issue);
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
        const issue = await store.fetchIssue(String(id));
        loadForm(issue);
    } catch (error) {
        notFound.value = error instanceof ApiError && error.status === 404;
    }
});

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

    errors.title = title ? null : 'عنوان النشرة مطلوب.';
    errors.slug = slug ? null : 'نص الرابط مطلوب.';
    errors.form = null;

    if (errors.title || errors.slug) {
        switchTab(errors.title ? 'edit' : 'advanced');
        return;
    }

    const recipientsRaw = form.recipientsCount.trim();
    const recipientsCount = recipientsRaw === '' ? 0 : Number(recipientsRaw);

    try {
        const issue = await store.updateIssue(uuid.value, {
            title,
            subject: form.subject.trim(),
            subtitle: form.subtitle.trim(),
            body,
            slug,
            mail_status: form.mailStatus,
            scheduled_date: form.mailStatus === 'scheduled' ? form.scheduledDate : null,
            scheduled_time: form.mailStatus === 'scheduled' ? form.scheduledTime : null,
            recipients_count: Number.isFinite(recipientsCount) && recipientsCount >= 0 ? recipientsCount : 0,
            published: Boolean(form.published),
            editor_mode: 'html',
        });

        if (close) {
            router.push('/manage/newsletter');
            return;
        }

        loadForm(issue);
        notifySuccess('Saved');
    } catch (error) {
        if (error instanceof ApiError) {
            errors.title = error.errors?.title?.[0] ?? null;
            errors.slug = error.errors?.slug?.[0] ?? null;
            errors.form = (!errors.title && !errors.slug)
                ? (error.message || 'تعذر حفظ النشرة.')
                : null;

            if (errors.title) {
                switchTab('edit');
            } else if (errors.slug) {
                switchTab('advanced');
            }
        } else {
            errors.form = 'تعذر حفظ النشرة.';
        }

        notifyApiError(error, 'تعذر حفظ النشرة.');
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
                        to="/manage/newsletter"
                        title="رجوع"
                        class="flex shrink-0 items-center justify-center rounded-lg bg-white p-2 shadow-sm hover:bg-stone-50"
                    >
                        <svg class="h-5 w-5 text-stone-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                    </RouterLink>
                    <div class="flex min-w-0 items-center gap-2 text-sm text-stone-700">
                        <img v-if="store.type?.icon" :src="`/${store.type.icon}`" class="h-5 w-5 shrink-0" alt="">
                        <span class="truncate font-semibold">{{ store.type?.name }}</span>
                        <span class="text-stone-400 hidden md:inline">/</span>
                        <span class="truncate text-stone-600 hidden md:inline">تحرير النشرة</span>
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
                        :class="formTab === 'send' ? 'bg-white font-semibold text-stone-900 shadow-sm' : 'text-stone-600 hover:bg-white/60 hover:text-stone-800'"
                        @click.prevent.stop="switchTab('send')"
                    >
                        الإرسال
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
                        placeholder="عنوان النشرة"
                        :error="errors.title"
                    />

                    <Input
                        v-model="form.subject"
                        name="subject"
                        label="موضوع البريد"
                        placeholder="الموضوع الذي يظهر في صندوق الوارد"
                        info="يُستخدم كعنوان رسالة البريد الإلكتروني عند الإرسال."
                    />

                    <Textarea
                        v-model="form.subtitle"
                        name="subtitle"
                        placeholder="نص معاينة"
                        info="نص قصير يظهر تحت العنوان في أرشيف النشرة وفي معاينة البريد."
                        :rows="2"
                    />

                    <div class="relative rounded-md bg-stone-100/75 p-1 lg:flex lg:items-start lg:gap-x-2">
                        <span class="inline-block w-36 flex-shrink-0 p-2 text-sm font-semibold text-stone-500">صورة الغلاف</span>
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
                    :class="formTab === 'send' ? 'relative z-10 block' : 'hidden'"
                >
                    <Select
                        v-model="form.mailStatus"
                        name="mailStatus"
                        label="حالة الإرسال"
                        :options="mailStatusOptions"
                    />

                    <div v-if="form.mailStatus === 'scheduled'" class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                        <Input
                            v-model="form.scheduledDate"
                            name="scheduledDate"
                            label="تاريخ الجدولة"
                            type="date"
                            dir="ltr"
                        />
                        <Input
                            v-model="form.scheduledTime"
                            name="scheduledTime"
                            label="وقت الجدولة"
                            type="time"
                            dir="ltr"
                        />
                    </div>

                    <Alert
                        v-if="form.mailStatus === 'sent' && form.sentAtLabel"
                        color="green"
                        heading="تم الإرسال"
                        :text="`أُرسلت النشرة في ${form.sentAtLabel}`"
                    />

                    <Input
                        v-model="form.recipientsCount"
                        name="recipientsCount"
                        label="عدد المستلمين"
                        type="number"
                        dir="ltr"
                        min="0"
                        step="1"
                        placeholder="0"
                        info="يُحدَّث تلقائياً عند ربط نظام الإرسال، أو يمكن إدخاله يدوياً بعد الإرسال."
                    />
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

                    <Toggle v-model="form.published" name="published" label="نشر في أرشيف الموقع" />
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
