<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { storeToRefs } from 'pinia';
import { useRoute } from 'vue-router';
import Badge from '../ui/Badge.vue';
import { useClientsStore } from '../../stores/clients.js';

const route = useRoute();
const clientsStore = useClientsStore();
const { orders, ordersEmpty, ordersHasPages } = storeToRefs(clientsStore);

const searchInput = ref('');
let searchTimer = null;

const clientUuid = computed(() => route.params.uuid);
const items = computed(() => orders.value.items);
const meta = computed(() => orders.value.meta);
const loading = computed(() => orders.value.loading);
const error = computed(() => orders.value.error);

watch(searchInput, (value) => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        if (clientUuid.value) {
            clientsStore.setClientOrdersSearch(clientUuid.value, value);
        }
    }, 300);
});

function goToPage(page) {
    if (!clientUuid.value || page < 1 || page > meta.value.last_page || page === meta.value.current_page) {
        return;
    }

    clientsStore.goToClientOrdersPage(clientUuid.value, page);
}

onMounted(() => {
    searchInput.value = orders.value.search;

    if (!clientUuid.value) {
        return;
    }

    if (
        orders.value.clientUuid !== clientUuid.value
        || !orders.value.loaded
    ) {
        clientsStore.fetchClientOrders(clientUuid.value);
    }
});
</script>

<template>
    <div class="divide-y divide-dotted divide-stone-300">
        <div class="space-y-3 bg-white p-3">
            <div class="flex w-full items-center gap-x-7">
                <div class="flex-grow">
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
            </div>
        </div>

        <div class="relative p-1">
            <div
                v-if="loading"
                class="absolute inset-0 z-10 flex items-center justify-center bg-white/50"
            >
                <svg class="h-10 w-10 animate-spin text-stone-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
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
                    class="rounded-lg border bg-white px-3 py-1.5 text-sm text-stone-700 hover:bg-stone-100"
                    @click="clientsStore.fetchClientOrders(clientUuid, { page: meta.current_page })"
                >
                    إعادة المحاولة
                </button>
            </div>

            <div
                v-else-if="ordersEmpty"
                class="flex flex-col items-center justify-center gap-2 p-10 text-center"
            >
                <svg class="h-12 w-12 p-0.5 text-stone-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.5 19H8c-4 0-6-1-6-6V8c0-4 2-6 6-6h8c4 0 6 2 6 6v5c0 4-2 6-6 6h-.5c-.31 0-.61.15-.8.4l-1.5 2c-.66.88-1.74.88-2.4 0l-1.5-2c-.16-.22-.53-.4-.8-.4Z" />
                </svg>
                <p class="text-stone-700">لا توجد طلبات.</p>
                <small class="text-stone-500">سيتم عرض طلبات هذا العميل هنا بعد إنشائها.</small>
            </div>

            <div v-else-if="items.length > 0" class="divide-y-2 divide-dotted divide-stone-200/50">
                <div
                    v-for="item in items"
                    :key="item.id"
                    class="flex w-full items-center justify-between gap-x-4 px-4 last:rounded-b-2xl hover:bg-stone-50 sm:px-6"
                >
                    <div class="min-w-0 flex-1 py-3">
                        <RouterLink :to="`/orders/${item.uuid}`" class="block">
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                                <h2 class="text-lg font-semibold text-stone-700">#{{ item.number }}</h2>
                                <Badge :color="item.status_color" size="sm">{{ item.status_label }}</Badge>
                                <Badge :color="item.payment_status_color" size="sm">{{ item.payment_status_label }}</Badge>
                            </div>
                        </RouterLink>
                    </div>

                    <div class="hidden shrink-0 items-center gap-x-6 text-sm text-stone-600 sm:flex">
                        <div class="text-end">
                            <div class="font-bold text-stone-800"><Money :formatted="item.grand_total_formatted" /></div>
                            <div class="mt-0.5 text-xs text-stone-400">{{ item.items_count }} منتج</div>
                        </div>
                        <div class="min-w-24 text-end">
                            <div>{{ item.date }}</div>
                            <div class="mt-0.5 text-xs text-stone-400">{{ item.time }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div
            v-if="ordersHasPages"
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
                <span class="text-sm text-stone-500">{{ meta.current_page }} / {{ meta.last_page }}</span>
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
