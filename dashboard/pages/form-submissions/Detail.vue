<script setup>
import { onMounted, onUnmounted, watch } from 'vue';
import { storeToRefs } from 'pinia';
import { useRoute } from 'vue-router';
import Container from '../../components/ui/Container.vue';
import Section from '../../components/ui/Section.vue';
import Badge from '../../components/ui/Badge.vue';
import Icon from '../../components/ui/Icon.vue';
import Button from '../../components/ui/Button.vue';
import { useFormSubmissionsStore } from '../../stores/formSubmissions.js';

const route = useRoute();
const submissionsStore = useFormSubmissionsStore();
const { detail: submission, detailLoading: loading, detailError: error } = storeToRefs(submissionsStore);

async function loadSubmission(id) {
    if (!id) {
        return;
    }

    try {
        await submissionsStore.fetchDetail(id);
    } catch {
        // store handles error
    }
}

watch(() => route.params.id, (id) => loadSubmission(id));
onMounted(() => loadSubmission(route.params.id));
onUnmounted(() => submissionsStore.clearDetail());
</script>

<template>
    <Container :title="`الطلبات / رد #${submission?.id ?? '...'}`" back-route="/orders?tab=form-submissions">
        <div v-if="loading && !submission" class="flex items-center justify-center rounded-xl bg-white p-16">
            <svg class="h-10 w-10 animate-spin text-stone-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" d="M12 3a9 9 0 1 0 9 9" /></svg>
        </div>

        <div v-else-if="error && !submission" class="flex flex-col items-center justify-center gap-3 rounded-xl bg-white p-16 text-center">
            <p class="text-sm text-red-600">{{ error }}</p>
            <button type="button" class="rounded-lg border px-3 py-1.5 text-sm" @click="loadSubmission(route.params.id)">إعادة المحاولة</button>
        </div>

        <div v-else-if="submission" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:order-1">
                <Section title="ملخص الرد" icon="clipboard">
                    <div class="space-y-3 p-5">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-stone-500">الحالة</span>
                            <Badge :color="submission.status_color">{{ submission.status_label }}</Badge>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-stone-500">عدد الحقول</span>
                            <span class="font-medium text-stone-800">{{ submission.fields_count }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-stone-500">عدد الردود</span>
                            <span class="font-medium text-stone-800">{{ submission.replies_count }}</span>
                        </div>
                        <div class="border-t border-stone-100 pt-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-semibold text-stone-800">رقم الرد</span>
                                <span class="text-xl font-bold text-primary-700">#{{ submission.id }}</span>
                            </div>
                        </div>
                        <div class="space-y-2 border-t border-stone-100 pt-3">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-stone-500">تاريخ الإرسال</span>
                                <span class="font-medium text-stone-800">{{ submission.submitted }}</span>
                            </div>
                            <div v-if="submission.read_at" class="flex items-center justify-between text-sm">
                                <span class="text-stone-500">تاريخ القراءة</span>
                                <span class="font-medium text-emerald-700">{{ submission.read_at }}</span>
                            </div>
                        </div>
                    </div>
                </Section>

                <Section title="العميل" icon="user">
                    <div class="p-5">
                        <template v-if="submission.client">
                            <div class="flex items-center gap-3">
                                <img :src="submission.client.avatar" :alt="submission.client.name" class="h-12 w-12 shrink-0 rounded-full bg-stone-100 object-cover">
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-stone-900">{{ submission.client.name }}</p>
                                    <p v-if="submission.client.email" class="truncate text-sm text-stone-500">{{ submission.client.email }}</p>
                                    <p v-if="submission.client.phone" class="text-sm text-stone-500" dir="ltr">{{ submission.client.phone }}</p>
                                </div>
                            </div>
                            <RouterLink :to="`/clients/${submission.client.uuid}`" class="mt-4 block">
                                <Button label="عرض ملف العميل" variant="outline" class="w-full" />
                            </RouterLink>
                        </template>
                        <div v-else class="flex flex-col items-center py-4 text-center">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-stone-100 text-stone-400"><Icon name="user" class="h-6 w-6" /></div>
                            <p class="mt-3 text-sm font-semibold text-stone-700">زائر</p>
                            <p class="mt-1 text-xs text-stone-400">رد بدون حساب عميل</p>
                        </div>
                    </div>
                </Section>

                <Section v-if="submission.form" title="النموذج" icon="clipboard">
                    <div class="p-5">
                        <p class="text-sm font-semibold text-stone-900">{{ submission.form.title }}</p>
                    </div>
                </Section>
            </div>

            <div class="space-y-6 lg:order-2 lg:col-span-2">
                <Section title="الحقول" icon="list">
                    <div class="divide-y divide-stone-50 p-5">
                        <div v-if="submission.fields.length === 0" class="py-6 text-center text-sm text-stone-500">لا توجد حقول.</div>
                        <div v-for="field in submission.fields" :key="field.id || field.name" class="py-4 first:pt-0 last:pb-0">
                            <p class="text-xs text-stone-400">{{ field.label || field.name }}</p>
                            <p class="mt-1 text-sm font-medium text-stone-800 whitespace-pre-wrap">
                                <template v-if="typeof field.value === 'boolean'">{{ field.value ? 'نعم' : 'لا' }}</template>
                                <template v-else>{{ field.value || '—' }}</template>
                            </p>
                        </div>
                    </div>
                </Section>

                <Section title="الردود" icon="message">
                    <div class="p-5">
                        <div v-if="submission.replies.length === 0" class="py-6 text-center text-sm text-stone-500">لا توجد ردود بعد.</div>
                        <div v-else class="space-y-4">
                            <div v-for="reply in submission.replies" :key="reply.id" class="rounded-lg bg-stone-50 p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="text-sm font-semibold text-stone-800">{{ reply.user || 'فريق المتجر' }}</p>
                                    <p class="text-xs text-stone-400">{{ reply.created }}</p>
                                </div>
                                <p class="mt-2 text-sm leading-relaxed text-stone-700 whitespace-pre-wrap">{{ reply.body }}</p>
                            </div>
                        </div>
                    </div>
                </Section>
            </div>
        </div>
    </Container>
</template>
