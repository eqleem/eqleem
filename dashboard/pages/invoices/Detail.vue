<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import Container from '../../components/ui/Container.vue';
import Section from '../../components/ui/Section.vue';
import Badge from '../../components/ui/Badge.vue';
import Icon from '../../components/ui/Icon.vue';
import { getInvoice, invoiceStatusLabel, invoiceStatusColor, invoiceTypeLabel, invoiceDue } from '../../data/invoices.js';
import { money } from '../../data/orders.js';
import { paymentStatusLabel, paymentStatusColor } from '../../data/payments.js';

// Port of resources/views/admin/orders/invoice-detail.blade.php (dummy data).
const route = useRoute();
const invoice = computed(() => getInvoice(route.params.uuid));
const vat = computed(() => invoice.value.total_after_vat - invoice.value.total_before_vat);
const due = computed(() => invoiceDue(invoice.value));
</script>

<template>
    <Container :title="`الطلبات / ${invoice.s_number}`" back-route="/orders?tab=invoices">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Sidebar -->
            <div class="space-y-6 lg:order-1">
                <Section title="ملخص الفاتورة" icon="invoice">
                    <div class="space-y-3 p-5">
                        <div class="flex items-center justify-between text-sm"><span class="text-gray-500">قبل الضريبة</span><span class="font-medium text-gray-800">{{ money(invoice.total_before_vat) }}</span></div>
                        <div class="flex items-center justify-between text-sm"><span class="text-gray-500">الضريبة</span><span class="font-medium text-gray-800">{{ money(vat) }}</span></div>
                        <div class="border-t border-gray-100 pt-4">
                            <div class="flex items-center justify-between"><span class="text-sm font-semibold text-gray-800">الإجمالي</span><span class="text-xl font-bold text-primary-700">{{ money(invoice.total_after_vat) }}</span></div>
                        </div>
                        <div class="space-y-2 border-t border-gray-100 pt-3">
                            <div class="flex items-center justify-between text-sm"><span class="text-gray-500">المدفوع</span><span class="font-medium text-emerald-700">{{ money(invoice.amount_paid) }}</span></div>
                            <div class="flex items-center justify-between text-sm"><span class="text-gray-500">المتبقي</span><span class="font-medium" :class="due > 0 ? 'text-amber-700' : 'text-gray-800'">{{ money(due) }}</span></div>
                        </div>
                    </div>
                </Section>

                <Section v-if="invoice.note" title="ملاحظات" icon="note">
                    <div class="p-5"><p class="rounded-lg bg-gray-50 p-4 text-sm leading-relaxed text-gray-700">{{ invoice.note }}</p></div>
                </Section>
            </div>

            <!-- Main -->
            <div class="space-y-6 lg:order-2 lg:col-span-2">
                <Section title="تفاصيل الفاتورة" icon="invoice">
                    <div class="p-5">
                        <dl class="grid grid-cols-1 gap-x-8 gap-y-5 sm:grid-cols-2">
                            <div><dt class="mb-1 text-xs text-gray-400">رقم الفاتورة</dt><dd class="text-sm font-semibold text-gray-900" dir="ltr">{{ invoice.s_number }}</dd></div>
                            <div><dt class="mb-1 text-xs text-gray-400">الحالة</dt><dd><Badge :color="invoiceStatusColor(invoice.status)">{{ invoiceStatusLabel(invoice.status) }}</Badge></dd></div>
                            <div><dt class="mb-1 text-xs text-gray-400">النوع</dt><dd class="text-sm text-gray-800">{{ invoiceTypeLabel(invoice.type) }}</dd></div>
                            <div><dt class="mb-1 text-xs text-gray-400">العملة</dt><dd class="text-sm text-gray-800" dir="ltr">{{ invoice.currency }}</dd></div>
                            <div><dt class="mb-1 text-xs text-gray-400">تاريخ الإصدار</dt><dd class="text-sm text-gray-800">{{ invoice.issued }} <span class="text-gray-400" dir="ltr">{{ invoice.time }}</span></dd></div>
                            <div v-if="invoice.user"><dt class="mb-1 text-xs text-gray-400">أُنشئت بواسطة</dt><dd class="text-sm text-gray-800">{{ invoice.user }}</dd></div>
                            <div v-if="invoice.order_uuid"><dt class="mb-1 text-xs text-gray-400">الطلب المرتبط</dt><dd><RouterLink :to="`/orders/${invoice.order_uuid}`" class="text-sm font-medium text-primary-600 hover:text-primary-700">{{ invoice.order_label }}</RouterLink></dd></div>
                        </dl>
                    </div>
                </Section>

                <Section :title="`البنود (${invoice.items.length})`" icon="list">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-100 text-xs text-gray-400">
                                    <th class="px-5 py-3 text-start font-medium">البند</th>
                                    <th class="px-3 py-3 text-center font-medium">الكمية</th>
                                    <th class="px-3 py-3 text-end font-medium">السعر</th>
                                    <th class="px-5 py-3 text-end font-medium">الإجمالي</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <tr v-for="item in invoice.items" :key="item.id">
                                    <td class="px-5 py-4">
                                        <div class="min-w-0 space-y-1">
                                            <p class="font-medium text-gray-900">{{ item.name }}</p>
                                            <p v-if="item.type" class="text-xs text-gray-400">{{ item.type }}</p>
                                        </div>
                                    </td>
                                    <td class="px-3 py-4 text-center text-gray-800" dir="ltr">{{ item.quantity }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-end text-gray-600">{{ money(item.amount_after_vat) }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-end font-semibold text-gray-900">{{ money(item.total_after_vat) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="border-t border-gray-100 px-5 py-4">
                        <div class="ms-auto max-w-xs space-y-2">
                            <div class="flex items-center justify-between text-sm"><span class="text-gray-500">المجموع الفرعي</span><span class="text-gray-800">{{ money(invoice.subtotal_after_vat) }}</span></div>
                            <div class="flex items-center justify-between border-t border-gray-100 pt-2"><span class="font-semibold text-gray-800">الإجمالي شامل الضريبة</span><span class="text-lg font-bold text-primary-700">{{ money(invoice.total_after_vat) }}</span></div>
                        </div>
                    </div>
                </Section>

                <Section v-if="invoice.payments.length" title="المدفوعات" icon="coin">
                    <div class="p-5">
                        <div class="divide-y divide-gray-50">
                            <div v-for="payment in invoice.payments" :key="payment.id" class="flex flex-col gap-3 py-4 first:pt-0 last:pb-0 sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <RouterLink :to="`/payments/${payment.uuid}`" class="text-sm font-semibold text-gray-800 transition hover:text-primary-600">دفعة #{{ payment.id }}</RouterLink>
                                        <Badge :color="paymentStatusColor(payment.status)">{{ paymentStatusLabel(payment.status) }}</Badge>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-400">{{ payment.created }}</p>
                                </div>
                                <p class="shrink-0 text-base font-bold text-gray-900 sm:text-end">{{ money(payment.amount) }}</p>
                            </div>
                        </div>
                    </div>
                </Section>
            </div>
        </div>
    </Container>
</template>
