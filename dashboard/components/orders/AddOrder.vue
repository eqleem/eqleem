<script setup>
import { reactive, ref, computed, watch, onBeforeUnmount } from 'vue';
import { useRouter } from 'vue-router';
import Box from '../ui/Box.vue';
import Button from '../ui/Button.vue';
import Input from '../ui/Input.vue';
import Dropdown from '../Dropdown.vue';
import { ApiError } from '../../lib/api.js';
import { closeModal } from '../../lib/modal.js';
import { useClientsStore } from '../../stores/clients.js';
import { useOrdersStore } from '../../stores/orders.js';
import { useBookingsStore } from '../../stores/bookings.js';
import { addItemTypeOptions, itemTypeOptions, itemSearchPlaceholders, walkingClientLabel } from '../../data/orders.js';

const router = useRouter();
const clientsStore = useClientsStore();
const ordersStore = useOrdersStore();
const bookingsStore = useBookingsStore();

function isBookingType(type) {
    return type === 'service' || type === 'unit_rental';
}

function blankBookingState() {
    return {
        calendar_id: '',
        calendars: [],
        available_dates: [],
        booked_ranges: [],
        booking_date: '',
        check_in: '',
        check_out: '',
        time_slots: [],
        raw_time_slots: [],
        booking_start_at: null,
        booking_end_at: null,
        duration_minutes: 60,
        base_unit_price: 0,
        availabilityLoading: false,
        bookingError: null,
    };
}

const clientId = ref(null);
const isWalking = ref(true);
const selectedClient = ref(null);
const clientSearch = ref('');
const showClientResults = ref(false);
const clientResults = ref([]);
const clientSearching = ref(false);
let clientSearchTimer = null;

const formError = ref(null);
const submitting = ref(false);

watch(clientSearch, (value) => {
    if (clientId.value || isWalking.value) {
        return;
    }

    clearTimeout(clientSearchTimer);
    clientSearchTimer = setTimeout(async () => {
        const query = value.trim();
        if (!query) {
            clientResults.value = [];
            return;
        }

        clientSearching.value = true;
        try {
            clientResults.value = await clientsStore.searchClients(query);
        } catch {
            clientResults.value = [];
        } finally {
            clientSearching.value = false;
        }
    }, 250);
});

function selectClient(client) {
    clientId.value = client.id;
    isWalking.value = false;
    selectedClient.value = client;
    clientSearch.value = client.name;
    showClientResults.value = false;
    clientResults.value = [];
}

function selectWalking() {
    clientId.value = null;
    isWalking.value = true;
    selectedClient.value = null;
    clientSearch.value = '';
    showClientResults.value = false;
    clientResults.value = [];
}

function enterSearch() {
    clientId.value = null;
    isWalking.value = false;
    selectedClient.value = null;
    clientSearch.value = '';
    showClientResults.value = true;
}

const showCreate = ref(false);
const newClient = reactive({ name: '', phone: '', email: '' });
const newClientErrors = reactive({ name: null, phone: null, email: null });
const creatingClient = ref(false);

function openCreate() {
    newClient.name = clientSearch.value;
    newClient.phone = '';
    newClient.email = '';
    newClientErrors.name = null;
    newClientErrors.phone = null;
    newClientErrors.email = null;
    showCreate.value = true;
}

async function saveNewClient() {
    creatingClient.value = true;
    newClientErrors.name = null;
    newClientErrors.phone = null;
    newClientErrors.email = null;

    try {
        const { client } = await clientsStore.createClient({
            name: newClient.name.trim(),
            phone: String(newClient.phone).trim() || null,
            email: newClient.email.trim() || null,
        });

        selectClient(client);
        showCreate.value = false;
    } catch (error) {
        if (error instanceof ApiError) {
            newClientErrors.name = error.errors?.name?.[0] ?? null;
            newClientErrors.phone = error.errors?.phone?.[0] ?? null;
            newClientErrors.email = error.errors?.email?.[0] ?? null;
            formError.value = error.message;
        } else {
            formError.value = 'تعذر حفظ العميل.';
        }
    } finally {
        creatingClient.value = false;
    }
}

let itemKey = 0;
const items = reactive([]);
const contentTimers = {};

const qtyPresetOptions = [1, 2, 3, 4, 5];

function addItem(type) {
    items.push({
        key: (itemKey += 1),
        type,
        search: '',
        name: '',
        product_id: null,
        status: null,
        qty: 1,
        qtyCustom: false,
        unit_price: 0,
        results: [],
        showResults: false,
        searching: false,
        creating: false,
        ...blankBookingState(),
    });
}

function enableCustomQty(item) {
    item.qtyCustom = true;
    if (!Number(item.qty) || Number(item.qty) < 1) {
        item.qty = 1;
    }
}

