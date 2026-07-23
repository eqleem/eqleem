<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import ManageLayout from '../../../components/page/ManageLayout.vue';
import Form from '../../../components/ui/Form.vue';
import Input from '../../../components/ui/Input.vue';
import Textarea from '../../../components/ui/Textarea.vue';
import Button from '../../../components/ui/Button.vue';
import Toggle from '../../../components/ui/Toggle.vue';
import PageFormMetaSection from '../../../components/page/pages/PageFormMetaSection.vue';
import NotFound from '../../NotFound.vue';
import { usePagesStore } from '../../../stores/pages.js';
import { usePageAdvancedOpen } from '../../../composables/usePageAdvancedOpen.js';
import { ApiError } from '../../../lib/api.js';
import { notifySuccess, notifyApiError } from '../../../lib/notify.js';

const FORM_FIELD_OPTIONS = [
    { key: 'name', label: 'الاسم' },
    { key: 'email', label: 'البريد الإلكتروني' },
    { key: 'phone', label: 'رقم الجوال' },
    { key: 'message', label: 'الرسالة' },
    { key: 'address', label: 'العنوان' },
];

const route = useRoute();
const router = useRouter();
const store = usePagesStore();
const { expand: expandAdvanced } = usePageAdvancedOpen();
const notFound = ref(false);

const form = reactive({
    title: '',
    subtitle: '',
    slug: '',
    published: false,
    showForm: true,
    formFields: {
        name: true,
        email: true,
        phone: true,
        message: true,
        address: false,
    },
    showSocialLinks: true,
    showContactInfo: true,
    showExtraLinks: true,
    successMessage: '',
});

const errors = reactive({
    title: null,
    slug: null,
    form: null,
});

const uuid = computed(() => String(route.params.id));
const slugPrefix = computed(() => store.detail?.slug_prefix ?? '/');

function loadForm(page) {
    if (!page) {
        return;
    }

    form.title = page.title ?? '';
    form.subtitle = page.subtitle ?? '';
    form.slug = page.slug ?? '';
    form.published = Boolean(page.published);
    form.showForm = page.show_form !== false;
    form.formFields = {
        name: page.form_fields?.name !== false,
        email: page.form_fields?.email !== false,
        phone: page.form_fields?.phone !== false,
        message: page.form_fields?.message !== false,
        address: Boolean(page.form_fields?.address),
    };
    form.showSocialLinks = page.show_social_links !== false;
    form.showContactInfo = page.show_contact_info !== false;
    form.showExtraLinks = page.show_extra_links !== false;
    form.successMessage = page.success_message ?? '';

    errors.title = null;
    errors.slug = null;
    errors.form = null;
}

async function loadPage() {
    try {
        const page = await store.fetchPage(uuid.value);

        if (page?.template && page.template !== 'contact') {
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
    await loadPage();
});

async function persist({ close = false } = {}) {
    const title = form.title.trim();
    const slug = form.slug.trim();

    errors.title = title ? null : 'عنوان الصفحة مطلوب.';
    errors.slug = slug ? null : 'نص الرابط مطلوب.';
    errors.form = null;

    if (errors.title || errors.slug) {
        if (errors.slug) {
            expandAdvanced();
        }
        return;
    }

    try {
        const page = await store.updatePage(uuid.value, {
            title,
            subtitle: form.subtitle.trim(),
            slug,
            published: Boolean(form.published),
            show_form: Boolean(form.showForm),
            form_fields: { ...form.formFields },
            show_social_links: Boolean(form.showSocialLinks),
            show_contact_info: Boolean(form.showContactInfo),
            show_extra_links: Boolean(form.showExtraLinks),
            success_message: form.successMessage.trim(),
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

            if (errors.slug) {
                expandAdvanced();
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
                        <span class="truncate text-stone-600 hidden md:inline">تحرير صفحة اتصل بنا</span>
                    </div>
                </div>
            </div>

            <Form class="!rounded-none !p-4 md:!p-6" @submit="save">
                <p v-if="errors.form" class="mb-3 text-sm text-red-600">{{ errors.form }}</p>

                <div class="space-y-5">
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
                        />
                    </div>

                    <div class="space-y-3 rounded-xl border border-stone-200 p-4">
                        <Toggle v-model="form.showForm" name="show_form" label="عرض نموذج الاتصال" />

                        <div v-if="form.showForm" class="space-y-3 border-t border-stone-100 pt-3">
                            <p class="text-sm font-medium text-stone-700">حقول النموذج</p>
                            <div class="grid gap-2 sm:grid-cols-2">
                                <Toggle
                                    v-for="field in FORM_FIELD_OPTIONS"
                                    :key="field.key"
                                    v-model="form.formFields[field.key]"
                                    :name="`form_field_${field.key}`"
                                    :label="field.label"
                                    label-width="w-28"
                                />
                            </div>

                            <Textarea
                                v-model="form.successMessage"
                                name="success_message"
                                label="رسالة الشكر بعد الإرسال"
                                placeholder="شكراً لتواصلك معنا..."
                                :rows="2"
                            />
                        </div>
                    </div>

                    <div class="space-y-2 rounded-xl border border-stone-200 p-4">
                        <p class="mb-1 text-sm font-medium text-stone-700">عناصر إضافية</p>
                        <Toggle v-model="form.showContactInfo" name="show_contact_info" label="عرض بيانات الاتصال" info="الجوال، البريد الإلكتروني، والواتساب" />
                        <Toggle v-model="form.showSocialLinks" name="show_social_links" label="عرض روابط السوشال ميديا" />
                        <Toggle v-model="form.showExtraLinks" name="show_extra_links" label="عرض الروابط الإضافية" info="الأسئلة المتكررة والتقييمات" />
                    </div>

                    <PageFormMetaSection
                        v-model:published="form.published"
                        v-model:slug="form.slug"
                        :slug-prefix="slugPrefix"
                        :slug-error="errors.slug"
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
