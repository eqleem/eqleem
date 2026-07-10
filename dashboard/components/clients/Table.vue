<script setup>
import { ref, computed } from 'vue';
import Dropdown from '../Dropdown.vue';
import { clients, removeClients, avatarFor } from '../../data/clients.js';

// Port of resources/views/admin/clients/table.blade.php (dummy data).
const search = ref('');
const selectedIds = ref([]);

const results = computed(() => {
    const query = search.value.trim().toLowerCase();
    if (!query) {
        return clients;
    }
    return clients.filter(
        (item) =>
            item.name.toLowerCase().includes(query) ||
            (item.email || '').toLowerCase().includes(query) ||
            (item.phone || '').includes(query),
    );
});

const allChecked = computed({
    get: () => results.value.length > 0 && results.value.every((item) => selectedIds.value.includes(item.id)),
    set: (checked) => {
        selectedIds.value = checked ? results.value.map((item) => item.id) : [];
    },
});

function deleteSelected() {
    removeClients(selectedIds.value);
    selectedIds.value = [];
}
</script>

<template>
    <div class="divide-y divide-dotted divide-gray-200">
        <div class="flex w-full items-center gap-x-7 bg-gray-100 p-3">
            <div class="ps-3">
                <div class="flex items-center">
                    <input v-model="allChecked" type="checkbox" class="h-4 w-4 rounded-xl border-gray-300 shadow-sm">
                </div>
            </div>

            <div class="flex-grow">
                <div class="relative col-span-3 text-sm text-gray-800">
                    <div class="pointer-events-none absolute bottom-0 right-0 top-0 flex items-center ps-2 text-gray-500">
                        <svg class="h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <circle cx="11" cy="11" r="7" />
                            <path stroke-linecap="round" d="m20 20-3-3" />
                        </svg>
                    </div>
                    <input
                        v-model="search"
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
                v-if="results.length === 0"
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

            <div v-else>
                <div
                    v-for="item in results"
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
                            <img class="h-10 w-10 flex-none rounded-full bg-gray-50" :src="avatarFor(item.name)" alt="">
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
    </div>
</template>
