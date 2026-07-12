<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { storeToRefs } from 'pinia';
import { useRoute, useRouter } from 'vue-router';
import Dropdown from '../Dropdown.vue';
import Badge from '../ui/Badge.vue';
import Button from '../ui/Button.vue';
import Icon from '../ui/Icon.vue';
import Modal from '../ui/Modal.vue';
import AddOrder from './AddOrder.vue';
import { statusFilterColors, statusFilters, walkingClientLabel } from '../../data/orders.js';
import { openModal } from '../../lib/modal.js';
import { useOrdersStore } from '../../stores/orders.js';

const route = useRoute();
const router = useRouter();
const ordersStore = useOrdersStore();
const { items, meta, search, status, loading, loaded, error, isEmpty, hasPages } = storeToRefs(ordersStore);

const selectedIds = ref([]);
const searchInput = ref('');
let searchTimer = null;

const activeStatus = computed(() => {
    const value = route.query.status;

    return typeof value === 'string' && statusFilters.some((filter) => filter.value === value)
        ? value
        : '';
});

const allChecked = computed({
    get: () => items.value.length > 0 && items.value.every((item) => selectedIds.value.includes(item.id)),
    set: (checked) => {
        selectedIds.value = checked ? items.value.map((item) => item.id) : [];
    },
});

function statusTagClass(filter) {
    const palette = statusFilterColors[filter.color] ?? statusFilterColors.gray;

    return activeStatus.value === filter.value ? palette.active : palette.idle;
}

function allTagClass() {
    const palette = statusFilterColors.gray;

    return activeStatus.value === '' ? palette.active : palette.idle;
}

async function setStatusFilter(nextStatus) {
    const query = { ...route.query };

    if (nextStatus) {
        query.status = nextStatus;
    } else {
        delete query.status;
    }

    selectedIds.value = [];
    await router.replace({ query });
}

watch(searchInput, (value) => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        selectedIds.value = [];
        ordersStore.setSearch(value);
    }, 300);
});

watch(items, () => {
    selectedIds.value = selectedIds.value.filter((id) => items.value.some((item) => item.id === id));
});

watch(activeStatus, (value, previous) => {
    if (value === previous || status.value === value) {
        return;
    }

    selectedIds.value = [];
    ordersStore.setStatus(value);
});

function deleteSelected() {
    ordersStore.removeLocal(selectedIds.value);
    selectedIds.value = [];
}

function goToPage(page) {
    if (page < 1 || page > meta.value.last_page || page === meta.value.current_page) {
        return;
    }

    selectedIds.value = [];
    ordersStore.goToPage(page);
}

