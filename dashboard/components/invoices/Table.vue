<script setup>
import { onMounted, ref, watch } from 'vue';
import { storeToRefs } from 'pinia';
import Dropdown from '../Dropdown.vue';
import Badge from '../ui/Badge.vue';
import { useInvoicesStore } from '../../stores/invoices.js';

const invoicesStore = useInvoicesStore();
const { items, meta, search, loading, loaded, error, isEmpty, hasPages } = storeToRefs(invoicesStore);

const searchInput = ref('');
let searchTimer = null;

watch(searchInput, (value) => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => invoicesStore.setSearch(value), 300);
});

function goToPage(page) {
    if (page < 1 || page > meta.value.last_page || page === meta.value.current_page) {
        return;
    }
    invoicesStore.goToPage(page);
}

onMounted(() => {
    searchInput.value = search.value;
    if (!loaded.value) {
        invoicesStore.fetchList();
    }
});
</script>

<template>
    <div class="divide-y divide-dotted divide-gray-200">
        <div class="flex w-full items-center gap-x-7 bg-white p-3">
            <div class="flex-grow bg-gray-100 rounded-lg">
                <div class="relative col-span-3 text-sm text-gray-800">
                    <div class="pointer-events-none absolute bottom-0 right-0 top-0 flex items-center ps-2 text-gray-500">
                        <svg class="h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="7" /><path stroke-linecap="round" d="m20 20-3-3" /></svg>
                    </div>
                    <input v-model="searchInput" type="text" placeholder="ابحث برقم الفاتورة أو الطلب .." class="block w-full rounded-lg border border-transparent py-1.5 ps-10 text-gray-800 ring-inset ring-gray-200 placeholder:text-gray-400 focus:border-primary-500 focus:outline-none sm:text-sm sm:leading-6">
                </div>
            </div>
        </div>

        <div class="relative p-1">
            <div
                v-if="loading"
                class="animate-pulse"
                aria-busy="true"
                aria-label="جاري تحميل الفواتير"
            >
                <div
                    v-for="n in 6"
                    :key="`skeleton-${n}`"
                    class="flex w-full items-center justify-between gap-x-4 px-4 sm:px-6"
                >
                    <div class="min-w-0 flex-1 space-y-2 py-3">
                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                            <div
                                class="h-5 rounded-md bg-gray-200"
                                :class="n % 2 === 0 ? 'w-28' : 'w-36'"
                            ></div>
                            <div class="h-5 w-16 rounded-md bg-gray-100"></div>
                            <div class="h-5 w-14 rounded-md bg-gray-100"></div>
                        </div>
                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                            <div
                                class="h-5 rounded-md bg-gray-100"
                                :class="n % 3 === 0 ? 'w-24' : 'w-32'"
                            ></div>
                            <div class="h-3 w-20 rounded-md bg-gray-100"></div>
                        </div>
                    </div>

                    <div class="hidden shrink-0 items-center gap-x-6 sm:flex">
                        <div class="space-y-1.5 text-end">
                            <div class="ms-auto h-4 w-20 rounded-md bg-gray-200"></div>
                            <div class="ms-auto h-3 w-16 rounded-md bg-gray-100"></div>
                        </div>
                        <div class="min-w-24 space-y-1.5 text-end">
                            <div class="ms-auto h-4 w-16 rounded-md bg-gray-200"></div>
                            <div class="ms-auto h-3 w-12 rounded-md bg-gray-100"></div>
                        </div>
                    </div>

                    <div class="flex shrink-0 flex-col items-center gap-1 pe-2 px-1.5 py-1.5">
                        <div class="h-1.5 w-1.5 rounded-full bg-gray-200"></div>
                        <div class="h-1.5 w-1.5 rounded-full bg-gray-200"></div>
                        <div class="h-1.5 w-1.5 rounded-full bg-gray-200"></div>
                    </div>
                </div>
            </div>

            <div v-else-if="error" class="flex flex-col items-center justify-center gap-2 p-10 text-center">
                <p class="text-sm text-red-600">{{ error }}</p>
                <button type="button" class="rounded-lg border bg-white px-3 py-1.5 text-sm" @click="invoicesStore.fetchList({ page: meta.current_page })">إعادة المحاولة</button>
            </div>

            <div v-else-if="isEmpty" class="flex flex-col items-center justify-center gap-2 p-10 text-center">
                <svg class="h-12 w-12 p-0.5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 3h9l3 3v15H6zM14 3v4h4M9 12h6M9 16h6" /></svg>
                <p class="text-gray-700">لا توجد فواتير.</p>
                <small class="text-gray-500">ستظهر فواتير المتجر هنا بعد تسجيل أي دفعة أو إصدار فاتورة.</small>
            </div>

            <div v-else-if="items.length > 0">
                <div v-for="item in items" :key="item.id" class="flex w-full items-center justify-between gap-x-4 px-4 last:rounded-b-2xl hover:bg-gray-50 sm:px-6">
                    <div class="min-w-0 flex-1 py-3">
                        <RouterLink :to="`/invoices/${item.uuid}`" class="block">
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                                <h2 class="text-lg font-semibold text-gray-700" dir="ltr">{{ item.s_number }}</h2>
                                <Badge :color="item.status_color">{{ item.status_label }}</Badge>
                                <Badge color="gray">{{ item.type_label }}</Badge>
                            </div>
                            <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-gray-500">
                                <span v-if="item.order_label" class="inline-flex items-center gap-x-1 rounded-md bg-gray-100 p-1 px-2 text-xs">{{ item.order_label }}</span>
                                <span v-if="item.issued" class="text-xs text-gray-400">{{ item.issued }}</span>
                            </div>
                        </RouterLink>
                    </div>

                    <div class="hidden shrink-0 items-center gap-x-6 text-sm text-gray-600 sm:flex">
                        <div class="text-end">
                            <div class="font-bold text-gray-800"><Money :formatted="item.total_after_vat_formatted" /></div>
                            <div class="mt-0.5 text-xs text-gray-400 inline-flex items-baseline gap-1">مدفوع <Money :formatted="item.amount_paid_formatted" class="inline-flex" /></div>
                        </div>
                        <div class="min-w-24 text-end">
                            <div>{{ item.date }}</div>
                            <div class="mt-0.5 text-xs text-gray-400" dir="ltr">{{ item.time }}</div>
                        </div>
                    </div>

                    <div class="shrink-0 pe-2">
                        <Dropdown width="w-36">
                            <template #trigger>
                                <button type="button" class="rounded p-1.5 text-gray-500 hover:bg-gray-100" aria-label="menu">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="5" r="1.6" /><circle cx="12" cy="12" r="1.6" /><circle cx="12" cy="19" r="1.6" /></svg>
                                </button>
                            </template>
                            <RouterLink :to="`/invoices/${item.uuid}`" class="flex items-center gap-x-2 rounded p-1.5 hover:bg-stone-100">تعديل</RouterLink>
                        </Dropdown>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="hasPages" class="flex items-center justify-between rounded-b-2xl bg-gray-50 p-4 px-6">
            <div class="text-sm text-gray-500">النتائج : <b>{{ meta.total.toLocaleString('ar-SA') }}</b></div>
            <div class="flex items-center gap-2">
                <button type="button" class="rounded-lg border bg-white px-3 py-1.5 text-sm disabled:opacity-40" :disabled="meta.current_page <= 1 || loading" @click="goToPage(meta.current_page - 1)">السابق</button>
                <span class="text-sm text-gray-500">{{ meta.current_page }} / {{ meta.last_page }}</span>
                <button type="button" class="rounded-lg border bg-white px-3 py-1.5 text-sm disabled:opacity-40" :disabled="meta.current_page >= meta.last_page || loading" @click="goToPage(meta.current_page + 1)">التالي</button>
            </div>
        </div>
    </div>
</template>
