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
                <svg class="h-12 w-12 p-0.5 text-stone-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M0 0h24v24H0z" fill="none" />
                    <path fill="currentColor" fill-rule="evenodd" d="M1.289 2.763a.75.75 0 0 1 .948-.475l.265.089l.04.013c.626.209 1.155.385 1.572.579c.442.206.826.46 1.117.865c.291.403.412.848.467 1.333c.052.456.052 1.014.052 1.674V9.5c0 1.435.002 2.437.103 3.192c.099.734.28 1.122.556 1.399c.277.277.666.457 1.4.556c.755.101 1.756.103 3.191.103h7a.75.75 0 1 1 0 1.5h-7.055c-1.367 0-2.47 0-3.337-.117c-.9-.12-1.658-.38-2.26-.981c-.601-.602-.86-1.36-.981-2.26c-.117-.867-.117-1.97-.117-3.337V6.883c0-.713 0-1.185-.042-1.546c-.04-.342-.107-.507-.194-.626c-.086-.12-.221-.237-.533-.382c-.33-.153-.777-.304-1.453-.53l-.265-.088a.75.75 0 0 1-.474-.948" clip-rule="evenodd" />
                    <path fill="currentColor" d="M5.745 6q.006.39.005.841V9.5c0 1.435.002 2.437.103 3.192q.023.165.05.308h10.12c.959 0 1.438 0 1.814-.248s.565-.688.942-1.57l.43-1c.809-1.89 1.213-2.833.769-3.508S18.506 6 16.45 6z" opacity=".5" />
                    <path fill="currentColor" d="M7.5 18a1.5 1.5 0 1 1 0 3a1.5 1.5 0 0 1 0-3M18 19.5a1.5 1.5 0 1 0-3 0a1.5 1.5 0 0 0 3 0" />
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
