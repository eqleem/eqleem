<script setup>
import { ref, computed } from 'vue';
import Dropdown from '../Dropdown.vue';
import Badge from '../ui/Badge.vue';
import Button from '../ui/Button.vue';
import Modal from '../ui/Modal.vue';
import AddOrder from './AddOrder.vue';
import { orders, removeOrders, money, statusLabel, statusColor, paymentLabel, paymentColor, walkingClientLabel } from '../../data/orders.js';
import { openModal } from '../../lib/modal.js';

// Port of resources/views/admin/orders/table.blade.php (dummy data).
const search = ref('');
const selectedIds = ref([]);

const results = computed(() => {
    const query = search.value.trim().toLowerCase();
    if (!query) {
        return orders;
    }
    return orders.filter(
        (item) => item.number.includes(query) || (item.client || '').toLowerCase().includes(query),
    );
});

const allChecked = computed({
    get: () => results.value.length > 0 && results.value.every((item) => selectedIds.value.includes(item.id)),
    set: (checked) => {
        selectedIds.value = checked ? results.value.map((item) => item.id) : [];
    },
});

function deleteSelected() {
    removeOrders(selectedIds.value);
    selectedIds.value = [];
}
</script>

<template>
    <div class="divide-y divide-dotted divide-gray-200">
        <div class="flex w-full items-center gap-x-4 bg-gray-100 p-3">
            <div class="ps-1">
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

            <div v-if="selectedIds.length > 0" class="flex items-center gap-x-2">
                <button
                    type="button"
                    class="flex items-center gap-2 rounded-lg border bg-white px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-200"
                    @click="deleteSelected"
                >
                    <svg class="h-4 w-4 text-gray-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
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

        <div class="relative p-1">
            <div
                v-if="results.length === 0"
                class="flex flex-col items-center justify-center gap-2 p-10 text-center"
            >
                <svg class="h-12 w-12 p-0.5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.5 19H8c-4 0-6-1-6-6V8c0-4 2-6 6-6h8c4 0 6 2 6 6v5c0 4-2 6-6 6h-.5c-.31 0-.61.15-.8.4l-1.5 2c-.66.88-1.74.88-2.4 0l-1.5-2c-.16-.22-.53-.4-.8-.4Z" />
                </svg>
                <p class="text-gray-700">لا توجد طلبات.</p>
                <small class="text-gray-500">سيتم عرض الطلبات هنا بعد إنشائها أو استلامها من المتجر.</small>
            </div>

            <div v-else>
                <div
                    v-for="item in results"
                    :key="item.id"
                    class="flex w-full items-center gap-x-4 px-4 py-3 last:rounded-b-2xl hover:bg-gray-50 sm:px-6"
                >
                    <div class="shrink-0">
                        <input v-model="selectedIds" :value="item.id" type="checkbox" class="h-4 w-4 rounded-xl border-gray-300 shadow-sm">
                    </div>

                    <RouterLink :to="`/orders/${item.uuid}`" class="flex min-w-0 flex-1 items-start gap-x-3">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gray-100 text-green-500">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.5 19H8c-4 0-6-1-6-6V8c0-4 2-6 6-6h8c4 0 6 2 6 6v5c0 4-2 6-6 6h-.5c-.31 0-.61.15-.8.4l-1.5 2c-.66.88-1.74.88-2.4 0l-1.5-2c-.16-.22-.53-.4-.8-.4Z" />
                            </svg>
                        </div>

                        <div class="min-w-0 flex-1">
                            <h2 class="text-sm font-bold text-gray-800">#{{ item.number }}</h2>

                            <div class="mt-1.5 flex flex-wrap items-center gap-x-1.5 gap-y-1">
                                <Badge color="gray">{{ item.created }}</Badge>
                                <Badge :color="statusColor(item.status)">{{ statusLabel(item.status) }}</Badge>
                                <Badge :color="paymentColor(item.payment_status)">{{ paymentLabel(item.payment_status) }}</Badge>
                                <Badge color="gray">{{ money(item.grand_total) }}</Badge>
                                <Badge color="blue">{{ item.client ?? walkingClientLabel }}</Badge>
                            </div>
                        </div>
                    </RouterLink>

                    <div class="shrink-0 pe-1">
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
                            <RouterLink :to="`/orders/${item.uuid}`" class="flex items-center gap-x-2 rounded p-1.5 hover:bg-stone-100">تعديل</RouterLink>
                        </Dropdown>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
