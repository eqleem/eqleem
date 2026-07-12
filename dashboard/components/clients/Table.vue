<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { storeToRefs } from 'pinia';
import Dropdown from '../Dropdown.vue';
import { useClientsStore } from '../../stores/clients.js';

const clientsStore = useClientsStore();
const { items, meta, search, loading, loaded, error, isEmpty, hasPages } = storeToRefs(clientsStore);

const selectedIds = ref([]);
const searchInput = ref('');
let searchTimer = null;

const allChecked = computed({
    get: () => items.value.length > 0 && items.value.every((item) => selectedIds.value.includes(item.id)),
    set: (checked) => {
        selectedIds.value = checked ? items.value.map((item) => item.id) : [];
    },
});

watch(searchInput, (value) => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        selectedIds.value = [];
        clientsStore.setSearch(value);
    }, 300);
});

watch(items, () => {
    selectedIds.value = selectedIds.value.filter((id) => items.value.some((item) => item.id === id));
});

function deleteSelected() {
    clientsStore.removeLocal(selectedIds.value);
    selectedIds.value = [];
}

function goToPage(page) {
    if (page < 1 || page > meta.value.last_page || page === meta.value.current_page) {
        return;
    }

    selectedIds.value = [];
    clientsStore.goToPage(page);
}

onMounted(() => {
    searchInput.value = search.value;
    if (!loaded.value) {
        clientsStore.fetchClients();
    }
});
</script>

