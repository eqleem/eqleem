<script setup>
import { ref, computed } from 'vue';
import Dropdown from '../Dropdown.vue';
import Badge from '../ui/Badge.vue';
import { payments, paymentStatusLabel, paymentStatusColor, reasonLabel, sourceTypeLabel, gatewayLabel } from '../../data/payments.js';
import { money } from '../../data/orders.js';

// Port of resources/views/admin/orders/payments-table.blade.php (dummy data).
const search = ref('');

const results = computed(() => {
    const query = search.value.trim().toLowerCase();
    if (!query) {
        return payments;
    }
    return payments.filter(
        (item) => String(item.id).includes(query) || (item.payer || '').toLowerCase().includes(query) || (item.email || '').toLowerCase().includes(query),
    );
});
</script>

<template>
    <div class="divide-y divide-dotted divide-gray-200">
        <div class="flex w-full items-center gap-x-7 bg-gray-100 p-3">
            <div class="flex-grow">
                <div class="relative col-span-3 text-sm text-gray-800">
                    <div class="pointer-events-none absolute bottom-0 right-0 top-0 flex items-center ps-2 text-gray-500">
                        <svg class="h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="7" /><path stroke-linecap="round" d="m20 20-3-3" /></svg>
                    </div>
                    <input v-model="search" type="text" placeholder="ابحث .." class="block w-full rounded-lg border border-transparent py-1.5 ps-10 text-gray-800 ring-inset ring-gray-200 placeholder:text-gray-400 focus:border-primary-500 focus:outline-none sm:text-sm sm:leading-6">
                </div>
            </div>
        </div>

        <div class="relative p-1">
            <div v-if="results.length === 0" class="flex flex-col items-center justify-center gap-2 p-10 text-center">
                <svg class="h-12 w-12 p-0.5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M5 21V5a1 1 0 0 1 1.5-.9L8 5l1.5-1 1.5 1L12 5l1.5-1 1.5 1L16 5l1.5-.9A1 1 0 0 1 19 5v16l-2-1-2 1-2-1-2 1-2-1-2 1Z" /></svg>
                <p class="text-gray-700">لا توجد عمليات دفع.</p>
                <small class="text-gray-500">ستظهر عمليات الدفع هنا بعد إتمام أي عملية شراء أو اشتراك.</small>
            </div>

            <div v-else>
                <div
                    v-for="item in results"
                    :key="item.id"
                    class="flex w-full items-center justify-between gap-x-4 px-4 last:rounded-b-2xl hover:bg-gray-50 sm:px-6"
                >
                    <div class="min-w-0 flex-1 py-3">
                        <RouterLink :to="`/payments/${item.uuid}`" class="block">
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                                <h2 class="text-lg font-semibold text-gray-700">#{{ item.id }}</h2>
                                <Badge :color="paymentStatusColor(item.status)">{{ paymentStatusLabel(item.status) }}</Badge>
                                <Badge color="gray">{{ reasonLabel(item.reason) }}</Badge>
                            </div>
                            <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-gray-500">
                                <span v-if="item.payer" class="truncate">{{ item.payer }}</span>
                                <span v-if="item.email" class="inline-flex items-center gap-x-1 truncate rounded-md bg-gray-100 p-1 px-2 text-xs">{{ item.email }}</span>
                                <span v-if="item.gateway" class="inline-flex items-center gap-x-1 rounded-md bg-gray-100 p-1 px-2 text-xs">{{ gatewayLabel(item.gateway) }}</span>
                                <span v-if="item.card" class="inline-block font-mono text-xs" dir="ltr">{{ item.card }}</span>
                            </div>
                        </RouterLink>
                    </div>

                    <div class="hidden shrink-0 items-center gap-x-6 text-sm text-gray-600 sm:flex">
                        <div class="text-end">
                            <div class="font-bold text-gray-800">{{ money(item.amount) }}</div>
                            <div class="mt-0.5 text-xs text-gray-400">{{ sourceTypeLabel(item.source_type) }}</div>
                        </div>
                        <div class="min-w-24 text-end">
                            <div>{{ item.created }}</div>
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
                            <RouterLink :to="`/payments/${item.uuid}`" class="flex items-center gap-x-2 rounded p-1.5 hover:bg-stone-100">تعديل</RouterLink>
                        </Dropdown>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