function usePresetQty(item) {
    item.qtyCustom = false;
    const qty = Number(item.qty) || 1;
    item.qty = qtyPresetOptions.includes(qty) ? qty : 1;
}

function removeItem(index) {
    const item = items[index];
    if (item) {
        clearTimeout(contentTimers[item.key]);
        delete contentTimers[item.key];
    }
    items.splice(index, 1);
    refreshBookingLocks();
}

function onItemSearchInput(item) {
    item.product_id = null;
    item.name = '';
    item.status = null;
    resetItemBooking(item);

    if (item.type === 'other') {
        item.results = [];
        item.showResults = false;
        return;
    }

    clearTimeout(contentTimers[item.key]);
    contentTimers[item.key] = setTimeout(async () => {
        const query = String(item.search ?? '').trim();
        if (!query) {
            item.results = [];
            item.showResults = false;
            return;
        }

        item.searching = true;
        item.showResults = true;
        try {
            item.results = await ordersStore.searchContent(item.type, query);
        } catch {
            item.results = [];
        } finally {
            item.searching = false;
        }
    }, 250);
}

function resetItemBooking(item) {
    Object.assign(item, blankBookingState());
}

function rangesOverlap(startA, endA, startB, endB) {
    return new Date(String(startA).replace(' ', 'T')) < new Date(String(endB).replace(' ', 'T'))
        && new Date(String(endA).replace(' ', 'T')) > new Date(String(startB).replace(' ', 'T'));
}

function reservedSlotsForItem(item) {
    const calendarId = Number(item.calendar_id) || 0;

    if (!calendarId) {
        return [];
    }

    return items
        .filter((other) => other.key !== item.key
            && isBookingType(other.type)
            && Number(other.calendar_id) === calendarId
            && other.booking_start_at
            && other.booking_end_at)
        .map((other) => ({
            start_at: other.booking_start_at,
            end_at: other.booking_end_at,
        }));
}

function applyReservedSlots(item, slots) {
    const reserved = reservedSlotsForItem(item);

    return (Array.isArray(slots) ? slots : []).map((slot) => {
        if (!slot.available) {
            return slot;
        }

        const blocked = reserved.some((range) => rangesOverlap(
            slot.start_at,
            slot.end_at,
            range.start_at,
            range.end_at,
        ));

        if (!blocked) {
            return slot;
        }

        return {
            ...slot,
            available: false,
            unavailable_reason: 'reserved',
        };
    });
}

function allBookedRangesForItem(item) {
    return [
        ...(Array.isArray(item.booked_ranges) ? item.booked_ranges : []),
        ...reservedSlotsForItem(item),
    ];
}

function addDaysToDate(date, days) {
    const [year, month, day] = String(date).split('-').map(Number);
    const next = new Date(Date.UTC(year, month - 1, day + days));

    return next.toISOString().slice(0, 10);
}

function nightIsAvailable(item, date) {
    if (!item.available_dates.includes(date)) {
        return false;
    }

    const nightStart = `${date} 00:00:00`;
    const nightEnd = `${addDaysToDate(date, 1)} 00:00:00`;

    return !allBookedRangesForItem(item).some((range) => rangesOverlap(
        nightStart,
        nightEnd,
        range.start_at,
        range.end_at,
    ));
}

function rentalNights(item) {
    if (!item.check_in || !item.check_out || item.check_out <= item.check_in) {
        return 0;
    }

    let nights = 0;
    let cursor = item.check_in;

    while (cursor < item.check_out) {
        nights += 1;
        cursor = addDaysToDate(cursor, 1);
    }

    return nights;
}

function syncRentalPrice(item) {
    const nights = rentalNights(item);
    const perNight = Number(item.base_unit_price) || 0;
    item.unit_price = nights > 0 ? perNight * nights : perNight;
}

function validateRentalRange(item) {
    item.bookingError = null;
    item.booking_start_at = null;
    item.booking_end_at = null;

    if (!item.check_in || !item.check_out) {
        syncRentalPrice(item);
        return false;
    }

    if (item.check_out <= item.check_in) {
        item.bookingError = 'تاريخ المغادرة يجب أن يكون بعد الوصول.';
        syncRentalPrice(item);
        return false;
    }

    let cursor = item.check_in;

    while (cursor < item.check_out) {
        if (!nightIsAvailable(item, cursor)) {
            item.bookingError = 'التواريخ المحددة غير متاحة أو محجوزة مسبقاً.';
            syncRentalPrice(item);
            return false;
        }

        cursor = addDaysToDate(cursor, 1);
    }

    item.booking_start_at = `${item.check_in} 00:00:00`;
    item.booking_end_at = `${item.check_out} 00:00:00`;
    syncRentalPrice(item);

    return true;
}