<template>
    <div class="divide-y divide-dotted divide-gray-200">
        <div class="flex w-full items-center gap-x-7 bg-white p-3">
            <div class="ps-3">
                <div class="flex items-center">
                    <input v-model="allChecked" type="checkbox" class="h-4 w-4 rounded-xl border-gray-300 shadow-sm">
                </div>
            </div>

            <div class="flex-grow bg-gray-100 rounded-lg">
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
                        placeholder="ابحث .."
                        class="block w-full rounded-lg border border-transparent py-1.5 ps-10 text-gray-800 ring-inset ring-gray-200 placeholder:text-gray-400 focus:border-primary-500 focus:outline-none sm:text-sm sm:leading-6"
                    >
                </div>
            </div>

            <div v-show="selectedIds.length > 0" class="flex items-center gap-x-2">
                <div class="flex items-center gap-1 text-sm text-gray-600">
                    <span>{{ selectedIds.length }}</span>
                    <span>محددة</span>
                </div>

                <button
                    type="button"
                    class="flex items-center gap-2 rounded-lg border bg-white px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-200"
                    @click="deleteSelected"
                >
                    <svg class="h-4 w-4 text-gray-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M10 11v6M14 11v6M5 7l1 13a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2l1-13M9 7V4h6v3" />
                    </svg>
                    حذف المحدد<span>({{ selectedIds.length }})</span>
                </button>
            </div>
        </div>

        <div class="relative overflow-visible p-1">
            <div
                v-if="loading"
                class="animate-pulse"
                aria-busy="true"
                aria-label="جاري تحميل العملاء"
            >
                <div
                    v-for="n in 6"
                    :key="`skeleton-${n}`"
                    class="flex w-full items-center justify-between gap-x-7"
                >
                    <div class="ps-6">
                        <div class="h-4 w-4 rounded-xl bg-gray-200"></div>
                    </div>

                    <div class="w-full py-3">
                        <div class="flex items-center gap-x-2">
                            <div class="h-10 w-10 flex-none rounded-full bg-gray-200"></div>
                            <div class="min-w-0 flex-1 space-y-2">
                                <div
                                    class="h-5 rounded-md bg-gray-200"
                                    :class="n % 3 === 0 ? 'w-28' : n % 2 === 0 ? 'w-40' : 'w-36'"
                                ></div>
                                <div class="flex items-center gap-x-2">
                                    <div class="h-2 w-2 rounded-full bg-gray-200"></div>
                                    <div
                                        class="h-5 rounded-md bg-gray-100"
                                        :class="n % 2 === 0 ? 'w-32' : 'w-40'"
                                    ></div>
                                    <div class="h-4 w-20 rounded-md bg-gray-100"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pe-6">
                        <div class="flex flex-col items-center gap-1 px-1.5 py-1.5">
                            <div class="h-1.5 w-1.5 rounded-full bg-gray-200"></div>
                            <div class="h-1.5 w-1.5 rounded-full bg-gray-200"></div>
                            <div class="h-1.5 w-1.5 rounded-full bg-gray-200"></div>
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
                    class="rounded-lg border bg-white px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-100"
                    @click="clientsStore.fetchClients({ page: meta.current_page })"
                >
                    إعادة المحاولة
                </button>
            </div>

            <div
                v-else-if="isEmpty"
                class="flex flex-col items-center justify-center gap-2 p-10 text-center"
            >
                <svg class="h-12 w-12 p-0.5 text-gray-400" viewBox="0 0 24 24">
                    <path d="M0 0h24v24H0z" fill="none" />
                    <circle cx="15" cy="6" r="3" fill="currentColor" opacity=".4" />
                    <ellipse cx="16" cy="17" fill="currentColor" opacity=".4" rx="5" ry="3" />
                    <circle cx="9.001" cy="6" r="4" fill="currentColor" />
                    <ellipse cx="9.001" cy="17.001" fill="currentColor" rx="7" ry="4" />
                </svg>
                <p class="text-gray-700">لا يوجد عملاء.</p>
                <small class="text-gray-500">سيتم عرض العملاء هنا بعد إضافتهم أو شراء أحد المنتجات أو الخدمات.</small>
            </div>

            <div v-else-if="items.length > 0">
                <div
                    v-for="item in items"
                    :key="item.id"
                    class="flex w-full items-center justify-between gap-x-7 last:rounded-b-2xl hover:bg-gray-50"
                >
                    <div class="ps-6">
                        <div class="flex items-center">
                            <input v-model="selectedIds" :value="item.id" type="checkbox" class="h-4 w-4 rounded-xl border-gray-300 shadow-sm">
                        </div>
                    </div>

                    <div class="w-full truncate py-3">
                        <RouterLink :to="`/clients/${item.uuid}`" class="flex items-center gap-x-2">
                            <img class="h-10 w-10 flex-none rounded-full bg-gray-50" :src="item.avatar" alt="">
                            <div>
                                <h2 class="text-lg text-gray-700">{{ item.name }}</h2>
                                <div class="mt-1 flex items-center gap-x-2">
                                    <div class="flex items-center gap-x-2 text-base leading-6 text-gray-900">
                                        <div v-if="item.active" class="mt-1 flex items-center gap-x-1.5">
                                            <div class="flex-none rounded-full bg-emerald-500/20 p-1">
                                                <div class="h-1.5 w-1.5 rounded-full bg-emerald-500"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <p class="mt-1 flex items-center gap-1 truncate text-xs text-gray-500">
                                        <span
                                            v-if="item.email"
                                            class="inline-flex items-center gap-x-1 truncate rounded-md bg-gray-100 p-1 px-2 text-xs"
                                        >{{ item.email }}</span>
                                        <span v-if="item.phone" class="inline-block" dir="ltr">{{ item.phone }}</span>
                                    </p>
                                </div>
                            </div>
                        </RouterLink>
                    </div>

                    <div class="pe-6">
                        <Dropdown width="w-36">
                            <template #trigger>
                                <button type="button" class="rounded p-1.5 text-gray-500 hover:bg-gray-100" aria-label="menu">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                                        <circle cx="12" cy="5" r="1.6" />
                                        <circle cx="12" cy="12" r="1.6" />
                                        <circle cx="12" cy="19" r="1.6" />
                                    </svg>
                                </button>
                            </template>
                            <RouterLink :to="`/clients/${item.uuid}`" class="flex items-center gap-x-2 rounded p-1.5 hover:bg-stone-100">تعديل</RouterLink>
                        </Dropdown>
                    </div>
                </div>
            </div>
        </div>

        <div
            v-if="hasPages"
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
                <span class="text-sm text-gray-500">
                    {{ meta.current_page }} / {{ meta.last_page }}
                </span>
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