onMounted(() => {
    searchInput.value = search.value;
    const statusFromUrl = activeStatus.value;

    if (!loaded.value || status.value !== statusFromUrl) {
        ordersStore.fetchOrders({ status: statusFromUrl });
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

            <div class="flex w-full items-center gap-x-4">
                <div class="ps-1">
                    <div class="flex items-center">
                        <input v-model="allChecked" type="checkbox" class="h-4 w-4 rounded-xl border-stone-300 shadow-sm">
                    </div>
                </div>

                <div class="flex-grow bg-stone-100 rounded-lg">
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

                <div v-if="selectedIds.length > 0" class="flex items-center gap-x-2">
                    <button
                        type="button"
                        class="flex items-center gap-2 rounded-lg border bg-white px-3 py-1.5 text-sm text-stone-700 hover:bg-stone-200"
                        @click="deleteSelected"
                    >
                        <svg class="h-4 w-4 text-stone-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M10 11v6M14 11v6M5 7l1 13a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2l1-13M9 7V4h6v3" />
                        </svg>
                        حذف المحدد ({{ selectedIds.length }})
                    </button>
                </div>

                <div>
                    <Button type="button" label="إضافة طلب" @click="openModal('add-order')">
                        <template #icon>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                                <path d="M12 8v8M8 12h8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M9 22h6c5 0 7-2 7-7V9c0-5-2-7-7-7H9C4 2 2 4 2 9v6c0 5 2 7 7 7Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </template>
                    </Button>
                </div>

                <Modal name="add-order" title="إضافة طلب جديد" size="4xl" :escape="false">
                    <AddOrder />
                </Modal>
            </div>
        </div>

        <div class="relative p-1">
            <div
                v-if="loading"
                class="animate-pulse"
                aria-busy="true"
                aria-label="جاري تحميل الطلبات"
            >
                <div
                    v-for="n in 6"
                    :key="`skeleton-${n}`"
                    class="flex w-full items-center gap-x-4 px-4 py-3 sm:px-6"
                >
                    <div class="h-4 w-4 shrink-0 rounded-xl bg-stone-200"></div>

                    <div class="flex min-w-0 flex-1 items-start gap-x-3">
                        <div class="h-11 w-11 shrink-0 rounded-xl bg-stone-200"></div>
                        <div class="min-w-0 flex-1 space-y-2.5">
                            <div
                                class="h-4 rounded-md bg-stone-200"
                                :class="n % 2 === 0 ? 'w-16' : 'w-20'"
                            ></div>
                            <div class="flex flex-wrap items-center gap-x-1.5 gap-y-1">
                                <div class="h-5 w-20 rounded-md bg-stone-100"></div>
                                <div class="h-5 w-16 rounded-md bg-stone-100"></div>
                                <div class="h-5 w-14 rounded-md bg-stone-100"></div>
                                <div
                                    class="h-5 rounded-md bg-stone-100"
                                    :class="n % 3 === 0 ? 'w-16' : 'w-20'"
                                ></div>
                                <div
                                    class="h-5 rounded-md bg-stone-100"
                                    :class="n % 2 === 0 ? 'w-24' : 'w-28'"
                                ></div>
                            </div>
                        </div>
                    </div>

                    <div class="flex shrink-0 flex-col items-center gap-1 pe-1 px-1.5 py-1.5">
                        <div class="h-1.5 w-1.5 rounded-full bg-stone-200"></div>
                        <div class="h-1.5 w-1.5 rounded-full bg-stone-200"></div>
                        <div class="h-1.5 w-1.5 rounded-full bg-stone-200"></div>
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
                    @click="ordersStore.fetchOrders({ page: meta.current_page })"
                >
                    إعادة المحاولة
                </button>
            </div>

            <div
                v-else-if="isEmpty"
                class="flex flex-col items-center justify-center gap-2 p-10 text-center"
            >
                <svg class="h-12 w-12 p-0.5 text-stone-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.5 19H8c-4 0-6-1-6-6V8c0-4 2-6 6-6h8c4 0 6 2 6 6v5c0 4-2 6-6 6h-.5c-.31 0-.61.15-.8.4l-1.5 2c-.66.88-1.74.88-2.4 0l-1.5-2c-.16-.22-.53-.4-.8-.4Z" />
                </svg>
                <p class="text-stone-700">لا توجد طلبات.</p>
                <small class="text-stone-500">سيتم عرض الطلبات هنا بعد إنشائها أو استلامها من المتجر.</small>
            </div>

            <div v-else-if="items.length > 0" class="divide-y-2 divide-dotted divide-stone-200/50">
                <div
                    v-for="item in items"
                    :key="item.id"
                    class="flex w-full items-center gap-x-4 px-4 py-3 last:rounded-b-2xl hover:bg-stone-50 sm:px-6"
                >
                    <div class="shrink-0">
                        <input v-model="selectedIds" :value="item.id" type="checkbox" class="h-4 w-4 rounded-xl border-stone-300 shadow-sm">
                    </div>

                    <RouterLink :to="`/orders/${item.uuid}`" class="flex min-w-0 flex-1 items-start gap-x-3">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-stone-100 text-green-500">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.5 19H8c-4 0-6-1-6-6V8c0-4 2-6 6-6h8c4 0 6 2 6 6v5c0 4-2 6-6 6h-.5c-.31 0-.61.15-.8.4l-1.5 2c-.66.88-1.74.88-2.4 0l-1.5-2c-.16-.22-.53-.4-.8-.4Z" />
                            </svg>
                        </div>

                        <div class="min-w-0 flex-1">
                            <h2 class="text-sm font-bold text-stone-800">#{{ item.number }}</h2>

                            <div class="mt-1.5 flex flex-wrap items-center gap-x-1.5 gap-y-1">
                                <Badge color="gray">{{ item.created }}</Badge>
                                <Badge :color="item.status_color">{{ item.status_label }}</Badge>
                                <Badge :color="item.payment_status_color">{{ item.payment_status_label }}</Badge>
                                <Badge color="gray"><Money :formatted="item.grand_total_formatted" /></Badge>
                                <Badge color="blue">{{ item.client ?? walkingClientLabel }}</Badge>
                            </div>
                        </div>
                    </RouterLink>

                    <div class="shrink-0 pe-1">
                        <Dropdown width="w-36">
                            <template #trigger>
                                <button type="button" class="rounded p-1.5 text-stone-500 hover:bg-stone-100" aria-label="menu">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                                        <circle cx="12" cy="5" r="1.6" />
                                        <circle cx="12" cy="12" r="1.6" />
                                        <circle cx="12" cy="19" r="1.6" />
                                    </svg>
                                </button>
                            </template>
                            <RouterLink :to="`/orders/${item.uuid}`" class="flex items-center gap-x-2 rounded p-1.5 hover:bg-stone-100">تعديل</RouterLink>
                        </Dropdown>
                    </div>
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
    </div>
</template>
