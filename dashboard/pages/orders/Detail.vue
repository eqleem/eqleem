<script setup>
import { onMounted, onUnmounted, watch } from 'vue';
import { storeToRefs } from 'pinia';
import { useRoute } from 'vue-router';
import Container from '../../components/ui/Container.vue';
import Section from '../../components/ui/Section.vue';
import Badge from '../../components/ui/Badge.vue';
import Icon from '../../components/ui/Icon.vue';
import Button from '../../components/ui/Button.vue';
import Modal from '../../components/ui/Modal.vue';
import AddPayment from '../../components/orders/AddPayment.vue';
import ChangeStatus from '../../components/orders/ChangeStatus.vue';
import { walkingClientLabel } from '../../data/orders.js';
import { openModal } from '../../lib/modal.js';
import { useOrdersStore } from '../../stores/orders.js';

const route = useRoute();
const ordersStore = useOrdersStore();
const { detail: order, detailLoading: loading, detailError: error } = storeToRefs(ordersStore);

async function loadOrder(uuid) {
    if (!uuid) {
        return;
    }

    try {
        await ordersStore.fetchOrder(uuid);
    } catch {
        // handled in store
    }
}

watch(() => route.params.uuid, (uuid) => loadOrder(uuid));
onMounted(() => loadOrder(route.params.uuid));
onUnmounted(() => ordersStore.clearDetail());
</script>

