<script setup>
import { computed, onBeforeUnmount, reactive, ref, watch } from 'vue';
import Box from '../ui/Box.vue';
import Button from '../ui/Button.vue';
import Input from '../ui/Input.vue';
import { ApiError } from '../../lib/api.js';
import { closeModal } from '../../lib/modal.js';
import { useClientsStore } from '../../stores/clients.js';
import { useBookingsStore } from '../../stores/bookings.js';
import {
    bookingTypeOptions,
    bookingTypeSearchPlaceholders,
    bookingStatusOptions,
    walkingClientLabel,
} from '../../data/bookings.js';

const clientsStore = useClientsStore();
const bookingsStore = useBookingsStore();

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

const type = ref('service');
const contentSearch = ref('');
const contentResults = ref([]);
const showContentResults = ref(false);
const contentSearching = ref(false);
let contentSearchTimer = null;

const selectedContent = ref(null);
const calendars = ref([]);
const calendarId = ref('');
const availableDates = ref([]);
const bookingDate = ref('');
const timeSlots = ref([]);
const startAt = ref(null);
const endAt = ref(null);
const status = ref('confirmed');
const availabilityLoading = ref(false);
const unitPrice = ref(0);

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

function resetBookingSelection({ keepType = true } = {}) {
    if (!keepType) {
        type.value = 'service';
    }

    selectedContent.value = null;
    contentSearch.value = '';
    contentResults.value = [];
    showContentResults.value = false;
    calendars.value = [];
    calendarId.value = '';
    availableDates.value = [];
    bookingDate.value = '';
    timeSlots.value = [];
    startAt.value = null;
    endAt.value = null;
    unitPrice.value = 0;
}

watch(type, () => {
    resetBookingSelection({ keepType: true });
});

watch(contentSearch, (value) => {
    if (selectedContent.value) {
        return;
    }

    clearTimeout(contentSearchTimer);
    contentSearchTimer = setTimeout(async () => {
        const query = value.trim();
        if (!query) {
            contentResults.value = [];
            showContentResults.value = false;
            return;
        }

        contentSearching.value = true;
        showContentResults.value = true;

        try {
            contentResults.value = await bookingsStore.searchContent(type.value, query);
        } catch {
            contentResults.value = [];
        } finally {
            contentSearching.value = false;
        }
    }, 250);
});

async function selectContent(product) {
    selectedContent.value = product;
    contentSearch.value = product.name;
    contentResults.value = [];
    showContentResults.value = false;
    unitPrice.value = Number(product.unit_price) || 0;
    calendarId.value = '';
    availableDates.value = [];
    bookingDate.value = '';
    timeSlots.value = [];
    startAt.value = null;
    endAt.value = null;

    await loadAvailability();
}

async function loadAvailability({ withDate = false } = {}) {
    if (!selectedContent.value?.product_id) {
        return;
    }

    availabilityLoading.value = true;
    formError.value = null;

    try {
        const payload = await bookingsStore.fetchAvailability({
            contentId: selectedContent.value.product_id,
            calendarId: calendarId.value,
            date: withDate ? bookingDate.value : null,
        });

        calendars.value = Array.isArray(payload?.calendars) ? payload.calendars : [];
        unitPrice.value = Number(payload?.content?.unit_price ?? unitPrice.value) || 0;

        if (!calendarId.value && calendars.value.length === 1) {
            calendarId.value = String(calendars.value[0].id);
            await loadAvailability({ withDate: false });
            return;
        }

        availableDates.value = Array.isArray(payload?.available_dates) ? payload.available_dates : [];

        if (withDate) {
            timeSlots.value = Array.isArray(payload?.time_slots) ? payload.time_slots : [];
        } else {
            timeSlots.value = [];
            bookingDate.value = '';
            startAt.value = null;
            endAt.value = null;
        }
    } catch (error) {
        if (error instanceof ApiError) {
            formError.value = Object.values(error.errors || {}).flat()[0] || error.message;
        } else {
            formError.value = 'تعذر تحميل توفر الحجز.';
        }
    } finally {
        availabilityLoading.value = false;
    }
}

async function onCalendarChange() {
    bookingDate.value = '';
    timeSlots.value = [];
    startAt.value = null;
    endAt.value = null;
    await loadAvailability();
}

async function onDateChange() {
    startAt.value = null;
    endAt.value = null;
    timeSlots.value = [];

    if (!bookingDate.value || !calendarId.value) {
        return;
    }

    await loadAvailability({ withDate: true });
}