async function selectContent(item, product) {
    item.product_id = product.product_id;
    item.name = product.name;
    item.search = product.name;
    item.unit_price = Number(product.unit_price) || 0;
    item.base_unit_price = Number(product.unit_price) || 0;
    item.status = product.status ?? null;
    item.duration_minutes = Number(product.duration_minutes) || 60;
    item.results = [];
    item.showResults = false;

    if (isBookingType(item.type)) {
        item.qty = 1;
        item.qtyCustom = false;
        item.calendar_id = '';
        item.calendars = [];
        item.available_dates = [];
        item.booked_ranges = [];
        item.booking_date = '';
        item.check_in = '';
        item.check_out = '';
        item.time_slots = [];
        item.raw_time_slots = [];
        item.booking_start_at = null;
        item.booking_end_at = null;
        item.bookingError = null;
        await loadItemAvailability(item);
    }
}

async function loadItemAvailability(item, { withDate = false } = {}) {
    if (!isBookingType(item.type) || !item.product_id) {
        return;
    }

    item.availabilityLoading = true;
    item.bookingError = null;

    try {
        const payload = await bookingsStore.fetchAvailability({
            contentId: item.product_id,
            calendarId: item.calendar_id || null,
            date: withDate && item.type === 'service' ? item.booking_date : null,
        });

        item.calendars = Array.isArray(payload?.calendars) ? payload.calendars : [];
        item.base_unit_price = Number(payload?.content?.unit_price ?? item.base_unit_price) || 0;
        item.duration_minutes = Number(payload?.content?.duration_minutes ?? item.duration_minutes) || 60;

        if (!item.calendar_id && item.calendars.length === 1) {
            item.calendar_id = String(item.calendars[0].id);
            await loadItemAvailability(item, { withDate: false });
            return;
        }

        item.available_dates = Array.isArray(payload?.available_dates) ? payload.available_dates : [];
        item.booked_ranges = Array.isArray(payload?.booked_ranges) ? payload.booked_ranges : [];

        if (item.type === 'service') {
            if (!item.unit_price || item.unit_price === 0) {
                item.unit_price = item.base_unit_price;
            }

            if (withDate) {
                item.raw_time_slots = Array.isArray(payload?.time_slots) ? payload.time_slots : [];
                item.time_slots = applyReservedSlots(item, item.raw_time_slots);
            } else {
                item.raw_time_slots = [];
                item.time_slots = [];
                item.booking_date = '';
                item.booking_start_at = null;
                item.booking_end_at = null;
            }
        } else {
            if (item.check_in || item.check_out) {
                validateRentalRange(item);
            } else {
                item.unit_price = item.base_unit_price;
            }
        }

        refreshBookingLocks(item);
    } catch (error) {
        if (error instanceof ApiError) {
            item.bookingError = Object.values(error.errors || {}).flat()[0] || error.message;
        } else {
            item.bookingError = 'تعذر تحميل توفر الحجز.';
        }
    } finally {
        item.availabilityLoading = false;
    }
}

async function onItemCalendarChange(item) {
    item.booking_date = '';
    item.check_in = '';
    item.check_out = '';
    item.time_slots = [];
    item.booking_start_at = null;
    item.booking_end_at = null;
    item.bookingError = null;
    await loadItemAvailability(item);
}

async function onItemBookingDateChange(item) {
    item.booking_start_at = null;
    item.booking_end_at = null;
    item.time_slots = [];

    if (!item.booking_date || !item.calendar_id) {
        return;
    }

    await loadItemAvailability(item, { withDate: true });
}

function selectItemTimeSlot(item, slot) {
    if (!slot?.available) {
        return;
    }

    item.booking_start_at = slot.start_at;
    item.booking_end_at = slot.end_at;
    refreshBookingLocks(item);
}

function onRentalRangeChange(item) {
    validateRentalRange(item);
    refreshBookingLocks(item);
}

function refreshBookingLocks(sourceItem = null) {
    for (const other of items) {
        if (sourceItem && other.key === sourceItem.key) {
            continue;
        }

        if (other.type === 'service' && Array.isArray(other.raw_time_slots) && other.raw_time_slots.length > 0) {
            other.time_slots = applyReservedSlots(other, other.raw_time_slots);

            if (
                other.booking_start_at
                && !other.time_slots.some((slot) => slot.available
                    && slot.start_at === other.booking_start_at
                    && slot.end_at === other.booking_end_at)
            ) {
                other.booking_start_at = null;
                other.booking_end_at = null;
            }
        }

        if (other.type === 'unit_rental' && (other.check_in || other.check_out)) {
            validateRentalRange(other);
        }
    }
}

function formatDateLabel(date) {
    try {
        return new Intl.DateTimeFormat('ar-SA', {
            weekday: 'long',
            day: 'numeric',
            month: 'long',
            year: 'numeric',
        }).format(new Date(`${date}T12:00:00`));
    } catch {
        return date;
    }
}

