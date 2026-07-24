<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { storeToRefs } from 'pinia';
import { useRoute, useRouter } from 'vue-router';
import Badge from '../ui/Badge.vue';
import Button from '../ui/Button.vue';
import Icon from '../ui/Icon.vue';
import Modal from '../ui/Modal.vue';
import AddBooking from './AddBooking.vue';
import { statusFilterColors, statusFilters, walkingClientLabel } from '../../data/bookings.js';
import { openModal } from '../../lib/modal.js';
import { useBookingsStore } from '../../stores/bookings.js';

import BookingsCalendar from './BookingsCalendar.vue';

const route = useRoute();
const router = useRouter();
const bookingsStore = useBookingsStore();
const { items, meta, search, status, loading, loaded, error, isEmpty, hasPages } = storeToRefs(bookingsStore);

const searchInput = ref('');
let searchTimer = null;

const viewMode = computed(() => (route.query.view === 'calendar' ? 'calendar' : 'list'));

const activeStatus = computed(() => {
    const value = route.query.status;

    return typeof value === 'string' && statusFilters.some((filter) => filter.value === value)
        ? value
        : '';
});

function statusTagClass(filter) {
    const palette = statusFilterColors[filter.color] ?? statusFilterColors.gray;

    return activeStatus.value === filter.value ? palette.active : palette.idle;
}

function allTagClass() {
    const palette = statusFilterColors.gray;

    return activeStatus.value === '' ? palette.active : palette.idle;
}

function viewToggleClass(mode) {
    return viewMode.value === mode
        ? 'bg-white text-stone-900 shadow-sm'
        : 'text-stone-600 hover:text-stone-900';
}

async function setViewMode(mode) {
    const query = { ...route.query, tab: 'bookings' };

    if (mode === 'calendar') {
        query.view = 'calendar';
    } else {
        delete query.view;
    }

    await router.replace({ query });
}

async function setStatusFilter(nextStatus) {
    const query = { ...route.query, tab: 'bookings' };

    if (nextStatus) {
        query.status = nextStatus;
    } else {
        delete query.status;
    }

    await router.replace({ query });
}

watch(searchInput, (value) => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        bookingsStore.setSearch(value, { fetch: (viewMode.value === 'list') });
    }, 300);
});

watch(activeStatus, (value, previous) => {
    if (value === previous || status.value === value) {
        return;
    }

    bookingsStore.setStatus(value, { fetch: (viewMode.value === 'list') });
});

watch(() => viewMode.value === 'list', (list) => {
    if (!list) {
        return;
    }

    if (!loaded.value) {
        bookingsStore.fetchBookings({ status: activeStatus.value });
    }
});

function goToPage(page) {
    if (page < 1 || page > meta.value.last_page || page === meta.value.current_page) {
        return;
    }

    bookingsStore.goToPage(page);
}

onMounted(() => {
    searchInput.value = search.value;
    const statusFromUrl = activeStatus.value;

    if ((viewMode.value === 'list') && (!loaded.value || status.value !== statusFromUrl)) {
        bookingsStore.fetchBookings({ status: statusFromUrl });
    }
});
</script>