function selectTimeSlot(slot) {
    if (!slot?.available) {
        return;
    }

    startAt.value = slot.start_at;
    endAt.value = slot.end_at;
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

const canSubmit = computed(() => (
    selectedContent.value?.product_id
    && calendarId.value
    && startAt.value
    && endAt.value
    && !submitting.value
));

function resetForm() {
    selectWalking();
    resetBookingSelection({ keepType: false });
    status.value = 'confirmed';
    formError.value = null;
    showCreate.value = false;
}

async function submit() {
    formError.value = null;

    if (!canSubmit.value) {
        formError.value = 'أكمل بيانات الحجز قبل التأكيد.';
        return;
    }

    submitting.value = true;

    try {
        await bookingsStore.createBooking({
            client_id: isWalking.value ? null : clientId.value,
            type: type.value,
            content_id: selectedContent.value.product_id,
            calendar_id: calendarId.value,
            start_at: startAt.value,
            end_at: endAt.value,
            status: status.value,
        });

        resetForm();
        closeModal('add-booking');
    } catch (error) {
        if (error instanceof ApiError) {
            const firstFieldError = Object.values(error.errors || {}).flat()[0];
            formError.value = firstFieldError || error.message || 'تعذر إنشاء الحجز.';
        } else {
            formError.value = 'تعذر إنشاء الحجز.';
        }
    } finally {
        submitting.value = false;
    }
}

onBeforeUnmount(() => {
    clearTimeout(clientSearchTimer);
    clearTimeout(contentSearchTimer);
});
</script>

<template>
    <div class="flex max-h-[75vh] flex-col">
        <div class="min-h-0 flex-1 space-y-4 overflow-y-auto p-5">
            <p v-if="formError" class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-600">{{ formError }}</p>

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
                    <div>
                        <p class="text-sm font-bold text-gray-800">{{ walkingClientLabel }}</p>
                        <p class="mt-0.5 text-xs text-gray-500">الافتراضي — بدون حساب عميل</p>
                    </div>
                    <button type="button" class="rounded px-2 py-1 text-xs text-red-500 hover:bg-red-50 hover:text-red-700" @click="enterSearch">تغيير</button>
                </div>

                <div v-else class="relative">
                    <input
                        v-model="clientSearch"
                        type="text"
                        placeholder="ابحث بالاسم أو البريد أو الهاتف .."
                        class="block w-full rounded-lg border border-gray-200 py-2 px-3 text-sm text-gray-800 placeholder:text-gray-400 focus:border-primary-500 focus:outline-none"
                        @focus="showClientResults = true"
                    >
                    <div v-if="showClientResults" class="absolute z-50 mt-1 max-h-52 w-full overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg">
                        <button type="button" class="w-full border-b border-gray-100 bg-gray-50/50 px-3 py-2.5 text-start hover:bg-gray-50" @click="selectWalking">
                            <p class="text-sm font-semibold text-gray-800">{{ walkingClientLabel }}</p>
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
                        </button>
                    </div>
                </div>

                <div v-if="showCreate" class="space-y-3 rounded-lg border border-gray-200 bg-white p-3">
                    <Input v-model="newClient.name" label="الاسم" :error="newClientErrors.name" />
                    <Input v-model="newClient.phone" label="الهاتف" dir="ltr" :error="newClientErrors.phone" />
                    <Input v-model="newClient.email" label="البريد" dir="ltr" :error="newClientErrors.email" />
                    <div class="flex justify-end gap-2">
                        <Button type="button" variant="outline" label="إلغاء" @click="showCreate = false" />
                        <Button type="button" label="حفظ العميل" :disabled="creatingClient" @click="saveNewClient" />
                    </div>
                </div>
            </div>

            <Box title="تفاصيل الحجز" class="border border-gray-100 shadow-sm">
                <div class="space-y-4 p-1">
                    <div>
                        <label class="mb-1 block text-xs text-gray-500">نوع الحجز</label>
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="(label, key) in bookingTypeOptions"
                                :key="key"
                                type="button"
                                class="rounded-lg border px-3 py-1.5 text-sm transition"
                                :class="type === key
                                    ? 'border-primary-500 bg-primary-50 font-semibold text-primary-700'
                                    : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50'"
                                @click="type = key"
                            >
                                {{ label }}
                            </button>
                        </div>
                    </div>

                    <div class="relative">
                        <label class="mb-1 block text-xs text-gray-500">{{ type === 'service' ? 'الخدمة' : 'وحدة التأجير' }}</label>
                        <input
                            v-model="contentSearch"
                            type="text"
                            :placeholder="bookingTypeSearchPlaceholders[type]"
                            class="block w-full rounded-lg border border-gray-200 py-2 px-3 text-sm text-gray-800 placeholder:text-gray-400 focus:border-primary-500 focus:outline-none"
                            @focus="showContentResults = contentResults.length > 0"
                            @input="selectedContent = null"
                        >
                        <p v-if="selectedContent" class="mt-1 text-xs text-green-600">المحدد: {{ selectedContent.name }}</p>

                        <div v-if="showContentResults && !selectedContent" class="absolute z-40 mt-1 max-h-52 w-full overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg">
                            <p v-if="contentSearching" class="px-3 py-2 text-xs text-gray-400">جاري البحث…</p>
                            <button
                                v-for="product in contentResults"
                                :key="`${product.product_id}-${product.name}`"
                                type="button"
                                class="w-full border-b border-gray-50 px-3 py-2 text-start last:border-0 hover:bg-gray-50"
                                @click="selectContent(product)"
                            >
                                <p class="text-sm font-semibold text-gray-800">{{ product.name }}</p>
                                <p class="mt-0.5 text-xs text-gray-500" dir="ltr">{{ product.unit_price }}</p>
                            </button>
                            <p v-if="!contentSearching && contentResults.length === 0" class="px-3 py-2 text-xs text-gray-400">لا توجد نتائج.</p>
                        </div>
                    </div>

                    <div v-if="selectedContent && calendars.length === 0 && !availabilityLoading">
                        <p class="text-xs text-amber-600">لا يوجد تقويم مرتبط. اربط تقويماً من إعدادات المحتوى أولاً.</p>
                    </div>

                    <div v-if="calendars.length > 0">
                        <label class="mb-1 block text-xs text-gray-500">
                            {{ type === 'unit_rental' ? 'مخزون الوحدات / التقويم' : 'مقدم الخدمة / التقويم' }}
                        </label>
                        <select
                            v-model="calendarId"
                            class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
                            @change="onCalendarChange"
                        >
                            <option value="">اختر التقويم ..</option>
                            <option v-for="calendar in calendars" :key="calendar.id" :value="String(calendar.id)">
                                {{ calendar.type_label ? `${calendar.type_label}: ` : '' }}{{ calendar.name }}
                            </option>
                        </select>
                    </div>

                    <div v-if="calendarId && availableDates.length > 0">
                        <label class="mb-1 block text-xs text-gray-500">تاريخ الحجز</label>
                        <select
                            v-model="bookingDate"
                            class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
                            dir="ltr"
                            @change="onDateChange"
                        >
                            <option value="">اختر التاريخ ..</option>
                            <option v-for="date in availableDates" :key="date" :value="date">
                                {{ formatDateLabel(date) }}
                            </option>
                        </select>
                    </div>
                    <p v-else-if="calendarId && !availabilityLoading" class="text-xs text-amber-600">لا توجد تواريخ متاحة في التقويم المحدد.</p>

                    <div v-if="bookingDate && timeSlots.length > 0">
                        <label class="mb-1 block text-xs text-gray-500">
                            {{ type === 'unit_rental' ? 'فترة التأجير' : 'وقت الحجز' }}
                        </label>
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="slot in timeSlots"
                                :key="`${slot.start_at}-${slot.end_at}`"
                                type="button"
                                class="rounded-lg border px-3 py-1.5 text-sm transition"
                                :class="slot.available
                                    ? (startAt === slot.start_at
                                        ? 'border-primary-500 bg-primary-50 font-semibold text-primary-700'
                                        : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50')
                                    : 'cursor-not-allowed select-none border-gray-100 bg-gray-50 text-gray-300 line-through'"
                                :disabled="!slot.available"
                                dir="ltr"
                                @click="selectTimeSlot(slot)"
                            >
                                {{ slot.label }}
                            </button>
                        </div>
                    </div>
                    <p v-else-if="bookingDate && !availabilityLoading" class="text-xs text-amber-600">لا توجد فترات في هذا التاريخ.</p>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs text-gray-500">الحالة</label>
                            <select
                                v-model="status"
                                class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
                            >
                                <option v-for="(label, key) in bookingStatusOptions" :key="key" :value="key">{{ label }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs text-gray-500">السعر</label>
                            <div class="rounded-lg border border-gray-100 bg-white px-3 py-2 text-sm font-semibold text-gray-800">
                                <Money :amount="unitPrice" />
                            </div>
                        </div>
                    </div>
                </div>
            </Box>
        </div>

        <div class="flex shrink-0 items-center justify-end gap-2 border-t border-gray-100 bg-gray-50 px-5 py-3">
            <Button type="button" variant="outline" label="إلغاء" @click="closeModal('add-booking')" />
            <Button type="button" label="تأكيد الحجز" :disabled="!canSubmit" @click="submit" />
        </div>
    </div>
</template>