<template>
    <Container :title="`الطلبات / #${order?.number ?? '...'}`" back-route="/orders">
        <div v-if="loading && !order" class="flex items-center justify-center rounded-xl bg-white p-16">
            <svg class="h-10 w-10 animate-spin text-gray-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" d="M12 3a9 9 0 1 0 9 9" />
            </svg>
        </div>

        <div v-else-if="error && !order" class="flex flex-col items-center justify-center gap-3 rounded-xl bg-white p-16 text-center">
            <p class="text-sm text-red-600">{{ error }}</p>
            <button type="button" class="rounded-lg border bg-white px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-100" @click="loadOrder(route.params.uuid)">
                إعادة المحاولة
            </button>
        </div>

        <div v-else-if="order" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:order-1">
                <Section title="ملخص الطلب" icon="receipt">
                    <div class="space-y-3 p-5">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">المجموع الفرعي</span>
                            <span class="font-medium text-gray-800"><Money :formatted="order.subtotal_formatted" /></span>
                        </div>
                        <div v-if="order.discount_total > 0" class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">الخصومات</span>
                            <span class="font-medium text-red-600 inline-flex items-baseline">−<Money :formatted="order.discount_total_formatted" class="inline-flex" /></span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">الضريبة</span>
                            <span class="font-medium text-gray-800"><Money :formatted="order.tax_total_formatted" /></span>
                        </div>
                        <div class="border-t border-gray-100 pt-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-semibold text-gray-800">الإجمالي</span>
                                <span class="text-xl font-bold text-primary-700"><Money :formatted="order.grand_total_formatted" /></span>
                            </div>
                        </div>
                        <div class="space-y-2 border-t border-gray-100 pt-3">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-500">المدفوع</span>
                                <span class="font-medium text-emerald-700"><Money :formatted="order.paid_total_formatted" /></span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-500">المتبقي</span>
                                <span class="font-medium" :class="order.due_total > 0 ? 'text-amber-700' : 'text-gray-800'"><Money :formatted="order.due_total_formatted" /></span>
                            </div>
                        </div>
                    </div>
                </Section>

                <Section title="العميل" icon="user">
                    <div class="p-5">
                        <template v-if="order.client">
                            <div class="flex items-center gap-3">
                                <img :src="order.client.avatar" :alt="order.client.name" class="h-12 w-12 shrink-0 rounded-full bg-gray-100 object-cover">
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-gray-900">{{ order.client.name }}</p>
                                    <p class="truncate text-sm text-gray-500">{{ order.client.email }}</p>
                                    <p class="text-sm text-gray-500" dir="ltr">{{ order.client.phone }}</p>
                                </div>
                            </div>
                            <RouterLink :to="`/clients/${order.client.uuid}`" class="mt-4 block">
                                <Button label="عرض ملف العميل" variant="outline" class="w-full" />
                            </RouterLink>
                        </template>
                        <div v-else class="flex flex-col items-center py-4 text-center">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400"><Icon name="user" class="h-6 w-6" /></div>
                            <p class="mt-3 text-sm font-semibold text-gray-700">{{ walkingClientLabel }}</p>
                            <p class="mt-1 text-xs text-gray-400">طلب بدون حساب عميل</p>
                        </div>
                    </div>
                </Section>
            </div>

            <div class="space-y-6 lg:order-2 lg:col-span-2">
                <Section title="تفاصيل الطلب" icon="package">
                    <template #action>
                        <Button
                            type="button"
                            label="تغيير الحالة"
                            variant="outline"
                            class="!h-8 !px-3 !text-xs"
                            @click="openModal('change-order-status')"
                        >
                            <template #icon>
                                <Icon name="refresh" class="h-3.5 w-3.5" />
                            </template>
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
                                <dd><Badge :color="order.status_color">{{ order.status_label }}</Badge></dd>
                            </div>
                            <div>
                                <dt class="mb-1 text-xs text-gray-400">تاريخ الطلب</dt>
                                <dd class="text-sm text-gray-800">{{ order.created }}</dd>
                            </div>
                            <div>
                                <dt class="mb-1 text-xs text-gray-400">حالة الدفع</dt>
                                <dd><Badge :color="order.payment_status_color">{{ order.payment_status_label }}</Badge></dd>
                            </div>
                            <div>
                                <dt class="mb-1 text-xs text-gray-400">مصدر الطلب</dt>
                                <dd class="flex items-center gap-1.5 text-sm text-gray-800"><Icon name="store" class="h-4 w-4 text-gray-400" /> {{ order.channel_label }}</dd>
                            </div>
                            <div>
                                <dt class="mb-1 text-xs text-gray-400">طريقة الدفع</dt>
                                <dd class="flex items-center gap-1.5 text-sm text-gray-800"><Icon name="card" class="h-4 w-4 text-gray-400" /> {{ order.payment_method_label }}</dd>
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
                                                <div class="flex flex-wrap items-center gap-1.5">
                                                    <Badge :color="item.type_color">{{ item.type_label }}</Badge>
                                                    <Badge v-if="item.booking?.status_label" :color="item.booking.status_color" size="sm">{{ item.booking.status_label }}</Badge>
                                                </div>

                                                <template v-if="item.is_booking && item.booking">
                                                    <div class="mt-1.5 space-y-1 text-xs text-gray-500">
                                                        <p v-if="item.booking.calendar_name">
                                                            <span class="text-gray-400">{{ item.booking.calendar_label }}:</span>
                                                            {{ item.booking.calendar_name }}
                                                        </p>

                                                        <template v-if="item.type === 'service'">
                                                            <p v-if="item.booking.date_label">
                                                                <span class="text-gray-400">التاريخ:</span>
                                                                {{ item.booking.date_label }}
                                                            </p>
                                                            <p v-if="item.booking.time_label">
                                                                <span class="text-gray-400">الوقت:</span>
                                                                <span dir="ltr">{{ item.booking.time_label }}</span>
                                                            </p>
                                                        </template>

                                                        <template v-else-if="item.type === 'unit_rental'">
                                                            <p v-if="item.booking.dates_label">
                                                                <span class="text-gray-400">التواريخ:</span>
                                                                {{ item.booking.dates_label }}
                                                            </p>
                                                            <p v-if="item.booking.duration_label" class="text-gray-400">
                                                                {{ item.booking.duration_label }}
                                                            </p>
                                                        </template>
                                                    </div>
                                                </template>

                                                <div v-else class="flex flex-wrap items-center gap-x-2 gap-y-0.5 text-xs text-gray-500">
                                                    <span v-if="item.sku && ['product', 'digital_product', 'menu'].includes(item.type)" dir="ltr">SKU: {{ item.sku }}</span>
                                                    <span v-if="item.type === 'course'">المقاعد: {{ item.qty }}</span>
                                                    <span v-if="item.description">{{ item.description }}</span>
                                                </div>

                                                <p v-if="item.discount > 0" class="text-xs text-red-500 inline-flex items-baseline gap-1">خصم <Money :formatted="item.discount_formatted" class="inline-flex" /></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-gray-600"><Money :formatted="item.unit_price_formatted" /></td>
                                    <td class="px-3 py-4 text-center text-gray-800">{{ item.qty }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-end font-semibold text-gray-900"><Money :formatted="item.line_total_formatted" /></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="border-t border-gray-100 px-5 py-4">
                        <div class="ms-auto max-w-xs space-y-2">
                            <div class="flex items-center justify-between text-sm"><span class="text-gray-500">المجموع الفرعي</span><span class="text-gray-800"><Money :formatted="order.subtotal_formatted" /></span></div>
                            <div v-if="order.discount_total > 0" class="flex items-center justify-between text-sm"><span class="text-gray-500">الخصومات</span><span class="text-red-600 inline-flex items-baseline">−<Money :formatted="order.discount_total_formatted" class="inline-flex" /></span></div>
                            <div class="flex items-center justify-between text-sm"><span class="text-gray-500">الضريبة</span><span class="text-gray-800"><Money :formatted="order.tax_total_formatted" /></span></div>
                            <div class="flex items-center justify-between border-t border-gray-100 pt-2"><span class="font-semibold text-gray-800">الإجمالي</span><span class="text-lg font-bold text-primary-700"><Money :formatted="order.grand_total_formatted" /></span></div>
                        </div>
                    </div>
                </Section>

                <Section title="المدفوعات" icon="coin">
                    <template #action>
                        <Button
                            v-if="order.due_total > 0"
                            type="button"
                            label="إضافة دفعة"
                            variant="outline"
                            class="!h-8 !px-3 !text-xs"
                            @click="openModal('add-order-payment')"
                        >
                            <template #icon>
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" d="M12 5v14M5 12h14" />
                                </svg>
                            </template>
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
                                        <Badge :color="payment.status_color">{{ payment.status_label }}</Badge>
                                    </div>
                                    <p class="mt-1 text-xs text-gray-400">{{ payment.created }}</p>
                                </div>
                                <p class="shrink-0 text-base font-bold text-gray-900 sm:text-end"><Money :formatted="payment.amount_formatted" /></p>
                            </div>
                        </div>
                    </div>
                </Section>

                <Section title="سجل النشاط" icon="history">
                    <div class="p-5">
                        <div v-if="order.activity.length === 0" class="py-6 text-center text-sm text-gray-500">لا يوجد نشاط بعد.</div>
                        <div v-else class="space-y-0">
                            <div v-for="(entry, index) in order.activity" :key="entry.key" class="relative flex gap-4 pb-6 last:pb-0">
                                <span v-if="index < order.activity.length - 1" class="absolute bottom-0 top-8 w-px bg-gray-200" style="inset-inline-start: 0.875rem;"></span>
                                <div class="relative z-10 flex h-7 w-7 shrink-0 items-center justify-center rounded-full" :class="entry.type === 'status' ? 'bg-primary-50 text-primary-600' : 'bg-gray-100 text-gray-500'">
                                    <Icon :name="entry.type === 'status' ? 'refresh' : 'history'" class="h-3.5 w-3.5" />
                                </div>
                                <div class="min-w-0 flex-1 pt-0.5">
                                    <p class="text-sm text-gray-800">{{ entry.title }}</p>
                                    <Badge v-if="entry.type === 'status'" :color="entry.status_color" class="mt-1">{{ entry.status_label }}</Badge>
                                    <p class="mt-1 text-xs text-gray-400">{{ entry.date }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </Section>
            </div>
        </div>

        <Modal v-if="order" name="add-order-payment" title="تسجيل دفعة" size="lg">
            <AddPayment :order="order" />
        </Modal>

        <Modal v-if="order" name="change-order-status" title="تغيير حالة الطلب" size="lg">
            <ChangeStatus :order="order" />
        </Modal>
    </Container>
</template>