function selectableCheckInDates(item) {
    return item.available_dates.filter((date) => nightIsAvailable(item, date));
}

function selectableCheckOutDates(item) {
    if (!item.check_in) {
        return [];
    }

    return item.available_dates.filter((date) => {
        if (date <= item.check_in) {
            return false;
        }

    const cursorStart = item.check_in;
    let cursor = item.check_in;
    const checkout = date;

    while (cursor < checkout) {
        if (!nightIsAvailable(item, cursor)) {
            return false;
        }

        cursor = addDaysToDate(cursor, 1);
    }

    return cursorStart < checkout;
    });
}

async function useAsNewContent(item) {
    const title = String(item.search ?? '').trim();

    if (!title || item.type === 'other' || item.creating) {
        return;
    }

    item.creating = true;
    formError.value = null;

    try {
        const content = await ordersStore.createDraftContent({
            type: item.type,
            title,
            unit_price: Number(item.unit_price) || 0,
        });

        selectContent(item, content);
        item.status = content.status ?? 'draft';
    } catch (error) {
        if (error instanceof ApiError) {
            formError.value = Object.values(error.errors || {}).flat()[0] || error.message || 'تعذر إنشاء العنصر.';
        } else {
            formError.value = 'تعذر إنشاء العنصر.';
        }
    } finally {
        item.creating = false;
    }
}

async function syncDraftContentPrice(item) {
    if (item.type === 'other' || item.status !== 'draft' || !item.product_id || !item.name) {
        return;
    }

    const unitPrice = Number(item.unit_price) || 0;

    if (unitPrice <= 0) {
        return;
    }

    try {
        await ordersStore.createDraftContent({
            type: item.type,
            title: String(item.name).trim(),
            unit_price: unitPrice,
        });
    } catch {
        // Price sync is best-effort; order submit still uses the line price.
    }
}

function onDraftUnitPriceChange(item) {
    clearTimeout(contentTimers[`price-${item.key}`]);
    contentTimers[`price-${item.key}`] = setTimeout(() => {
        syncDraftContentPrice(item);
    }, 400);
}

function clearSelectedContent(item) {
    item.product_id = null;
    item.name = '';
    item.status = null;
    item.search = '';
    item.results = [];
    item.showResults = false;
    resetItemBooking(item);
    refreshBookingLocks();
}

function lineTotal(item) {
    return Math.max(0, (Number(item.qty) || 0) * (Number(item.unit_price) || 0));
}

const totals = computed(() => {
    const subtotal = items.reduce((sum, item) => sum + (Number(item.qty) || 0) * (Number(item.unit_price) || 0), 0);
    const tax = 0;
    return { subtotal, tax, grand: Math.max(0, subtotal + tax) };
});

function resetForm() {
    items.splice(0);
    selectWalking();
    formError.value = null;
}

async function submit() {
    formError.value = null;

    if (items.length === 0) {
        formError.value = 'أضف عنصراً واحداً على الأقل.';
        return;
    }

    for (const item of items) {
        if (item.type === 'other') {
            if (!String(item.name ?? item.search ?? '').trim()) {
                formError.value = 'أكمل وصف العنصر المخصص قبل التأكيد.';
                return;
            }
        } else if (!item.product_id || !String(item.name ?? '').trim()) {
            formError.value = 'اختر عنصراً من النتائج أو أضفه كعنصر جديد قبل التأكيد.';
            return;
        }

        if (isBookingType(item.type)) {
            if (!item.calendar_id) {
                formError.value = `اختر التقويم لـ «${item.name}» قبل التأكيد.`;
                return;
            }

            if (!item.booking_start_at || !item.booking_end_at) {
                formError.value = item.type === 'unit_rental'
                    ? `حدد فترة التأجير لـ «${item.name}» قبل التأكيد.`
                    : `حدد اليوم والوقت لـ «${item.name}» قبل التأكيد.`;
                return;
            }

            if (item.bookingError) {
                formError.value = item.bookingError;
                return;
            }
        }
    }

    submitting.value = true;

    try {
        await Promise.all(
            items
                .filter((item) => item.status === 'draft' && item.product_id && !isBookingType(item.type))
                .map((item) => syncDraftContentPrice(item)),
        );

        const { order } = await ordersStore.createOrder({
            client_id: isWalking.value ? null : clientId.value,
            items: items.map((item) => {
                const name = item.type === 'other'
                    ? String(item.name || item.search).trim()
                    : String(item.name).trim();

                const payload = {
                    type: item.type,
                    name,
                    product_id: item.product_id,
                    qty: isBookingType(item.type) ? 1 : Math.max(1, Number(item.qty) || 1),
                    unit_price: Number(item.unit_price) || 0,
                    discount: 0,
                    description: item.type === 'other' ? name : null,
                };

                if (isBookingType(item.type)) {
                    payload.calendar_id = Number(item.calendar_id);
                    payload.booking_start_at = item.booking_start_at;
                    payload.booking_end_at = item.booking_end_at;
                }

                return payload;
            }),
        });

        resetForm();
        closeModal('add-order');
        await router.push(`/orders/${order.uuid}`);
    } catch (error) {
        if (error instanceof ApiError) {
            const firstFieldError = Object.values(error.errors || {}).flat()[0];
            formError.value = firstFieldError || error.message || 'تعذر إنشاء الطلب.';
        } else {
            formError.value = 'تعذر إنشاء الطلب.';
        }
    } finally {
        submitting.value = false;
    }
}

