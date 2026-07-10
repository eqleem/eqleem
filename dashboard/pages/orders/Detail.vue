<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import Container from '../../components/ui/Container.vue';
import Section from '../../components/ui/Section.vue';
import Badge from '../../components/ui/Badge.vue';
import Icon from '../../components/ui/Icon.vue';
import Button from '../../components/ui/Button.vue';
import { getOrderDetail, money, statusLabel, statusColor, paymentLabel, paymentColor, walkingClientLabel } from '../../data/orders.js';
import { avatarFor } from '../../data/clients.js';

// Port of resources/views/admin/orders/detail.blade.php (dummy data).
const route = useRoute();
const order = computed(() => getOrderDetail(route.params.uuid));
</script>

<template>
    <Container :title="`الطلبات / #${order.number}`" back-route="/orders">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Sidebar -->
            <div class="space-y-6 lg:order-1">
                <Section title="ملخص الطلب" icon="receipt">
                    <div class="space-y-3 p-5">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">المجموع الفرعي</span>
                            <span class="font-medium text-gray-800">{{ money(order.subtotal) }}</span>
                        </div>
                        <div v-if="order.discount > 0" class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">الخصومات</span>
                            <span class="font-medium text-red-600">−{{ money(order.discount) }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">الضريبة</span>
                            <span class="font-medium text-gray-800">{{ money(order.tax) }}</span>
                        </div>
                        <div class="border-t border-gray-100 pt-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-semibold text-gray-800">الإجمالي</span>
                                <span class="text-xl font-bold text-primary-700">{{ money(order.grand) }}</span>
                            </div>
                        </div>
                        <div class="space-y-2 border-t border-gray-100 pt-3">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-500">المدفوع</span>
                                <span class="font-medium text-emerald-700">{{ money(order.paid) }}</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-500">المتبقي</span>
                                <span class="font-medium" :class="order.due > 0 ? 'text-amber-700' : 'text-gray-800'">{{ money(order.due) }}</span>
                            </div>
                        </div>
                    </div>
                </Section>

                <Section title="العميل" icon="user">
                    <div class="p-5">
                        <template v-if="order.client">
                            <div class="flex items-center gap-3">
                                <img :src="avatarFor(order.client.name)" :alt="order.client.name" class="h-12 w-12 shrink-0 rounded-full bg-gray-100 object-cover">
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-gray-900">{{ order.client.name }}</p>
                                    <p class="truncate text-sm text-gray-500">{{ order.client.email }}</p>
                                    <p class="text-sm text-gray-500" dir="ltr">{{ order.client.phone }}</p>
                                </div>
                            </div>
                            <Button :href="`#/clients/${order.client.uuid}`" label="عرض ملف العميل" variant="outline" class="mt-4 w-full" />
                        </template>
                        <div v-else class="flex flex-col items-center py-4 text-center">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400"><Icon name="user" class="h-6 w-6" /></div>
                            <p class="mt-3 text-sm font-semibold text-gray-700">{{ walkingClientLabel }}</p>
                            <p class="mt-1 text-xs text-gray-400">طلب بدون حساب عميل</p>
                        </div>
                    </div>
                </Section>
            </div>

            <!-- Main -->
            <div class="space-y-6 lg:order-2 lg:col-span-2">
                <Section title="تفاصيل الطلب" icon="package">
                    <template #action>
                        <Button label="تغيير الحالة" variant="outline" class="!h-8 !px-3 !text-xs">
                            <template #icon><Icon name="refresh" class="h-4 w-4" /></template>
                        </Button>
                    </template>
                    <div class="p-5">
                        <dl class="grid grid-cols-1 gap-x-8 gap-y-5 sm:grid-cols-2">
                            <div>
                                <dt class="mb-1 text-xs text-gray-400">رقم الطلب</dt>
                                <dd class="text-sm font-semibold text-gray-900">#{{ order.number }}</dd>
                            </div>
                            <div>
                                <dt class="mb-1 text-xs text-gray-400">حالة الطلب</dt>
                                <dd><Badge :color="statusColor(order.status)">{{ statusLabel(order.status) }}</Badge></dd>
                            </div>
                            <div>
                                <dt class="mb-1 text-xs text-gray-400">تاريخ الطلب</dt>
                                <dd class="text-sm text-gray-800">{{ order.created }}</dd>
                            </div>
                            <div>
                                <dt class="mb-1 text-xs text-gray-400">حالة الدفع</dt>
                                <dd><Badge :color="paymentColor(order.payment_status)">{{ paymentLabel(order.payment_status) }}</Badge></dd>
                            </div>
                            <div>
                                <dt class="mb-1 text-xs text-gray-400">مصدر الطلب</dt>
                                <dd class="flex items-center gap-1.5 text-sm text-gray-800"><Icon name="store" class="h-4 w-4 text-gray-400" /> يدوي</dd>
                            </div>
                            <div>
                                <dt class="mb-1 text-xs text-gray-400">طريقة الدفع</dt>
                                <dd class="flex items-center gap-1.5 text-sm text-gray-800"><Icon name="card" class="h-4 w-4 text-gray-400" /> نقداً</dd>
                            </div>
                        </dl>
                    </div>
                </Section>

                <Section :title="`العناصر (${order.items.length})`" icon="cart">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-100 text-xs text-gray-400">
                                    <th class="px-5 py-3 text-start font-medium">المنتج</th>
                                    <th class="px-3 py-3 text-start font-medium">السعر</th>
                                    <th class="px-3 py-3 text-center font-medium">الكمية</th>
                                    <th class="px-5 py-3 text-end font-medium">الإجمالي</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <tr v-for="item in order.items" :key="item.id">
                                    <td class="px-5 py-4">
                                        <div class="flex items-start gap-3">
                                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-400"><Icon name="package" class="h-5 w-5" /></div>
                                            <div class="min-w-0 space-y-1">
                                                <p class="font-medium text-gray-900">{{ item.name }}</p>
                                                <Badge :color="item.type_color">{{ item.type_label }}</Badge>
                                                <p v-if="item.discount > 0" class="text-xs text-red-500">خصم {{ money(item.discount) }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-gray-600">{{ money(item.unit_price) }}</td>
                                    <td class="px-3 py-4 text-center text-gray-800">{{ item.qty }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-end font-semibold text-gray-900">{{ money(item.line_total) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="border-t border-gray-100 px-5 py-4">
                        <div class="ms-auto max-w-xs space-y-2">
                            <div class="flex items-center justify-between text-sm"><span class="text-gray-500">المجموع الفرعي</span><span class="text-gray-800">{{ money(order.subtotal) }}</span></div>
                            <div v-if="order.discount > 0" class="flex items-center justify-between text-sm"><span class="text-gray-500">الخصومات</span><span class="text-red-600">−{{ money(order.discount) }}</span></div>
                            <div class="flex items-center justify-between text-sm"><span class="text-gray-500">الضريبة</span><span class="text-gray-800">{{ money(order.tax) }}</span></div>
                            <div class="flex items-center justify-between border-t border-gray-100 pt-2"><span class="font-semibold text-gray-800">الإجمالي</span><span class="text-lg font-bold text-primary-700">{{ money(order.grand) }}</span></div>
                        </div>
                    </div>
                </Section>

                <Section title="المدفوعات" icon="coin">
                    <template v-if="order.due > 0" #action>
                        <Button label="إضافة دفعة" variant="outline" class="!h-8 !px-3 !text-xs">
                            <template #icon><Icon name="plus" class="h-4 w-4" /></template>
                        </Button>
                    </template>
                    <div class="p-5">
                        <div v-if="order.payments.length === 0" class="flex flex-col items-center gap-2 py-6 text-center">
                            <Icon name="coin" class="h-12 w-12 p-0.5 text-gray-400" />
                            <p class="text-gray-700">لا توجد مدفوعات بعد.</p>
                            <small class="text-gray-500">سجّل دفعة لإنشاء فاتورة وربطها بهذا الطلب.</small>
                        </div>
                        <div v-else class="divide-y divide-gray-50">
                            <div v-for="payment in order.payments" :key="payment.id" class="flex flex-col gap-3 py-4 first:pt-0 last:pb-0 sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <RouterLink :to="`/payments/${payment.uuid}`" class="text-sm font-semibold text-gray-800 transition hover:text-primary-600">{{ payment.method }}</RouterLink>
                                        <Badge :color="statusColor(payment.status) === 'gray' ? 'green' : 'green'">مدفوع</Badge>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-400">{{ payment.created }}</p>
                                </div>
                                <p class="shrink-0 text-base font-bold text-gray-900 sm:text-end">{{ money(payment.amount) }} {{ payment.currency }}</p>
                            </div>
                        </div>
                    </div>
                </Section>

                <Section title="سجل النشاط" icon="history">
                    <div class="p-5">
                        <div class="space-y-0">
                            <div v-for="(entry, index) in order.activity" :key="entry.key" class="relative flex gap-4 pb-6 last:pb-0">
                                <span v-if="index < order.activity.length - 1" class="absolute bottom-0 top-8 w-px bg-gray-200" style="inset-inline-start: 0.875rem;"></span>
                                <div class="relative z-10 flex h-7 w-7 shrink-0 items-center justify-center rounded-full" :class="entry.type === 'status' ? 'bg-primary-50 text-primary-600' : 'bg-gray-100 text-gray-500'">
                                    <Icon :name="entry.type === 'status' ? 'refresh' : 'history'" class="h-3.5 w-3.5" />
                                </div>
                                <div class="min-w-0 flex-1 pt-0.5">
                                    <p class="text-sm text-gray-800">{{ entry.title }}</p>
                                    <Badge v-if="entry.type === 'status'" :color="statusColor(entry.status)" class="mt-1">{{ statusLabel(entry.status) }}</Badge>
                                    <p class="mt-1 text-xs text-gray-400">{{ entry.date }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </Section>
            </div>
        </div>
    </Container>
</template>
