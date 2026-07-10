<script setup>
import { ref, computed } from 'vue';
import Dropdown from '../Dropdown.vue';
import Badge from '../ui/Badge.vue';
import { invoices, invoiceStatusLabel, invoiceStatusColor, invoiceTypeLabel } from '../../data/invoices.js';
import { money } from '../../data/orders.js';

// Port of resources/views/admin/orders/invoices-table.blade.php (dummy data).
const search = ref('');

const results = computed(() => {
    const query = search.value.trim().toLowerCase();
    if (!query) {
        return invoices;
    }
    return invoices.filter(
        (item) => item.s_number.toLowerCase().includes(query) || (item.order_label || '').toLowerCase().includes(query),
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
                    <input v-model="search" type="text" placeholder="ابحث برقم الفاتورة أو الطلب .." class="block w-full rounded-lg border border-transparent py-1.5 ps-10 text-gray-800 ring-inset ring-gray-200 placeholder:text-gray-400 focus:border-primary-500 focus:outline-none sm:text-sm sm:leading-6">
                </div>
            </div>
        </div>

        <div class="relative p-1">
            <div v-if="results.length === 0" class="flex flex-col items-center justify-center gap-2 p-10 text-center">
                <svg class="h-12 w-12 p-0.5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 3h9l3 3v15H6zM14 3v4h4M9 12h6M9 16h6" /></svg>
                <p class="text-gray-700">لا توجد فواتير.</p>
                <small class="text-gray-500">ستظهر فواتير المتجر هنا بعد تسجيل أي دفعة أو إصدار فاتورة.</small>
            </div>

            <div v-else>
                <div
                    v-for="item in results"
                    :key="item.id"
                    class="flex w-full items-center justify-between gap-x-4 px-4 last:rounded-b-2xl hover:bg-gray-50 sm:px-6"
                >
                    <div class="min-w-0 flex-1 py-3">
                        <RouterLink :to="`/invoices/${item.uuid}`" class="block">
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                                <h2 class="text-lg font-semibold text-gray-700" dir="ltr">{{ item.s_number }}</h2>
                                <Badge :color="invoiceStatusColor(item.status)">{{ invoiceStatusLabel(item.status) }}</Badge>
                                <Badge color="gray">{{ invoiceTypeLabel(item.type) }}</Badge>
                            </div>
                            <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-gray-500">
                                <span v-if="item.order_label" class="inline-flex items-center gap-x-1 rounded-md bg-gray-100 p-1 px-2 text-xs">{{ item.order_label }}</span>
                                <span v-if="item.user" class="truncate">{{ item.user }}</span>
                                <span class="text-xs text-gray-400">{{ item.issued }}</span>
                            </div>
                        </RouterLink>
                    </div>

                    <div class="hidden shrink-0 items-center gap-x-6 text-sm text-gray-600 sm:flex">
                        <div class="text-end">
                            <div class="font-bold text-gray-800">{{ money(item.total_after_vat) }}</div>
                            <div class="mt-0.5 text-xs text-gray-400">مدفوع {{ money(item.amount_paid) }}</div>
                        </div>
                        <div class="min-w-24 text-end">
                            <div>{{ item.issued }}</div>
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
    </div>
</template>
