<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import Container from '../../components/ui/Container.vue';
import Section from '../../components/ui/Section.vue';
import Badge from '../../components/ui/Badge.vue';
import Icon from '../../components/ui/Icon.vue';
import { getPayment, paymentStatusLabel, paymentStatusColor, reasonLabel, sourceTypeLabel, gatewayLabel } from '../../data/payments.js';
import { money } from '../../data/orders.js';

// Port of resources/views/admin/orders/payment-detail.blade.php (dummy data).
const route = useRoute();
const payment = computed(() => getPayment(route.params.uuid));
</script>

<template>
    <Container :title="`الطلبات / عملية #${payment.id}`" back-route="/orders?tab=payments">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Sidebar -->
            <div class="space-y-6 lg:order-1">
                <Section title="ملخص العملية" icon="coin">
                    <div class="space-y-3 p-5">
                        <div class="flex items-center justify-between text-sm"><span class="text-gray-500">المبلغ</span><span class="font-medium text-gray-800">{{ money(payment.amount) }}</span></div>
                        <div class="border-t border-gray-100 pt-4">
                            <div class="flex items-center justify-between"><span class="text-sm font-semibold text-gray-800">الإجمالي</span><span class="text-xl font-bold text-primary-700">{{ money(payment.amount) }}</span></div>
                        </div>
                        <div class="space-y-2 border-t border-gray-100 pt-3">
                            <div class="flex items-center justify-between text-sm"><span class="text-gray-500">العملة</span><span class="font-medium text-gray-800" dir="ltr">{{ payment.currency }}</span></div>
                        </div>
                    </div>
                </Section>

                <Section title="الدافع" icon="user">
                    <div class="p-5">
                        <div v-if="payment.payer" class="flex flex-col items-center py-2 text-center">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-lg font-semibold text-gray-600">{{ payment.payer.charAt(0) }}</div>
                            <p class="mt-3 text-sm font-semibold text-gray-700">{{ payment.payer }}</p>
                            <p v-if="payment.email" class="mt-1 truncate text-xs text-gray-400">{{ payment.email }}</p>
                        </div>
                        <div v-else class="flex flex-col items-center py-4 text-center">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400"><Icon name="user" class="h-6 w-6" /></div>
                            <p class="mt-3 text-sm font-semibold text-gray-700">—</p>
                            <p class="mt-1 text-xs text-gray-400">لا توجد بيانات دافع</p>
                        </div>
                    </div>
                </Section>

                <Section title="معلومات تقنية" icon="info">
                    <div class="space-y-3 p-5 text-sm">
                        <div><p class="text-xs text-gray-400">المعرّف</p><p class="mt-0.5 break-all font-mono text-xs text-gray-600" dir="ltr">{{ payment.uuid }}</p></div>
                        <div><p class="text-xs text-gray-400">تاريخ الإنشاء</p><p class="mt-0.5 text-xs font-medium text-gray-800">{{ payment.created }} {{ payment.time }}</p></div>
                    </div>
                </Section>
            </div>

            <!-- Main -->
            <div class="space-y-6 lg:order-2 lg:col-span-2">
                <Section title="تفاصيل الدفع" icon="receipt">
                    <div class="p-5">
                        <dl class="grid grid-cols-1 gap-x-8 gap-y-5 sm:grid-cols-2">
                            <div><dt class="mb-1 text-xs text-gray-400">رقم العملية</dt><dd class="text-sm font-semibold text-gray-900">#{{ payment.id }}</dd></div>
                            <div><dt class="mb-1 text-xs text-gray-400">الحالة</dt><dd><Badge :color="paymentStatusColor(payment.status)">{{ paymentStatusLabel(payment.status) }}</Badge></dd></div>
                            <div><dt class="mb-1 text-xs text-gray-400">نوع العملية</dt><dd class="text-sm text-gray-800">{{ reasonLabel(payment.reason) }}</dd></div>
                            <div><dt class="mb-1 text-xs text-gray-400">تاريخ العملية</dt><dd class="text-sm text-gray-800">{{ payment.created }} <span class="text-gray-400" dir="ltr">{{ payment.time }}</span></dd></div>
                            <div><dt class="mb-1 text-xs text-gray-400">بوابة الدفع</dt><dd class="flex items-center gap-1.5 text-sm text-gray-800"><Icon name="bank" class="h-4 w-4 text-gray-400" /> {{ gatewayLabel(payment.gateway) }}</dd></div>
                            <div><dt class="mb-1 text-xs text-gray-400">طريقة الدفع</dt><dd class="flex items-center gap-1.5 text-sm text-gray-800"><Icon name="card" class="h-4 w-4 text-gray-400" /> {{ sourceTypeLabel(payment.source_type) }}</dd></div>
                            <div v-if="payment.card"><dt class="mb-1 text-xs text-gray-400">البطاقة</dt><dd class="font-mono text-sm text-gray-800" dir="ltr">{{ payment.card }}</dd></div>
                        </dl>
                    </div>
                </Section>

                <Section v-if="payment.order_uuid" title="الطلب المرتبط" icon="package">
                    <div class="p-5">
                        <RouterLink :to="`/orders/${payment.order_uuid}`" class="flex items-center justify-between gap-4 rounded-lg bg-gray-50 p-4 transition hover:bg-gray-100">
                            <div>
                                <p class="font-semibold text-gray-800">طلب #{{ payment.order_number }}</p>
                                <p class="mt-1 text-sm text-gray-500">عرض تفاصيل الطلب</p>
                            </div>
                            <div class="text-end">
                                <p class="font-bold text-gray-900">{{ money(payment.amount) }}</p>
                                <Icon name="link" class="mt-2 h-4 w-4 text-primary-500" />
                            </div>
                        </RouterLink>
                    </div>
                </Section>
            </div>
        </div>
    </Container>
</template>
