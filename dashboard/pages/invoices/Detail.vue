<script setup>
import { onMounted, onUnmounted, watch } from 'vue';
import { storeToRefs } from 'pinia';
import { useRoute } from 'vue-router';
import Container from '../../components/ui/Container.vue';
import Section from '../../components/ui/Section.vue';
import Badge from '../../components/ui/Badge.vue';
import { useInvoicesStore } from '../../stores/invoices.js';

const route = useRoute();
const invoicesStore = useInvoicesStore();
const { detail: invoice, detailLoading: loading, detailError: error } = storeToRefs(invoicesStore);

async function loadInvoice(uuid) {
    if (!uuid) {
        return;
    }

    try {
        await invoicesStore.fetchDetail(uuid);
    } catch {
        // store handles error
    }
}

watch(() => route.params.uuid, (uuid) => loadInvoice(uuid));
onMounted(() => loadInvoice(route.params.uuid));
onUnmounted(() => invoicesStore.clearDetail());
</script>

<template>
    <Container :title="`الطلبات / ${invoice?.s_number ?? '...'}`" back-route="/orders?tab=invoices">
        <div v-if="loading && !invoice" class="flex items-center justify-center rounded-xl bg-white p-16">
            <svg class="h-10 w-10 animate-spin text-stone-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" d="M12 3a9 9 0 1 0 9 9" /></svg>
        </div>

        <div v-else-if="error && !invoice" class="flex flex-col items-center justify-center gap-3 rounded-xl bg-white p-16 text-center">
            <p class="text-sm text-red-600">{{ error }}</p>
            <button type="button" class="rounded-lg border px-3 py-1.5 text-sm" @click="loadInvoice(route.params.uuid)">إعادة المحاولة</button>
        </div>

        <div v-else-if="invoice" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:order-1">
                <Section title="ملخص الفاتورة" icon="invoice">
                    <div class="space-y-3 p-5">
                        <div class="flex items-center justify-between text-sm"><span class="text-stone-500">قبل الضريبة</span><span class="font-medium text-stone-800"><Money :formatted="invoice.total_before_vat_formatted" /></span></div>
                        <div class="flex items-center justify-between text-sm"><span class="text-stone-500">الضريبة</span><span class="font-medium text-stone-800"><Money :formatted="invoice.vat_formatted" /></span></div>
                        <div class="border-t border-stone-100 pt-4">
                            <div class="flex items-center justify-between"><span class="text-sm font-semibold text-stone-800">الإجمالي</span><span class="text-xl font-bold text-primary-700"><Money :formatted="invoice.total_after_vat_formatted" /></span></div>
                        </div>
                        <div class="space-y-2 border-t border-stone-100 pt-3">
                            <div class="flex items-center justify-between text-sm"><span class="text-stone-500">المدفوع</span><span class="font-medium text-emerald-700"><Money :formatted="invoice.amount_paid_formatted" /></span></div>
                            <div class="flex items-center justify-between text-sm"><span class="text-stone-500">المتبقي</span><span class="font-medium" :class="invoice.due > 0 ? 'text-amber-700' : 'text-stone-800'"><Money :formatted="invoice.due_formatted" /></span></div>
                        </div>
                    </div>
                </Section>

                <Section v-if="invoice.note" title="ملاحظات" icon="note">
                    <div class="p-5"><p class="rounded-lg bg-stone-50 p-4 text-sm leading-relaxed text-stone-700">{{ invoice.note }}</p></div>
                </Section>
            </div>

            <div class="space-y-6 lg:order-2 lg:col-span-2">
                <Section title="تفاصيل الفاتورة" icon="invoice">
                    <div class="p-5">
                        <dl class="grid grid-cols-1 gap-x-8 gap-y-5 sm:grid-cols-2">
                            <div><dt class="mb-1 text-xs text-stone-400">رقم الفاتورة</dt><dd class="text-sm font-semibold text-stone-900" dir="ltr">{{ invoice.s_number }}</dd></div>
                            <div><dt class="mb-1 text-xs text-stone-400">الحالة</dt><dd><Badge :color="invoice.status_color">{{ invoice.status_label }}</Badge></dd></div>
                            <div><dt class="mb-1 text-xs text-stone-400">النوع</dt><dd class="text-sm text-stone-800">{{ invoice.type_label }}</dd></div>
                            <div><dt class="mb-1 text-xs text-stone-400">العملة</dt><dd class="text-sm text-stone-800" dir="ltr">{{ invoice.currency }}</dd></div>
                            <div><dt class="mb-1 text-xs text-stone-400">تاريخ الإصدار</dt><dd class="text-sm text-stone-800">{{ invoice.issued }} <span class="text-stone-400" dir="ltr">{{ invoice.time }}</span></dd></div>
                            <div v-if="invoice.user"><dt class="mb-1 text-xs text-stone-400">أُنشئت بواسطة</dt><dd class="text-sm text-stone-800">{{ invoice.user }}</dd></div>
                            <div v-if="invoice.order_uuid"><dt class="mb-1 text-xs text-stone-400">الطلب المرتبط</dt><dd><RouterLink :to="`/orders/${invoice.order_uuid}`" class="text-sm font-medium text-primary-600 hover:text-primary-700">{{ invoice.order_label }}</RouterLink></dd></div>
                        </dl>
                    </div>
                </Section>

                <Section :title="`البنود (${invoice.items.length})`" icon="list">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-stone-100 text-xs text-stone-400">
                                    <th class="px-5 py-3 text-start font-medium">البند</th>
                                    <th class="px-3 py-3 text-center font-medium">الكمية</th>
                                    <th class="px-3 py-3 text-end font-medium">السعر</th>
                                    <th class="px-5 py-3 text-end font-medium">الإجمالي</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-stone-50">
                                <tr v-for="item in invoice.items" :key="item.id">
                                    <td class="px-5 py-4">
                                        <div class="min-w-0 space-y-1">
                                            <p class="font-medium text-stone-900">{{ item.name }}</p>
                                            <p v-if="item.type" class="text-xs text-stone-400">{{ item.type }}</p>
                                        </div>
                                    </td>
                                    <td class="px-3 py-4 text-center text-stone-800" dir="ltr">{{ item.quantity }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-end text-stone-600"><Money :formatted="item.amount_after_vat_formatted" /></td>
                                    <td class="whitespace-nowrap px-5 py-4 text-end font-semibold text-stone-900"><Money :formatted="item.total_after_vat_formatted" /></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="border-t border-stone-100 px-5 py-4">
                        <div class="ms-auto max-w-xs space-y-2">
                            <div class="flex items-center justify-between text-sm"><span class="text-stone-500">المجموع الفرعي</span><span class="text-stone-800"><Money :formatted="invoice.subtotal_after_vat_formatted" /></span></div>
                            <div class="flex items-center justify-between border-t border-stone-100 pt-2"><span class="font-semibold text-stone-800">الإجمالي شامل الضريبة</span><span class="text-lg font-bold text-primary-700"><Money :formatted="invoice.total_after_vat_formatted" /></span></div>
                        </div>
                    </div>
                </Section>

                <Section v-if="invoice.payments.length" title="المدفوعات" icon="coin">
                    <div class="p-5">
                        <div class="divide-y divide-stone-50">
                            <div v-for="payment in invoice.payments" :key="payment.id" class="flex flex-col gap-3 py-4 first:pt-0 last:pb-0 sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <RouterLink :to="`/payments/${payment.uuid}`" class="text-sm font-semibold text-stone-800 transition hover:text-primary-600">دفعة #{{ payment.id }}</RouterLink>
                                        <Badge :color="payment.status_color">{{ payment.status_label }}</Badge>
                                    </div>
                                    <p class="mt-1 text-xs text-stone-400">{{ payment.created }}</p>
                                </div>
                                <p class="shrink-0 text-base font-bold text-stone-900 sm:text-end"><Money :formatted="payment.amount_formatted" /></p>
                            </div>
                        </div>
                    </div>
                </Section>
            </div>
        </div>
    </Container>
</template>