<template>
    <div class="divide-y divide-dotted divide-stone-300">
        <div class="space-y-3 bg-white p-3">
            <div class="flex gap-1 overflow-x-auto pb-0.5 bg-stone-100 rounded-lg p-0.5 no-scrollbar">
                <button
                    type="button"
                    class="inline-flex shrink-0 items-center gap-1.5 rounded-lg border-stone-300 px-2.5 py-1.5 text-sm font-medium transition"
                    :class="allTagClass()"
                    @click="setStatusFilter('')"
                >
                    <Icon name="list" class="h-4 w-4" />
                    الكل
                </button>

                <button
                    v-for="filter in statusFilters"
                    :key="filter.value"
                    type="button"
                    class="inline-flex shrink-0 items-center gap-1.5 rounded-lg border px-2.5 py-1.5 text-sm font-medium transition"
                    :class="statusTagClass(filter)"
                    @click="setStatusFilter(filter.value)"
                >
                    <Icon :name="filter.icon" class="h-4 w-4" />
                    {{ filter.label }}
                </button>
            </div>

            <div class="flex w-full items-center gap-x-3 gap-y-2 sm:flex-nowrap sm:gap-x-4">
                <div
                    class="inline-flex shrink-0 rounded-lg bg-stone-100 p-0.5"
                    role="group"
                    aria-label="طريقة العرض"
                >
                    <button
                        type="button"
                        class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1.5 text-sm font-medium transition"
                        :class="viewToggleClass('list')"
                        :aria-pressed="viewMode === 'list'"
                        @click="setViewMode('list')"
                    >
                        <Icon name="list" class="h-4 w-4" />
                        قائمة
                    </button>
                    <button
                        type="button"
                        class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1.5 text-sm font-medium transition"
                        :class="viewToggleClass('calendar')"
                        :aria-pressed="viewMode === 'calendar'"
                        @click="setViewMode('calendar')"
                    >
                        <Icon name="calendar" class="h-4 w-4" />
                        تقويم
                    </button>
                </div>

                <div class="min-w-0 flex-grow bg-stone-100 rounded-lg">
                    <div class="relative col-span-3 text-sm text-stone-800">
                        <div class="pointer-events-none absolute bottom-0 right-0 top-0 flex items-center ps-2 text-stone-500">
                            <svg class="h-5 w-5 text-stone-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <circle cx="11" cy="11" r="7" />
                                <path stroke-linecap="round" d="m20 20-3-3" />
                            </svg>
                        </div>
                        <input
                            v-model="searchInput"
                            type="text"
                            placeholder="ابحث .."
                            class="block w-full rounded-lg border border-transparent py-1.5 ps-10 text-stone-800 ring-inset ring-stone-200 placeholder:text-stone-400 focus:border-primary-500 focus:outline-none sm:text-sm sm:leading-6"
                        >
                    </div>
                </div>

                <div class="shrink-0">
                    <Button type="button" aria-label="إضافة حجز" @click="openModal('add-booking')">
                        <template #icon>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                <path d="M12 8v8M8 12h8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M9 22h6c5 0 7-2 7-7V9c0-5-2-7-7-7H9C4 2 2 4 2 9v6c0 5 2 7 7 7Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </template>
                        <span class="hidden sm:inline">إضافة حجز</span>
                    </Button>
                </div>

                <Modal name="add-booking" title="إضافة حجز جديد" size="4xl" :escape="false">
                    <AddBooking />
                </Modal>
            </div>
        </div>

        <BookingsCalendar
            v-if="viewMode === 'calendar'"
            :search="search"
            :status="status"
        />

        <template v-else>
            <div class="relative p-1">
                <div
                    v-if="loading"
                    class="animate-pulse"
                    aria-busy="true"
                    aria-label="جاري تحميل الحجوزات"
                >
                    <div
                        v-for="n in 6"
                        :key="`skeleton-${n}`"
                        class="flex w-full items-start gap-x-4 px-4 py-3 sm:px-6"
                    >
                        <div class="h-11 w-11 shrink-0 rounded-xl bg-stone-200"></div>
                        <div class="min-w-0 flex-1 space-y-2.5">
                            <div class="h-4 rounded-md bg-stone-200" :class="n % 2 === 0 ? 'w-40' : 'w-52'"></div>
                            <div class="flex flex-wrap gap-1.5">
                                <div class="h-5 w-20 rounded-md bg-stone-100"></div>
                                <div class="h-5 w-24 rounded-md bg-stone-100"></div>
                                <div class="h-5 w-28 rounded-md bg-stone-100"></div>
                                <div class="h-5 w-16 rounded-md bg-stone-100"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    v-else-if="error"
                    class="flex flex-col items-center justify-center gap-2 p-10 text-center"
                >
                    <p class="text-sm text-red-600">{{ error }}</p>
                    <button
                        type="button"
                        class="rounded-lg border bg-white px-3 py-1.5 text-sm text-stone-700 hover:bg-stone-100"
                        @click="bookingsStore.fetchBookings({ page: meta.current_page })"
                    >
                        إعادة المحاولة
                    </button>
                </div>

                <div
                    v-else-if="isEmpty"
                    class="flex flex-col items-center justify-center gap-2 p-10 text-center"
                >
                    <svg class="h-12 w-12 p-0.5 text-stone-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <rect x="3" y="5" width="18" height="16" rx="2" />
                        <path d="M3 10h18M8 3v4M16 3v4" />
                    </svg>
                    <p class="text-stone-700">لا توجد حجوزات.</p>
                    <small class="text-stone-500">سيتم عرض الحجوزات هنا بعد إنشائها من الخدمات أو تأجير الوحدات.</small>
                </div>

                <div v-else-if="items.length > 0" class="divide-y-2 divide-dotted divide-stone-200/50">
                    <div
                        v-for="item in items"
                        :key="item.id"
                        class="flex w-full items-start gap-x-4 px-4 py-3 last:rounded-b-2xl hover:bg-stone-50 sm:px-6"
                    >
                        <RouterLink :to="`/bookings/${item.id}`" class="flex min-w-0 flex-1 items-start gap-x-4">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-stone-100 text-primary-600">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <rect x="3" y="5" width="18" height="16" rx="2" />
                                    <path d="M3 10h18M8 3v4M16 3v4" />
                                </svg>
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                    <h2 class="text-sm font-bold text-stone-800">
                                        {{ item.content?.title ?? 'حجز بدون محتوى' }}
                                    </h2>
                                    <Badge v-if="item.content?.type_label" color="blue">{{ item.content.type_label }}</Badge>
                                </div>

                                <div class="mt-1.5 flex flex-wrap items-center gap-x-1.5 gap-y-1">
                                    <Badge :color="item.status_color">{{ item.status_label }}</Badge>
                                    <Badge color="gray">{{ item.client?.name ?? walkingClientLabel }}</Badge>
                                    <Badge v-if="item.calendar" color="purple">
                                        {{ item.calendar.type_label ? `${item.calendar.type_label}: ` : '' }}{{ item.calendar.name }}
                                    </Badge>
                                    <Badge v-if="item.dates_label" color="white">{{ item.dates_label }}</Badge>
                                    <Badge color="gray"><Money :formatted="item.price_formatted" /></Badge>
                                    <Badge v-if="!item.order?.uuid" color="gray">بدون طلب</Badge>
                                </div>
                            </div>
                        </RouterLink>

                        <RouterLink
                            v-if="item.order?.uuid"
                            :to="`/orders/${item.order.uuid}`"
                            class="inline-flex shrink-0 self-center"
                        >
                            <Badge color="green">طلب #{{ item.order.number }}</Badge>
                        </RouterLink>
                    </div>
                </div>
            </div>

            <div
                v-if="hasPages"
                class="flex items-center justify-between rounded-b-2xl bg-stone-50 p-4 px-6"
            >
                <div class="text-sm text-stone-500">
                    النتائج : <b>{{ meta.total.toLocaleString('ar-SA') }}</b>
                </div>
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        class="rounded-lg border bg-white px-3 py-1.5 text-sm text-stone-700 disabled:opacity-40"
                        :disabled="meta.current_page <= 1 || loading"
                        @click="goToPage(meta.current_page - 1)"
                    >
                        السابق
                    </button>
                    <span class="text-sm text-stone-500">
                        {{ meta.current_page }} / {{ meta.last_page }}
                    </span>
                    <button
                        type="button"
                        class="rounded-lg border bg-white px-3 py-1.5 text-sm text-stone-700 disabled:opacity-40"
                        :disabled="meta.current_page >= meta.last_page || loading"
                        @click="goToPage(meta.current_page + 1)"
                    >
                        التالي
                    </button>
                </div>
            </div>
        </template>
    </div>
</template>
