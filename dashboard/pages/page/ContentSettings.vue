<script setup>
import { computed, onMounted, reactive, watch } from 'vue';
import { useRoute } from 'vue-router';
import ManageLayout from '../../components/page/ManageLayout.vue';
import ContentShell from '../../components/page/ContentShell.vue';
import Form from '../../components/ui/Form.vue';
import Input from '../../components/ui/Input.vue';
import Button from '../../components/ui/Button.vue';
import NotFound from '../NotFound.vue';
import { contentTypeBySlug } from '../../data/page.js';
import { useReviewsStore } from '../../stores/reviews.js';
import { ApiError } from '../../lib/api.js';
import { notifySuccess, notifyApiError } from '../../lib/notify.js';

const route = useRoute();
const contentType = computed(() => contentTypeBySlug(route.params.type));
const reviewsStore = useReviewsStore();

const form = reactive({
    title: '',
    perPage: '12',
});
const errors = reactive({
    title: null,
    perPage: null,
});

const isReviews = computed(() => contentType.value?.slug === 'reviews');

onMounted(() => {
    if (isReviews.value) {
        reviewsStore.fetchSettings();
    }
});

watch(
    () => [reviewsStore.settings?.section_title, reviewsStore.settings?.per_page],
    ([sectionTitle, perPage]) => {
        form.title = sectionTitle ?? '';
        form.perPage = String(perPage ?? 12);
    },
    { immediate: true },
);

async function submit() {
    if (!isReviews.value) {
        return;
    }

    const title = form.title.trim();
    const perPage = Number(form.perPage);

    errors.title = title.length >= 2 ? null : 'عنوان القسم مطلوب (حرفان على الأقل).';
    errors.perPage = Number.isInteger(perPage) && perPage >= 1 && perPage <= 50
        ? null
        : 'عدد العناصر يجب أن يكون بين 1 و 50.';

    if (errors.title || errors.perPage) {
        return;
    }

    try {
        await reviewsStore.updateSettings({
            section_title: title,
            per_page: perPage,
        });

        notifySuccess('تم حفظ الإعدادات بنجاح.');
    } catch (error) {
        if (error instanceof ApiError) {
            errors.title = error.errors?.section_title?.[0] ?? null;
            errors.perPage = error.errors?.per_page?.[0] ?? null;
        }

        notifyApiError(error, 'تعذر حفظ الإعدادات.');
    }
}
</script>

<template>
    <ManageLayout v-if="contentType">
        <ContentShell :content-type="contentType">
            <div class="p-4">
                <div v-if="isReviews && reviewsStore.settingsLoading && !reviewsStore.settingsLoaded" class="flex items-center justify-center py-8">
                    <LoadingSpinner />
                </div>

                <Form v-else @submit="submit">
                    <Input
                        v-model="form.title"
                        name="title"
                        label="عنوان القسم"
                        :placeholder="contentType.name"
                        :error="errors.title"
                    />
                    <Input
                        v-model="form.perPage"
                        name="perPage"
                        type="number"
                        label="عدد العناصر بالصفحة"
                        placeholder="12"
                        dir="ltr"
                        :error="errors.perPage"
                    />
                    <template #footer>
                        <Button type="submit" label="حفظ" :disabled="isReviews && reviewsStore.saving" />
                    </template>
                </Form>
            </div>
        </ContentShell>
    </ManageLayout>
    <NotFound v-else />
</template>