onBeforeUnmount(() => {
    clearTimeout(clientSearchTimer);
    Object.values(contentTimers).forEach(clearTimeout);
});
</script>

<template>
    <div class="flex max-h-[75vh] flex-col">
        <div class="shrink-0 space-y-4 p-5 pb-0">
                <p v-if="formError" class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-600">{{ formError }}</p>

                <!-- Client -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-semibold text-gray-500">العميل</p>
                        <Button type="button" variant="outline" label="عميل جديد" @click="openCreate">
                            <template #icon>
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" d="M12 5v14M5 12h14" />
                                </svg>
                            </template>
                        </Button>
                    </div>

                    <div v-if="clientId" class="flex items-center justify-between rounded-lg bg-gray-50 p-3">
                        <div>
                            <p class="text-sm font-bold text-gray-800">{{ selectedClient?.name }}</p>
                            <p v-if="selectedClient?.email" class="mt-1 text-xs text-gray-500">{{ selectedClient.email }}</p>
                            <p v-if="selectedClient?.phone" class="mt-0.5 text-xs text-gray-500" dir="ltr">{{ selectedClient.phone }}</p>
                        </div>
                        <button type="button" class="rounded px-2 py-1 text-xs text-red-500 hover:bg-red-50 hover:text-red-700" @click="enterSearch">تغيير</button>
                    </div>

                    <div v-else-if="isWalking" class="flex items-center justify-between rounded-lg bg-gray-50 p-3">
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-white text-gray-400 ring-1 ring-gray-100">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <circle cx="12" cy="8" r="4" />
                                    <path d="M4 20c0-4 4-6 8-6s8 2 8 6" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-800">{{ walkingClientLabel }}</p>
                                <p class="mt-0.5 text-xs text-gray-500">الافتراضي — بدون حساب عميل</p>
                            </div>
                        </div>
                        <button type="button" class="rounded px-2 py-1 text-xs text-red-500 hover:bg-red-50 hover:text-red-700" @click="enterSearch">تغيير</button>
                    </div>

                    <div v-else class="relative">
                        <div class="relative flex-1">
                            <div class="pointer-events-none absolute bottom-0 right-0 top-0 flex items-center ps-2 text-gray-500">
                                <svg class="h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <circle cx="11" cy="11" r="7" />
                                    <path stroke-linecap="round" d="m20 20-3-3" />
                                </svg>
                            </div>
                            <input
                                v-model="clientSearch"
                                type="text"
                                placeholder="ابحث بالاسم أو البريد أو الهاتف .."
                                class="block w-full rounded-lg border border-gray-200 py-2 ps-10 text-gray-800 placeholder:text-gray-400 focus:border-primary-500 focus:outline-none sm:text-sm"
                                @focus="showClientResults = true"
                            >
                        </div>

                        <div v-if="showClientResults" class="absolute z-50 mt-1 max-h-52 w-full overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg">
                            <button type="button" class="w-full border-b border-gray-100 bg-gray-50/50 px-3 py-2.5 text-start hover:bg-gray-50" @click="selectWalking">
                                <p class="text-sm font-semibold text-gray-800">{{ walkingClientLabel }}</p>
                                <p class="mt-0.5 text-xs text-gray-500">بدون حساب عميل</p>
                            </button>
                            <p v-if="clientSearching" class="px-3 py-2 text-xs text-gray-400">جاري البحث…</p>
                            <button
                                v-for="client in clientResults"
                                :key="client.id"
                                type="button"
                                class="w-full border-b border-gray-50 px-3 py-2 text-start last:border-0 hover:bg-gray-50"
                                @click="selectClient(client)"
                            >
                                <p class="text-sm font-semibold text-gray-800">{{ client.name }}</p>
                                <p class="mt-0.5 text-xs text-gray-500">
                                    <span v-if="client.email">{{ client.email }}</span>
                                    <span v-if="client.phone" class="ms-2" dir="ltr">{{ client.phone }}</span>
                                </p>
                            </button>
                            <button
                                v-if="clientSearch !== '' && !clientSearching && clientResults.length === 0"
                                type="button"
                                class="w-full border-t border-gray-100 px-3 py-2.5 text-start text-sm text-primary-600 hover:bg-primary-50"
                                @click="openCreate"
                            >
                                <span class="font-semibold">إضافة "{{ clientSearch }}"</span>
                                <span class="ms-1 text-xs text-primary-500/80">كعميل جديد</span>
                            </button>
                        </div>
                    </div>
                </div>
        </div>

        <div class="relative z-30 min-h-0 flex-1 overflow-visible px-5 py-4">
                <!-- Items -->
                <Box title="العناصر" class="relative z-30 border border-gray-100 shadow-sm">
                    <template #action>
                        <Dropdown width="min-w-52 w-52" placement="top">
                            <template #trigger>
                                <button type="button" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg>
                                    <span>إضافة عنصر</span>
                                    <svg class="h-4 w-4 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" /></svg>
                                </button>
                            </template>
                            <button
                                v-for="(label, type) in addItemTypeOptions"
                                :key="type"
                                type="button"
                                class="flex w-full items-center gap-2.5 rounded-md px-3 py-2 text-start text-sm text-gray-700 hover:bg-gray-50"
                                @click="addItem(type)"
                            >
                                {{ label }}
                            </button>
                        </Dropdown>
                    </template>

                    <div class="max-h-[40vh] space-y-4 overflow-y-auto overflow-x-visible p-4">
                        <p v-if="items.length === 0" class="py-6 text-center text-sm text-gray-400">اختر نوع العنصر من القائمة لبدء إضافة الطلب.</p>

                        <div
                            v-for="(item, index) in items"
                            :key="item.key"
                            class="relative space-y-3 rounded-lg border border-gray-100 bg-gray-50 p-3"
                        >
                            <button type="button" class="absolute left-2 top-2 rounded p-1 text-red-400 hover:bg-red-50 hover:text-red-600" @click="removeItem(index)">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M10 11v6M14 11v6M6 7l1 13a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2l1-13" /></svg>
                            </button>

                            <p class="pe-8 text-xs font-semibold text-gray-500">{{ itemTypeOptions[item.type] ?? item.type }}</p>

                            <div class="relative">
                                <textarea
                                    v-if="item.type === 'other'"
                                    v-model="item.name"
                                    rows="2"
                                    :placeholder="itemSearchPlaceholders.other"
                                    class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-primary-500 focus:outline-none"
                                ></textarea>
                                <template v-else>
                                    <input
                                        v-model="item.search"
                                        type="text"
                                        :placeholder="itemSearchPlaceholders[item.type] ?? 'ابحث أو أدخل الاسم ..'"
                                        class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-800 focus:border-primary-500 focus:outline-none"
                                        @input="onItemSearchInput(item)"
                                        @focus="item.showResults = !item.name && String(item.search || '').trim() !== ''"
                                    >

                                    <div
                                        v-if="!item.name && item.showResults && String(item.search || '').trim() !== ''"
                                        class="absolute z-40 mt-1 max-h-48 w-full overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg"
                                    >
                                        <p v-if="item.searching" class="px-3 py-2 text-xs text-gray-400">جاري البحث…</p>
                                        <button
                                            v-for="product in item.results"
                                            :key="`${product.product_id}-${product.name}`"
                                            type="button"
                                            class="w-full border-b border-gray-50 px-3 py-2 text-start last:border-0 hover:bg-gray-50"
                                            @click="selectContent(item, product)"
                                        >
                                            <p class="text-sm font-semibold text-gray-800">{{ product.name }}</p>
                                            <p class="mt-0.5 text-xs text-gray-500"><Money :amount="product.unit_price" /></p>
                                        </button>
                                        <button
                                            type="button"
                                            class="w-full border-t border-gray-100 px-3 py-2.5 text-start text-sm text-primary-600 hover:bg-primary-50 disabled:opacity-60"
                                            :disabled="item.creating"
                                            @click="useAsNewContent(item)"
                                        >
                                            <span class="font-semibold">
                                                {{ item.creating ? 'جاري الإضافة…' : `استخدام "${item.search.trim()}" كعنصر جديد` }}
                                            </span>
                                            <span class="ms-1 text-xs text-primary-500/80">{{ itemTypeOptions[item.type] }} — مسودة</span>
                                        </button>
                                    </div>

                                    <div v-if="item.name" class="mt-1 flex items-center justify-between gap-2">
                                        <p class="text-xs text-green-600">
                                            المحدد: {{ item.name }}
                                            <span v-if="item.status === 'draft'" class="ms-1 rounded bg-amber-50 px-1.5 py-0.5 text-[10px] font-medium text-amber-700">مسودة</span>
                                        </p>
                                        <button type="button" class="text-xs text-red-500 hover:text-red-700" @click="clearSelectedContent(item)">تغيير</button>
                                    </div>
                                </template>
                            </div>

                            <div
                                v-if="isBookingType(item.type) && item.name"
                                class="space-y-3 rounded-lg border border-dashed border-primary-100 bg-white p-3"
                            >
                                <p v-if="item.availabilityLoading" class="text-xs text-gray-400">جاري تحميل التوفر…</p>
                                <p v-else-if="item.calendars.length === 0" class="text-xs text-amber-600">
                                    لا يوجد تقويم / مخزون مرتبط بهذا العنصر.
                                </p>

                                <div v-if="item.calendars.length > 0">
                                    <label class="mb-1 block text-xs text-gray-500">
                                        {{ item.type === 'unit_rental' ? 'مخزون الوحدات / التقويم' : 'التقويم' }}
                                    </label>
                                    <select
                                        v-model="item.calendar_id"
                                        class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
                                        @change="onItemCalendarChange(item)"
                                    >
                                        <option value="">اختر التقويم ..</option>
                                        <option v-for="calendar in item.calendars" :key="calendar.id" :value="String(calendar.id)">
                                            {{ calendar.name }}
                                        </option>
                                    </select>
                                </div>

                                <template v-if="item.type === 'service' && item.calendar_id">
                                    <div v-if="item.available_dates.length > 0">
                                        <label class="mb-1 block text-xs text-gray-500">اليوم</label>
                                        <select
                                            v-model="item.booking_date"
                                            class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
                                            @change="onItemBookingDateChange(item)"
                                        >
                                            <option value="">اختر التاريخ ..</option>
                                            <option v-for="date in item.available_dates" :key="date" :value="date">
                                                {{ formatDateLabel(date) }}
                                            </option>
                                        </select>
                                    </div>
                                    <p v-else-if="!item.availabilityLoading" class="text-xs text-amber-600">لا توجد تواريخ متاحة في التقويم المحدد.</p>

                                    <div v-if="item.booking_date && item.time_slots.length > 0">
                                        <label class="mb-1 block text-xs text-gray-500">وقت الحجز</label>
                                        <div class="flex flex-wrap gap-2">
                                            <button
                                                v-for="slot in item.time_slots"
                                                :key="`${slot.start_at}-${slot.end_at}`"
                                                type="button"
                                                class="rounded-lg border px-3 py-1.5 text-sm transition"
                                                :class="slot.available
                                                    ? (item.booking_start_at === slot.start_at
                                                        ? 'border-primary-500 bg-primary-50 font-semibold text-primary-700'
                                                        : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50')
                                                    : 'cursor-not-allowed select-none border-gray-100 bg-gray-50 text-gray-300 line-through'"
                                                :disabled="!slot.available"
                                                dir="ltr"
                                                @click="selectItemTimeSlot(item, slot)"
                                            >
                                                {{ slot.label }}
                                            </button>
                                        </div>
                                    </div>
                                    <p v-else-if="item.booking_date && !item.availabilityLoading" class="text-xs text-amber-600">لا توجد فترات متاحة في هذا اليوم.</p>
                                </template>

                                <template v-else-if="item.type === 'unit_rental' && item.calendar_id">
                                    <div v-if="selectableCheckInDates(item).length > 0" class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                        <div>
                                            <label class="mb-1 block text-xs text-gray-500">من تاريخ (وصول)</label>
                                            <select
                                                v-model="item.check_in"
                                                class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
                                                @change="item.check_out = ''; onRentalRangeChange(item)"
                                            >
                                                <option value="">اختر الوصول ..</option>
                                                <option v-for="date in selectableCheckInDates(item)" :key="`in-${date}`" :value="date">
                                                    {{ formatDateLabel(date) }}
                                                </option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-xs text-gray-500">إلى تاريخ (مغادرة)</label>
                                            <select
                                                v-model="item.check_out"
                                                class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
                                                :disabled="!item.check_in"
                                                @change="onRentalRangeChange(item)"
                                            >
                                                <option value="">اختر المغادرة ..</option>
                                                <option v-for="date in selectableCheckOutDates(item)" :key="`out-${date}`" :value="date">
                                                    {{ formatDateLabel(date) }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                    <p v-else-if="!item.availabilityLoading" class="text-xs text-amber-600">لا توجد ليالٍ متاحة في هذا المخزون.</p>
                                    <p v-if="rentalNights(item) > 0" class="text-xs text-gray-500">
                                        {{ rentalNights(item) }} ليلة · السعر لليلة <Money :amount="item.base_unit_price" class="inline-flex" />
                                    </p>
                                </template>

                                <p v-if="item.bookingError" class="text-xs text-red-600">{{ item.bookingError }}</p>
                            </div>

                            <div class="grid grid-cols-1 gap-3" :class="isBookingType(item.type) ? 'sm:grid-cols-2' : 'sm:grid-cols-3'">
                                <div>
                                    <label class="mb-1 block text-xs text-gray-500">
                                        {{ item.type === 'unit_rental' ? 'سعر الإقامة' : 'سعر الوحدة' }}
                                    </label>
                                    <div class="relative">
                                        <input
                                            v-model.number="item.unit_price"
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            dir="ltr"
                                            class="block w-full rounded-lg border border-gray-200 py-2 pe-9 ps-3 text-sm focus:border-primary-500 focus:outline-none"
                                            @change="onDraftUnitPriceChange(item)"
                                            @blur="onDraftUnitPriceChange(item)"
                                        >
                                        <span class="pointer-events-none absolute inset-y-0 end-0 flex items-center pe-3 text-gray-400" aria-hidden="true">
                                            <span class="money-symbol icon-saudi_riyal_new"></span>
                                        </span>
                                    </div>
                                </div>
                                <div v-if="!isBookingType(item.type)">
                                    <label class="mb-1 block text-xs text-gray-500">الكمية</label>
                                    <div v-if="!item.qtyCustom" class="flex gap-2">
                                        <select
                                            v-model.number="item.qty"
                                            dir="ltr"
                                            class="block min-w-0 flex-1 rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
                                        >
                                            <option v-for="n in qtyPresetOptions" :key="n" :value="n">{{ n }}</option>
                                        </select>
                                        <button
                                            type="button"
                                            class="inline-flex shrink-0 items-center justify-center rounded-lg border border-gray-200 bg-white px-2 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50"
                                            @click="enableCustomQty(item)"
                                        >
                                            <iconify-icon icon="solar:add-circle-bold" class="text-2xl"></iconify-icon>
                                        </button>
                                    </div>
                                    <div v-else class="flex gap-2">
                                        <input
                                            v-model.number="item.qty"
                                            type="number"
                                            min="1"
                                            dir="ltr"
                                            class="block min-w-0 flex-1 rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
                                        >
                                        <button
                                            type="button"
                                            class="shrink-0 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                                            title="العودة للقائمة"
                                            @click="usePresetQty(item)"
                                        >
                                            1–5
                                        </button>
                                    </div>
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs text-gray-500">الإجمالي</label>
                                    <div class="rounded-lg border border-gray-100 bg-white px-3 py-2 text-sm font-bold text-gray-800"><Money :amount="lineTotal(item)" /></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </Box>
        </div>

        <div class="relative z-10 shrink-0 border-t-2 border-gray-100 bg-white">
            <div class="space-y-2 px-5 py-4">
                <div class="ms-auto max-w-sm space-y-2">
                    <div class="flex items-center justify-between text-sm text-gray-600">
                        <span>المجموع الفرعي</span>
                        <span class="font-semibold text-gray-800"><Money :amount="totals.subtotal" /></span>
                    </div>
                    <div class="flex items-center justify-between text-sm text-gray-600">
                        <span>الضريبة</span>
                        <span class="font-semibold text-gray-800"><Money :amount="totals.tax" /></span>
                    </div>
                    <div class="flex items-center justify-between border-t border-gray-100 pt-2 text-base font-bold text-gray-800">
                        <span>الإجمالي النهائي</span>
                        <span><Money :amount="totals.grand" /></span>
                    </div>
                </div>
            </div>
            <div class="flex justify-end border-t border-gray-100 px-5 py-4 shadow">
                <Button type="button" label="أنشئ الطلب" :loading="submitting" @click="submit" />
            </div>
        </div>

        <Teleport to="body">
            <div v-if="showCreate" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
                <div class="fixed inset-0 bg-gray-900/60" @click="showCreate = false"></div>
                <div class="relative w-full max-w-lg rounded-xl bg-white shadow-2xl ring-1 ring-black/5">
                    <div class="flex items-center justify-between border-b border-gray-100 p-3">
                        <p class="px-1 text-sm font-semibold text-gray-600">إضافة عميل جديد</p>
                        <button type="button" class="rounded-md bg-gray-100 p-1 text-gray-400 hover:bg-gray-200" @click="showCreate = false">
                            <span class="sr-only">Close</span>
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                    <div class="space-y-4 p-5">
                        <Input v-model="newClient.name" name="newClientName" label="الاسم" placeholder="الاسم" :error="newClientErrors.name" />
                        <Input v-model="newClient.phone" name="newClientPhone" type="number" label="رقم الجوال" placeholder="123456789" dir="ltr" :error="newClientErrors.phone" />
                        <Input v-model="newClient.email" name="newClientEmail" type="email" label="البريد الإلكتروني" placeholder="client@email.com" dir="ltr" :error="newClientErrors.email" />
                        <div class="flex justify-end gap-2 border-t border-gray-100 pt-4">
                            <Button type="button" variant="outline" label="إلغاء" @click="showCreate = false" />
                            <Button type="button" label="حفظ" :loading="creatingClient" @click="saveNewClient" />
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>
