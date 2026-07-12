<script setup>
import { onMounted, onUnmounted, watch } from 'vue';
import { storeToRefs } from 'pinia';
import { useRoute } from 'vue-router';
import Container from '../../components/ui/Container.vue';
import Section from '../../components/ui/Section.vue';
import Badge from '../../components/ui/Badge.vue';
import Icon from '../../components/ui/Icon.vue';
import { usePaymentsStore } from '../../stores/payments.js';

const route = useRoute();
const paymentsStore = usePaymentsStore();
const { detail: payment, detailLoading: loading, detailError: error } = storeToRefs(paymentsStore);

async function loadPayment(uuid) {
    if (!uuid) {
        return;
    }

    try {
        await paymentsStore.fetchDetail(uuid);
    } catch {
        // store handles error
    }
}

watch(() => route.params.uuid, (uuid) => loadPayment(uuid));
onMounted(() => loadPayment(route.params.uuid));
onUnmounted(() => paymentsStore.clearDetail());
</script>

<template>
    <Container :title="`الطلبات / عملية #${payment?.id ?? '...'}`" back-route="/orders?tab=payments">
        <div v-if="loading && !payment" class="flex items-center justify-center rounded-xl bg-white p-16">
            <svg class="h-10 w-10 animate-spin text-stone-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" d="M12 3a9 9 0 1 0 9 9" /></svg>
        </div>

        <div v-else-if="error && !payment" class="flex flex-col items-center justify-center gap-3 rounded-xl bg-white p-16 text-center">
            <p class="text-sm text-red-600">{{ error }}</p>
            <button type="button" class="rounded-lg border px-3 py-1.5 text-sm" @click="loadPayment(route.params.uuid)">إعادة المحاولة</button>
        </div>

        <div v-else-if="payment" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:order-1">
                <Section title="ملخص العملية" icon="coin">
                    <div class="space-y-3 p-5">
                        <div class="flex items-center justify-between text-sm"><span class="text-stone-500">المبلغ</span><span class="font-medium text-stone-800"><Money :formatted="payment.amount_formatted" /></span></div>
                        <div class="border-t border-stone-100 pt-4">
                            <div class="flex items-center justify-between"><span class="text-sm font-semibold text-stone-800">الإجمالي</span><span class="text-xl font-bold text-primary-700"><Money :formatted="payment.amount_formatted" /></span></div>
                        </div>
                        <div class="space-y-2 border-t border-stone-100 pt-3">
                            <div class="flex items-center justify-between text-sm"><span class="text-stone-500">العملة</span><span class="font-medium text-stone-800" dir="ltr">{{ payment.currency }}</span></div>
                        </div>
                    </div>
                </Section>

                <Section title="الدافع" icon="user">
                    <div class="p-5">
                        <div v-if="payment.payer" class="flex flex-col items-center py-2 text-center">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-stone-100 text-lg font-semibold text-stone-600">{{ payment.payer.charAt(0) }}</div>
                            <p class="mt-3 text-sm font-semibold text-stone-700">{{ payment.payer }}</p>
                            <p v-if="payment.email" class="mt-1 truncate text-xs text-stone-400">{{ payment.email }}</p>
                        </div>
                        <div v-else class="flex flex-col items-center py-4 text-center">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-stone-100 text-stone-400"><Icon name="user" class="h-6 w-6" /></div>
                            <p class="mt-3 text-sm font-semibold text-stone-700">—</p>
                            <p class="mt-1 text-xs text-stone-400">لا توجد بيانات دافع</p>
                        </div>
                    </div>
                </Section>

                <Section title="معلومات تقنية" icon="info">
                    <div class="space-y-3 p-5 text-sm">
                        <div><p class="text-xs text-stone-400">المعرّف</p><p class="mt-0.5 break-all font-mono text-xs text-stone-600" dir="ltr">{{ payment.uuid }}</p></div>
                        <div><p class="text-xs text-stone-400">تاريخ الإنشاء</p><p class="mt-0.5 text-xs font-medium text-stone-800">{{ payment.date }} {{ payment.time }}</p></div>
                    </div>
                </Section>
            </div>

            <div class="space-y-6 lg:order-2 lg:col-span-2">
                <Section title="تفاصيل الدفع" icon="receipt">
                    <div class="p-5">
                        <dl class="grid grid-cols-1 gap-x-8 gap-y-5 sm:grid-cols-2">
                            <div><dt class="mb-1 text-xs text-stone-400">رقم العملية</dt><dd class="text-sm font-semibold text-stone-900">#{{ payment.id }}</dd></div>
                            <div><dt class="mb-1 text-xs text-stone-400">الحالة</dt><dd><Badge :color="payment.status_color">{{ payment.status_label }}</Badge></dd></div>
                            <div><dt class="mb-1 text-xs text-stone-400">نوع العملية</dt><dd class="text-sm text-stone-800">{{ payment.reason_label }}</dd></div>
                            <div><dt class="mb-1 text-xs text-stone-400">تاريخ العملية</dt><dd class="text-sm text-stone-800">{{ payment.date }} <span class="text-stone-400" dir="ltr">{{ payment.time }}</span></dd></div>
                            <div><dt class="mb-1 text-xs text-stone-400">بوابة الدفع</dt><dd class="flex items-center gap-1.5 text-sm text-stone-800"><Icon name="bank" class="h-4 w-4 text-stone-400" /> {{ payment.gateway_label }}</dd></div>
                            <div><dt class="mb-1 text-xs text-stone-400">طريقة الدفع</dt><dd class="flex items-center gap-1.5 text-sm text-stone-800"><Icon name="card" class="h-4 w-4 text-stone-400" /> {{ payment.source_type_label }}</dd></div>
                            <div v-if="payment.card"><dt class="mb-1 text-xs text-stone-400">البطاقة</dt><dd class="font-mono text-sm text-stone-800" dir="ltr">{{ payment.card }}</dd></div>
                        </dl>
                    </div>
                </Section>

                <Section v-if="payment.order_uuid" title="الطلب المرتبط" icon="package">
                    <div class="p-5">
                        <RouterLink :to="`/orders/${payment.order_uuid}`" class="flex items-center justify-between gap-4 rounded-lg bg-stone-50 p-4 transition hover:bg-stone-100">
                            <div>
                                <p class="font-semibold text-stone-800">طلب #{{ payment.order_number }}</p>
                                <p class="mt-1 text-sm text-stone-500">عرض تفاصيل الطلب</p>
                            </div>
                            <div class="text-end">
                                <p class="font-bold text-stone-900"><Money :formatted="payment.amount_formatted" /></p>
                                <Icon name="link" class="mt-2 h-4 w-4 text-primary-500" />
                            </div>
                        </RouterLink>
                    </div>
                </Section>
            </div>
        </div>
    </Container>
</template>
