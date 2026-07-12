<script setup>
import { computed, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import arLocale from '@fullcalendar/core/locales/ar';
import {
    bookingTypeColorFallback,
    bookingTypeColors,
    bookingTypeOptions,
    mailtoHref,
    telHref,
    walkingClientLabel,
    whatsappHref,
} from '../../data/bookings.js';
import { closeModal, openModal } from '../../lib/modal.js';
import { useBookingsStore } from '../../stores/bookings.js';
import Icon from '../ui/Icon.vue';
import Badge from '../ui/Badge.vue';
import Button from '../ui/Button.vue';
import Modal from '../ui/Modal.vue';

const props = defineProps({
    search: { type: String, default: '' },
    status: { type: String, default: '' },
});

const router = useRouter();
const bookingsStore = useBookingsStore();
const calendarRef = ref(null);
const loading = ref(false);
const error = ref(null);
const selectedBooking = ref(null);

const modalName = 'booking-preview';

const typeLegend = computed(() => Object.entries(bookingTypeOptions).map(([type, label]) => ({
    type,
    label,
    swatch: (bookingTypeColors[type] ?? bookingTypeColorFallback).swatch,
})));

function colorsForType(type) {
    return bookingTypeColors[type] ?? bookingTypeColorFallback;
}

/** FullCalendar all-day `end` is exclusive — bump the calendar day by one. */
function exclusiveAllDayEnd(iso) {
    const day = String(iso).slice(0, 10);
    const [year, month, dayOfMonth] = day.split('-').map(Number);

    if (!year || !month || !dayOfMonth) {
        return iso;
    }

    const next = new Date(Date.UTC(year, month - 1, dayOfMonth + 1));

    return next.toISOString().slice(0, 10);
}

function bookingToEvent(booking) {
    const type = booking.content?.type ?? null;
    const colors = colorsForType(type);
    const isMultiDay = type === 'unit_rental'
        || (booking.start_at && booking.end_at && booking.start_at.slice(0, 10) !== booking.end_at.slice(0, 10));

    return {
        id: String(booking.id),
        title: booking.content?.title ?? 'حجز',
        start: isMultiDay ? booking.start_at.slice(0, 10) : booking.start_at,
        end: isMultiDay ? exclusiveAllDayEnd(booking.end_at) : booking.end_at,
        allDay: isMultiDay,
        backgroundColor: colors.background,
        borderColor: colors.border,
        textColor: colors.text,
        classNames: ['is-clickable'],
        extendedProps: {
            booking: {
                id: booking.id,
                title: booking.content?.title ?? 'حجز بدون محتوى',
                type: type,
                type_label: booking.content?.type_label ?? null,
                client: booking.client
                    ? {
                        name: booking.client.name ?? null,
                        email: booking.client.email ?? null,
                        phone: booking.client.phone ?? null,
                    }
                    : null,
                status: booking.status,
                status_label: booking.status_label,
                status_color: booking.status_color,
                calendar_name: booking.calendar?.name ?? null,
                calendar_type_label: booking.calendar?.type_label ?? null,
                dates_label: booking.dates_label,
                price_formatted: booking.price_formatted,
                order_uuid: booking.order?.uuid ?? null,
                order_number: booking.order?.number ?? null,
            },
        },
    };
}

async function loadEvents(fetchInfo, successCallback, failureCallback) {
    loading.value = true;
    error.value = null;

    try {
        const bookings = await bookingsStore.fetchCalendarBookings({
            from: fetchInfo.startStr,
            to: fetchInfo.endStr,
            search: props.search,
            status: props.status,
        });

        successCallback(bookings.map(bookingToEvent));
    } catch (err) {
        error.value = err?.message ?? 'تعذر تحميل الحجوزات';
        failureCallback(err);
    } finally {
        loading.value = false;
    }
}

function refetch() {
    calendarRef.value?.getApi()?.refetchEvents();
}

watch(
    () => [props.search, props.status],
    () => {
        refetch();
    },
);

function onEventDidMount(info) {
    const booking = info.event.extendedProps.booking;
    const parts = [
        info.event.title,
        booking?.type_label,
        booking?.client?.name,
        booking?.status_label,
        booking?.dates_label,
    ].filter(Boolean);

    info.el.setAttribute('title', parts.join(' · '));
}

function onEventClick(info) {
    const booking = info.event.extendedProps.booking;

    if (!booking?.id) {
        return;
    }

    info.jsEvent.preventDefault();
    selectedBooking.value = booking;
    openModal(modalName);
}

function goToBookingDetail() {
    const id = selectedBooking.value?.id;

    if (!id) {
        return;
    }

    closeModal(modalName);
    router.push(`/bookings/${id}`);
}

function goToOrder() {
    const uuid = selectedBooking.value?.order_uuid;

    if (!uuid) {
        return;
    }

    closeModal(modalName);
    router.push(`/orders/${uuid}`);
}

const calendarOptions = {
    plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
    locale: arLocale,
    direction: 'rtl',
    initialView: 'dayGridMonth',
    height: 'auto',
    headerToolbar: {
        start: 'prev,next today',
        center: 'title',
        end: 'dayGridMonth,timeGridWeek',
    },
    buttonText: {
        today: 'اليوم',
        month: 'شهر',
        week: 'أسبوع',
    },
    firstDay: 6,
    navLinks: true,
    dayMaxEvents: 3,
    moreLinkText: (n) => `+${n} المزيد`,
    eventTimeFormat: {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false,
    },
    events: loadEvents,
    eventDidMount: onEventDidMount,
    eventClick: onEventClick,
};
</script>

<template>
    <div class="space-y-3 p-3 sm:p-4">
        <div class="flex flex-wrap items-center gap-3 text-xs text-gray-600">
            <span
                v-for="item in typeLegend"
                :key="item.type"
                class="inline-flex items-center gap-1.5"
            >
                <span class="h-2.5 w-2.5 rounded-sm" :class="item.swatch"></span>
                {{ item.label }}
            </span>
            <span v-if="loading" class="text-gray-400">جاري التحميل…</span>
        </div>

        <p v-if="error" class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
            {{ error }}
            <button type="button" class="ms-2 underline" @click="refetch">إعادة المحاولة</button>
        </p>

        <div class="bookings-calendar overflow-hidden rounded-xl border border-gray-200 bg-white">
            <FullCalendar ref="calendarRef" :options="calendarOptions" />
        </div>

        <Modal :name="modalName" title="تفاصيل الحجز" size="md">
            <div v-if="selectedBooking" class="space-y-4 p-4 sm:p-5">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h3 class="text-base font-bold text-gray-900">{{ selectedBooking.title }}</h3>
                        <Badge v-if="selectedBooking.type_label" color="blue">{{ selectedBooking.type_label }}</Badge>
                    </div>
                    <p class="mt-1 text-xs text-gray-400">حجز #{{ selectedBooking.id }}</p>
                </div>

                <dl class="divide-y divide-gray-100 rounded-xl border border-gray-100 bg-gray-50/60">
                    <div class="flex items-center justify-between gap-3 px-4 py-3 text-sm">
                        <dt class="text-gray-500">الحالة</dt>
                        <dd><Badge :color="selectedBooking.status_color">{{ selectedBooking.status_label }}</Badge></dd>
                    </div>
                    <div class="space-y-2 px-4 py-3 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-gray-500">العميل</dt>
                            <dd class="font-medium text-gray-800">{{ selectedBooking.client?.name ?? walkingClientLabel }}</dd>
                        </div>
                        <div v-if="selectedBooking.client?.phone || selectedBooking.client?.email" class="space-y-2 pt-1">
                            <div
                                v-if="selectedBooking.client?.phone"
                                class="flex items-center justify-between gap-2 rounded-lg bg-white px-3 py-2 ring-1 ring-gray-100"
                            >
                                <span class="truncate text-gray-700" dir="ltr">{{ selectedBooking.client.phone }}</span>
                                <div class="flex shrink-0 items-center gap-1">
                                    <a
                                        v-if="telHref(selectedBooking.client.phone)"
                                        :href="telHref(selectedBooking.client.phone)"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-primary-50 text-primary-700 hover:bg-primary-100"
                                        title="اتصال"
                                        aria-label="اتصال"
                                    >
                                        <Icon name="phone" class="h-4 w-4" />
                                    </a>
                                    <a
                                        v-if="whatsappHref(selectedBooking.client.phone)"
                                        :href="whatsappHref(selectedBooking.client.phone)"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-green-50 text-green-700 hover:bg-green-100"
                                        title="واتساب"
                                        aria-label="مراسلة واتساب"
                                    >
                                        <Icon name="brand-whatsapp" class="h-4 w-4" />
                                    </a>
                                </div>
                            </div>
                            <div
                                v-if="selectedBooking.client?.email"
                                class="flex items-center justify-between gap-2 rounded-lg bg-white px-3 py-2 ring-1 ring-gray-100"
                            >
                                <span class="truncate text-gray-700" dir="ltr">{{ selectedBooking.client.email }}</span>
                                <a
                                    v-if="mailtoHref(selectedBooking.client.email)"
                                    :href="mailtoHref(selectedBooking.client.email)"
                                    class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-gray-100 text-gray-700 hover:bg-gray-200"
                                    title="بريد"
                                    aria-label="إرسال بريد"
                                >
                                    <Icon name="mail" class="h-4 w-4" />
                                </a>
                            </div>
                        </div>
                    </div>
                    <div v-if="selectedBooking.calendar_name" class="flex items-center justify-between gap-3 px-4 py-3 text-sm">
                        <dt class="text-gray-500">التقويم</dt>
                        <dd class="text-end font-medium text-gray-800">
                            <span v-if="selectedBooking.calendar_type_label" class="text-gray-500">{{ selectedBooking.calendar_type_label }}: </span>
                            {{ selectedBooking.calendar_name }}
                        </dd>
                    </div>
                    <div v-if="selectedBooking.dates_label" class="flex items-center justify-between gap-3 px-4 py-3 text-sm">
                        <dt class="text-gray-500">الموعد</dt>
                        <dd class="text-end font-medium text-gray-800">{{ selectedBooking.dates_label }}</dd>
                    </div>
                    <div v-if="selectedBooking.price_formatted" class="flex items-center justify-between gap-3 px-4 py-3 text-sm">
                        <dt class="text-gray-500">السعر</dt>
                        <dd class="font-medium text-gray-800"><Money :formatted="selectedBooking.price_formatted" /></dd>
                    </div>
                    <div class="flex items-center justify-between gap-3 px-4 py-3 text-sm">
                        <dt class="text-gray-500">الطلب</dt>
                        <dd>
                            <Badge v-if="selectedBooking.order_number" color="green">#{{ selectedBooking.order_number }}</Badge>
                            <Badge v-else color="gray">بدون طلب</Badge>
                        </dd>
                    </div>
                </dl>

                <div class="flex flex-col gap-2 sm:flex-row sm:justify-end">
                    <Button
                        v-if="selectedBooking.order_uuid"
                        type="button"
                        variant="outline"
                        label="عرض الطلب"
                        @click="goToOrder"
                    />
                    <Button
                        type="button"
                        label="تفاصيل الحجز"
                        @click="goToBookingDetail"
                    />
                </div>
            </div>
        </Modal>
    </div>
</template>
