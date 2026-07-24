<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import ManageLayout from '../../../components/page/ManageLayout.vue';
import Form from '../../../components/ui/Form.vue';
import Input from '../../../components/ui/Input.vue';
import Textarea from '../../../components/ui/Textarea.vue';
import Button from '../../../components/ui/Button.vue';
import BrandMarkField from '../../../components/ui/BrandMarkField.vue';
import PageFormMetaSection from '../../../components/page/pages/PageFormMetaSection.vue';
import AboutPrimaryButtonField from '../../../components/page/pages/AboutPrimaryButtonField.vue';
import NotFound from '../../NotFound.vue';
import { usePagesStore } from '../../../stores/pages.js';
import { usePageAdvancedOpen } from '../../../composables/usePageAdvancedOpen.js';
import { ApiError } from '../../../lib/api.js';
import { notifySuccess, notifyApiError } from '../../../lib/notify.js';

function makeId(prefix) {
    return `${prefix}_${Date.now()}_${Math.random().toString(36).slice(2, 8)}`;
}

function makeStat(overrides = {}) {
    return {
        id: overrides.id || makeId('stat'),
        value: overrides.value || '',
        label: overrides.label || '',
    };
}

function makeFeature(overrides = {}) {
    return {
        id: overrides.id || makeId('feature'),
        title: overrides.title || '',
        description: overrides.description || '',
        brand_mark: overrides.brand_mark && typeof overrides.brand_mark === 'object'
            ? { ...overrides.brand_mark }
            : null,
    };
}

function emptyPrimaryButton() {
    return {
        label: '',
        link_type: 'external',
        content_id: null,
        selected_content_title: '',
        url: '',
        branch_ids: [],
        calendar_ids: [],
        allow_client_choice: true,
        duration_minutes: 30,
    };
}

function serializeBrandMark(mark) {
    if (!mark || typeof mark !== 'object' || !mark.type || mark.type === 'none') {
        return null;
    }

    if (mark.type === 'image') {
        if (!mark.path) {
            return null;
        }

        return {
            type: 'image',
            value: '',
            color: '',
            path: mark.path,
        };
    }

    return {
        type: mark.type,
        value: mark.value ?? '',
        color: mark.color ?? '',
    };
}

const route = useRoute();
const router = useRouter();
const store = usePagesStore();
const { expand: expandAdvanced } = usePageAdvancedOpen();
const notFound = ref(false);
const uploadingHero = ref(false);
const heroInput = ref(null);

const form = reactive({
    title: '',
    subtitle: '',
    heroImage: null,
    slug: '',
    published: false,
    primaryButton: emptyPrimaryButton(),
    stats: [],
    featuresTitle: '',
    featuresDescription: '',
    features: [],
});

const errors = reactive({
    title: null,
    slug: null,
    form: null,
});

const uuid = computed(() => String(route.params.id));
const slugPrefix = computed(() => store.detail?.slug_prefix ?? '/');
const emptyPickerOptions = [];
const emptyBookingTargets = Object.freeze({ branches: [], calendars: [] });
const linkTypePickerOptions = computed(() => store.detail?.link_type_picker_options ?? emptyPickerOptions);
const bookingTargets = computed(() => store.detail?.booking_targets ?? emptyBookingTargets);

function loadForm(page) {
    if (!page) {
        return;
    }

    form.title = page.title ?? '';
    form.subtitle = page.subtitle ?? '';
    form.heroImage = page.hero_image ?? null;
    form.slug = page.slug ?? '';
    form.published = Boolean(page.published);
    form.primaryButton = {
        ...emptyPrimaryButton(),
        ...(page.primary_button ?? {}),
    };
    form.stats = Array.isArray(page.stats)
        ? page.stats.map((stat) => makeStat(stat))
        : [];
    form.featuresTitle = page.features_title ?? '';
    form.featuresDescription = page.features_description ?? '';
    form.features = Array.isArray(page.features)
        ? page.features.map((feature) => makeFeature(feature))
        : [];

    errors.title = null;
    errors.slug = null;
    errors.form = null;
}

