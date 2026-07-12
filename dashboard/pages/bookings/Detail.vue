<script setup>
import { onMounted, onUnmounted, watch } from 'vue';
import { storeToRefs } from 'pinia';
import { useRoute } from 'vue-router';
import Container from '../../components/ui/Container.vue';
import Section from '../../components/ui/Section.vue';
import Badge from '../../components/ui/Badge.vue';
import Icon from '../../components/ui/Icon.vue';
import Button from '../../components/ui/Button.vue';
import { walkingClientLabel } from '../../data/bookings.js';
import { useBookingsStore } from '../../stores/bookings.js';

const route = useRoute();
const bookingsStore = useBookingsStore();
const { detail: booking, detailLoading: loading, detailError: error } = storeToRefs(bookingsStore);

async function loadBooking(id) {
    if (!id) {
        return;
    }

    try {
        await bookingsStore.fetchBooking(id);
    } catch {
        // handled in store
    }
}

watch(() => route.params.id, (id) => loadBooking(id));
onMounted(() => loadBooking(route.params.id));
onUnmounted(() => bookingsStore.clearDetail());
</script>

<template>
    <Container :title="`الحجوزات / #${booking?.id ?? '...'}`" back-route="/orders?tab=bookings">
        <div v-if="loading && !booking" class="flex items-center justify-center rounded-xl bg-white p-16">
            <svg class="h-10 w-10 animate-spin text-stone-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" d="M12 3a9 9 0 1 0 9 9" />
            </svg>
        </div>

        <div v-else-if="error && !booking" class="flex flex-col items-center justify-center gap-3 rounded-xl bg-white p-16 text-center">
            <p class="text-sm text-red-600">{{ error }}</p>
            <button
                type="button"
                class="rounded-lg border bg-white px-3 py-1.5 text-sm text-stone-700 hover:bg-stone-100"
                @click="loadBooking(route.params.id)"
            >
                إعادة المحاولة
            </button>
        </div>

        <div v-else-if="booking" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:order-1">
                <Section title="ملخص الحجز" icon="calendar">
                    <div class="space-y-3 p-5">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-stone-500">السعر</span>
                            <span class="font-medium text-stone-800"><Money :formatted="booking.price_formatted" /></span>
                        </div>
                        <div class="border-t border-stone-100 pt-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-semibold text-stone-800">الإجمالي</span>
                                <span class="text-xl font-bold text-primary-700"><Money :formatted="booking.price_formatted" /></span>
                            </div>
                        </div>
                        <div class="space-y-2 border-t border-stone-100 pt-3">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-stone-500">العملة</span>
                                <span class="font-medium text-stone-800" dir="ltr">{{ booking.currency }}</span>
                            </div>
                        </div>
                    </div>
                </Section>

                <Section title="العميل" icon="user">
                    <div class="p-5">
                        <template v-if="booking.client">
                            <div class="flex items-center gap-3">
                                <img :src="booking.client.avatar" :alt="booking.client.name" class="h-12 w-12 shrink-0 rounded-full bg-stone-100 object-cover">
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-stone-900">{{ booking.client.name }}</p>
                                    <p v-if="booking.client.email" class="truncate text-sm text-stone-500">{{ booking.client.email }}</p>
                                    <p v-if="booking.client.phone" class="text-sm text-stone-500" dir="ltr">{{ booking.client.phone }}</p>
                                </div>
                            </div>
                            <RouterLink v-if="booking.client.uuid" :to="`/clients/${booking.client.uuid}`" class="mt-4 block">
                                <Button label="عرض ملف العميل" variant="outline" class="w-full" />
                            </RouterLink>
                        </template>
                        <div v-else class="flex flex-col items-center py-4 text-center">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-stone-100 text-stone-400">
                                <Icon name="user" class="h-6 w-6" />
                            </div>
                            <p class="mt-3 text-sm font-semibold text-stone-700">{{ walkingClientLabel }}</p>
                            <p class="mt-1 text-xs text-stone-400">حجز بدون حساب عميل</p>
                        </div>
                    </div>
                </Section>
            </div>

            <div class="space-y-6 lg:order-2 lg:col-span-2">
                <Section title="تفاصيل الحجز" icon="package">
                    <div class="p-5">
                        <dl class="grid grid-cols-1 gap-x-8 gap-y-5 sm:grid-cols-2">
                            <div>
                                <dt class="mb-1 text-xs text-stone-400">رقم الحجز</dt>
                                <dd class="text-sm font-semibold text-stone-900">#{{ booking.id }}</dd>
                            </div>
                            <div>
                                <dt class="mb-1 text-xs text-stone-400">الحالة</dt>
                                <dd><Badge :color="booking.status_color">{{ booking.status_label }}</Badge></dd>
                            </div>
                            <div>
                                <dt class="mb-1 text-xs text-stone-400">تاريخ الإنشاء</dt>
                                <dd class="text-sm text-stone-800">{{ booking.created_label ?? booking.created }}</dd>
                            </div>
                            <div v-if="booking.content?.type_label">
                                <dt class="mb-1 text-xs text-stone-400">النوع</dt>
                                <dd><Badge color="blue">{{ booking.content.type_label }}</Badge></dd>
                            </div>
                            <div v-if="booking.content">
                                <dt class="mb-1 text-xs text-stone-400">{{ booking.content.type === 'unit_rental' ? 'الوحدة' : 'الخدمة' }}</dt>
                                <dd class="text-sm font-medium text-stone-900">{{ booking.content.title }}</dd>
                            </div>
                            <div v-if="booking.calendar">
                                <dt class="mb-1 text-xs text-stone-400">{{ booking.calendar.type_label || 'التقويم' }}</dt>
                                <dd class="text-sm text-stone-800">{{ booking.calendar.name }}</dd>
                            </div>
                            <div v-if="booking.content?.type === 'service' && booking.date_label">
                                <dt class="mb-1 text-xs text-stone-400">التاريخ</dt>
                                <dd class="text-sm text-stone-800">{{ booking.date_label }}</dd>
                            </div>
                            <div v-if="booking.time_label">
                                <dt class="mb-1 text-xs text-stone-400">الوقت</dt>
                                <dd class="text-sm text-stone-800" dir="ltr">{{ booking.time_label }}</dd>
                            </div>
                            <div v-if="booking.content?.type === 'unit_rental' && booking.dates_label">
                                <dt class="mb-1 text-xs text-stone-400">التواريخ</dt>
                                <dd class="text-sm text-stone-800">{{ booking.dates_label }}</dd>
                            </div>
                            <div v-if="booking.duration_label">
                                <dt class="mb-1 text-xs text-stone-400">المدة</dt>
                                <dd class="text-sm text-stone-800">{{ booking.duration_label }}</dd>
                            </div>
                            <div v-if="booking.content?.type !== 'service' && booking.content?.type !== 'unit_rental' && booking.dates_label">
                                <dt class="mb-1 text-xs text-stone-400">الموعد</dt>
                                <dd class="text-sm text-stone-800">{{ booking.dates_label }}</dd>
                            </div>
                        </dl>
                    </div>
                </Section>

                <Section v-if="booking.order?.uuid" title="الطلب المرتبط" icon="receipt">
                    <div class="p-5">
                        <RouterLink
                            :to="`/orders/${booking.order.uuid}`"
                            class="flex items-center justify-between gap-4 rounded-lg bg-stone-50 p-4 transition hover:bg-stone-100"
                        >
                            <div>
                                <p class="font-semibold text-stone-800">طلب #{{ booking.order.number }}</p>
                                <p class="mt-1 text-sm text-stone-500">عرض تفاصيل الطلب</p>
                            </div>
                            <div class="text-end">
                                <p class="font-bold text-stone-900"><Money :formatted="booking.price_formatted" /></p>
                                <Icon name="link" class="mt-2 h-4 w-4 text-primary-500" />
                            </div>
                        </RouterLink>
                    </div>
                </Section>

                <Section v-else title="الطلب المرتبط" icon="receipt">
                    <div class="flex flex-col items-center gap-2 p-8 text-center">
                        <Icon name="receipt" class="h-12 w-12 p-0.5 text-stone-400" />
                        <p class="text-stone-700">لا يوجد طلب مرتبط بهذا الحجز.</p>
                    </div>
                </Section>
            </div>
        </div>
    </Container>
</template>
