<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { storeToRefs } from 'pinia';
import { useRoute } from 'vue-router';
import Badge from '../ui/Badge.vue';
import { useClientsStore } from '../../stores/clients.js';

const route = useRoute();
const clientsStore = useClientsStore();
const { invoices, invoicesEmpty, invoicesHasPages } = storeToRefs(clientsStore);

const searchInput = ref('');
let searchTimer = null;

const clientUuid = computed(() => route.params.uuid);
const items = computed(() => invoices.value.items);
const meta = computed(() => invoices.value.meta);
const loading = computed(() => invoices.value.loading);
const error = computed(() => invoices.value.error);

watch(searchInput, (value) => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        if (clientUuid.value) {
            clientsStore.setClientInvoicesSearch(clientUuid.value, value);
        }
    }, 300);
});

function goToPage(page) {
    if (!clientUuid.value || page < 1 || page > meta.value.last_page || page === meta.value.current_page) {
        return;
    }

    clientsStore.goToClientInvoicesPage(clientUuid.value, page);
}

onMounted(() => {
    searchInput.value = invoices.value.search;
    if (clientUuid.value && (invoices.value.clientUuid !== clientUuid.value || !invoices.value.loaded)) {
        clientsStore.fetchClientInvoices(clientUuid.value);
    }
});
</script>

<template>
    <div class="divide-y divide-dotted divide-gray-200">
        <div class="flex w-full items-center gap-x-7 bg-gray-100 p-3">
            <div class="flex-grow">
                <div class="relative col-span-3 text-sm text-gray-800">
                    <div class="pointer-events-none absolute bottom-0 right-0 top-0 flex items-center ps-2 text-gray-500">
                        <svg class="h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <circle cx="11" cy="11" r="7" />
                            <path stroke-linecap="round" d="m20 20-3-3" />
                        </svg>
                    </div>
                    <input
                        v-model="searchInput"
                        type="text"
                        placeholder="ابحث برقم الفاتورة أو الطلب .."
                        class="block w-full rounded-lg border border-transparent py-1.5 ps-10 text-gray-800 ring-inset ring-gray-200 placeholder:text-gray-400 focus:border-primary-500 focus:outline-none sm:text-sm sm:leading-6"
                    >
                </div>
            </div>
        </div>

        <div class="relative p-1">
            <div
                v-if="loading"
                class="absolute inset-0 z-10 flex items-center justify-center bg-white/50"
            >
                <svg class="h-10 w-10 animate-spin text-gray-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" d="M12 3a9 9 0 1 0 9 9" />
                </svg>
            </div>

            <div
                v-if="error && !loading"
                class="flex flex-col items-center justify-center gap-2 p-10 text-center"
            >
                <p class="text-sm text-red-600">{{ error }}</p>
                <button
                    type="button"
                    class="rounded-lg border bg-white px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-100"
                    @click="clientsStore.fetchClientInvoices(clientUuid, { page: meta.current_page })"
                >
                    إعادة المحاولة
                </button>
            </div>

            <div
                v-else-if="invoicesEmpty"
                class="flex flex-col items-center justify-center gap-2 p-10 text-center"
            >
                <svg class="h-12 w-12 p-0.5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M6 3h9l3 3v15H6zM14 3v4h4M9 12h6M9 16h6" />
                </svg>
                <p class="text-gray-700">لا توجد فواتير.</p>
                <small class="text-gray-500">ستظهر فواتير هذا العميل هنا بعد تسجيل أي دفعة أو إصدار فاتورة لطلباته.</small>
            </div>

            <div v-else-if="items.length > 0">
                <div
                    v-for="item in items"
                    :key="item.id"
                    class="flex w-full items-center justify-between gap-x-4 px-4 last:rounded-b-2xl hover:bg-gray-50 sm:px-6"
                >
                    <div class="min-w-0 flex-1 py-3">
                        <RouterLink :to="`/invoices/${item.uuid}`" class="block">
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                                <h2 class="text-lg font-semibold text-gray-700" dir="ltr">{{ item.s_number }}</h2>
                                <Badge :color="item.status_color" size="sm">{{ item.status_label }}</Badge>
                                <Badge color="gray" size="sm">{{ item.type_label }}</Badge>
                            </div>
                            <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-gray-500">
                                <span
                                    v-if="item.order_label"
                                    class="inline-flex items-center gap-x-1 rounded-md bg-gray-100 p-1 px-2 text-xs"
                                >{{ item.order_label }}</span>
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
                            <div class="mt-0.5 text-xs text-gray-400">{{ item.time }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div
            v-if="invoicesHasPages"
            class="flex items-center justify-between rounded-b-2xl bg-gray-50 p-4 px-6"
        >
            <div class="text-sm text-gray-500">
                النتائج : <b>{{ meta.total.toLocaleString('ar-SA') }}</b>
            </div>
            <div class="flex items-center gap-2">
                <button
                    type="button"
                    class="rounded-lg border bg-white px-3 py-1.5 text-sm text-gray-700 disabled:opacity-40"
                    :disabled="meta.current_page <= 1 || loading"
                    @click="goToPage(meta.current_page - 1)"
                >
                    السابق
                </button>
                <span class="text-sm text-gray-500">{{ meta.current_page }} / {{ meta.last_page }}</span>
                <button
                    type="button"
                    class="rounded-lg border bg-white px-3 py-1.5 text-sm text-gray-700 disabled:opacity-40"
                    :disabled="meta.current_page >= meta.last_page || loading"
                    @click="goToPage(meta.current_page + 1)"
                >
                    التالي
                </button>
            </div>
        </div>
    </div>
</template>