async function loadPage() {
    try {
        const page = await store.fetchPage(uuid.value);

        if (page?.template && page.template !== 'about') {
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

function addStat() {
    form.stats.push(makeStat());
}

function removeStat(id) {
    form.stats = form.stats.filter((stat) => stat.id !== id);
}

function addFeature() {
    form.features.push(makeFeature());
}

function removeFeature(id) {
    form.features = form.features.filter((feature) => feature.id !== id);
}

function openHeroPicker() {
    heroInput.value?.click();
}

async function onHeroSelected(event) {
    const file = event.target.files?.[0];

    if (!file) {
        return;
    }

    uploadingHero.value = true;
    errors.form = null;

    try {
        const result = await store.uploadHeroImage(uuid.value, file);
        form.heroImage = result?.hero_image ?? null;
        notifySuccess('Saved');
    } catch (error) {
        errors.form = error instanceof ApiError ? error.message : 'تعذر رفع الصورة.';
        notifyApiError(error, 'تعذر رفع الصورة.');
    } finally {
        uploadingHero.value = false;
        event.target.value = '';
    }
}

async function removeHeroImage() {
    uploadingHero.value = true;
    errors.form = null;

    try {
        await store.deleteHeroImage(uuid.value);
        form.heroImage = null;
        notifySuccess('Saved');
    } catch (error) {
        errors.form = error instanceof ApiError ? error.message : 'تعذر حذف الصورة.';
        notifyApiError(error, 'تعذر حذف الصورة.');
    } finally {
        uploadingHero.value = false;
    }
}

async function onFeatureBrandMarkChange(feature, mark) {
    if (mark?.type === 'image' && mark.file) {
        try {
            const uploaded = await store.uploadBrandMarkImage(uuid.value, mark.file);
            feature.brand_mark = uploaded?.brand_mark
                ? { ...uploaded.brand_mark, file: null }
                : null;
        } catch (error) {
            notifyApiError(error, 'تعذر رفع الأيقونة.');
        }
    }
}

async function persist({ close = false } = {}) {
    const title = form.title.trim();
    const slug = form.slug.trim();
    const stats = form.stats
        .map((stat) => ({
            id: stat.id,
            value: String(stat.value ?? '').trim(),
            label: String(stat.label ?? '').trim(),
        }))
        .filter((stat) => stat.value !== '');
    const features = form.features
        .map((feature) => ({
            id: feature.id,
            title: String(feature.title ?? '').trim(),
            description: String(feature.description ?? '').trim(),
            brand_mark: serializeBrandMark(feature.brand_mark),
        }))
        .filter((feature) => feature.title !== '');

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
            primary_button: {
                label: String(form.primaryButton.label ?? '').trim(),
                link_type: form.primaryButton.link_type || 'external',
                content_id: form.primaryButton.content_id || null,
                url: form.primaryButton.url || '',
                branch_ids: form.primaryButton.branch_ids || [],
                calendar_ids: form.primaryButton.calendar_ids || [],
                allow_client_choice: form.primaryButton.allow_client_choice ?? true,
                duration_minutes: Number(form.primaryButton.duration_minutes) || 30,
            },
            stats,
            features_title: form.featuresTitle.trim(),
            features_description: form.featuresDescription.trim(),
            features,
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
                        <span class="hidden text-stone-400 md:inline">/</span>
                        <span class="hidden truncate text-stone-600 md:inline">تحرير صفحة من نحن</span>
                    </div>
                </div>
            </div>

            <Form class="!rounded-none !p-4 md:!p-6" @submit="save">
                <p v-if="errors.form" class="mb-3 text-sm text-red-600">{{ errors.form }}</p>

                <div class="space-y-8">
                    <section class="space-y-3">
                        <div>
                            <h3 class="text-sm font-semibold text-stone-800">القسم الأول: البطل</h3>
                            <p class="mt-1 text-xs text-stone-500">الصورة الرئيسية والعنوان والوصف والزر.</p>
                        </div>

                        <Input
                            v-model="form.title"
                            name="title"
                            label="العنوان"
                            placeholder="عنوان الصفحة"
                            :error="errors.title"
                        />

                        <Textarea
                            v-model="form.subtitle"
                            name="subtitle"
                            label="الوصف"
                            placeholder="وصف قصير يظهر في قسم البطل"
                        />

                        <div class="relative rounded-md bg-stone-100/75 p-1 lg:flex lg:items-start lg:gap-x-2">
                            <span class="inline-block w-36 flex-shrink-0 p-2 text-sm font-semibold text-stone-500">الصورة الرئيسية</span>
                            <div class="w-full space-y-2 p-2">
                                <img
                                    v-if="form.heroImage"
                                    :src="form.heroImage"
                                    class="mb-2 max-h-56 w-full max-w-sm rounded-xl object-cover"
                                    alt=""
                                >
                                <div class="flex flex-wrap items-center gap-2">
                                    <input
                                        ref="heroInput"
                                        type="file"
                                        accept="image/*"
                                        class="hidden"
                                        @change="onHeroSelected"
                                    >
                                    <Button
                                        type="button"
                                        variant="secondary"
                                        :label="uploadingHero ? 'جاري الرفع…' : 'رفع الصورة'"
                                        :disabled="uploadingHero || store.saving"
                                        @click="openHeroPicker"
                                    />
                                    <Button
                                        v-if="form.heroImage"
                                        type="button"
                                        variant="secondary"
                                        label="حذف الصورة"
                                        :disabled="uploadingHero || store.saving"
                                        @click="removeHeroImage"
                                    />
                                </div>
                            </div>
                        </div>

                        <AboutPrimaryButtonField
                            v-model="form.primaryButton"
                            :picker-options="linkTypePickerOptions"
                            :booking-targets="bookingTargets"
                        />
                    </section>

                    <section class="space-y-3">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <h3 class="text-sm font-semibold text-stone-800">القسم الثاني: الأرقام</h3>
                                <p class="mt-1 text-xs text-stone-500">أضف أي عدد من الأرقام مع وصف لكل رقم.</p>
                            </div>
                            <Button type="button" variant="secondary" label="إضافة رقم" @click="addStat">
                                <template #icon>
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg>
                                </template>
                            </Button>
                        </div>

                        <div v-if="form.stats.length === 0" class="rounded-xl border border-dashed border-stone-200 p-8 text-center text-sm text-stone-500">
                            لا توجد أرقام بعد. أضف رقماً للبدء.
                        </div>

                        <div
                            v-for="(stat, index) in form.stats"
                            :key="stat.id"
                            class="space-y-3 rounded-xl border border-stone-200 p-4"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <p class="text-sm font-medium text-stone-600">رقم {{ index + 1 }}</p>
                                <button
                                    type="button"
                                    class="rounded-lg px-2 py-1 text-xs text-red-600 hover:bg-red-50"
                                    @click="removeStat(stat.id)"
                                >
                                    حذف
                                </button>
                            </div>

                            <div class="grid gap-3 sm:grid-cols-2">
                                <Input
                                    v-model="stat.value"
                                    :name="`stat_value_${stat.id}`"
                                    label="الرقم"
                                    placeholder="95%"
                                />
                                <Input
                                    v-model="stat.label"
                                    :name="`stat_label_${stat.id}`"
                                    label="الوصف"
                                    placeholder="رضا العملاء"
                                />
                            </div>
                        </div>
                    </section>

                    <section class="space-y-3">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <h3 class="text-sm font-semibold text-stone-800">القسم الثالث: المزايا</h3>
                                <p class="mt-1 text-xs text-stone-500">عنوان ووصف اختياريان للقسم، ثم بطاقات المزايا.</p>
                            </div>
                            <Button type="button" variant="secondary" label="إضافة ميزة" @click="addFeature">
                                <template #icon>
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg>
                                </template>
                            </Button>
                        </div>

                        <Input
                            v-model="form.featuresTitle"
                            name="features_title"
                            label="عنوان قسم المزايا"
                            placeholder="لماذا تختارنا؟"
                        />

                        <Textarea
                            v-model="form.featuresDescription"
                            name="features_description"
                            label="وصف قسم المزايا"
                            placeholder="وصف اختياري يظهر تحت العنوان"
                        />

                        <div v-if="form.features.length === 0" class="rounded-xl border border-dashed border-stone-200 p-8 text-center text-sm text-stone-500">
                            لا توجد مزايا بعد. أضف ميزة للبدء.
                        </div>

                        <div
                            v-for="(feature, index) in form.features"
                            :key="feature.id"
                            class="space-y-3 rounded-xl border border-stone-200 p-4"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <p class="text-sm font-medium text-stone-600">ميزة {{ index + 1 }}</p>
                                <button
                                    type="button"
                                    class="rounded-lg px-2 py-1 text-xs text-red-600 hover:bg-red-50"
                                    @click="removeFeature(feature.id)"
                                >
                                    حذف
                                </button>
                            </div>

                            <BrandMarkField
                                v-model="feature.brand_mark"
                                :name="`feature_mark_${feature.id}`"
                                label="الأيقونة"
                                @change="onFeatureBrandMarkChange(feature, $event)"
                            />

                            <Input
                                v-model="feature.title"
                                :name="`feature_title_${feature.id}`"
                                label="عنوان الميزة"
                                placeholder="عنوان قصير"
                            />

                            <Textarea
                                v-model="feature.description"
                                :name="`feature_description_${feature.id}`"
                                label="وصف الميزة"
                                placeholder="وصف صغير للميزة"
                            />
                        </div>
                    </section>

                    <PageFormMetaSection
                        v-model:published="form.published"
                        v-model:slug="form.slug"
                        :slug-prefix="slugPrefix"
                        :slug-error="errors.slug"
                    />
                </div>

                <template #footer>
                    <div class="flex items-center gap-2">
                        <Button type="button" variant="secondary" label="حفظ وإغلاق" :disabled="store.saving || uploadingHero" @click="saveAndClose" />
                        <Button type="submit" label="حفظ" :disabled="store.saving || uploadingHero" />
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
