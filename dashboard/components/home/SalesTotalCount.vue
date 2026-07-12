<script setup>
import { storeToRefs } from 'pinia';
import { onMounted } from 'vue';
import { useDashboardStatsStore } from '../../stores/dashboardStats.js';

const store = useDashboardStatsStore();
const { sales, loading, loaded } = storeToRefs(store);

onMounted(() => {
    store.fetchStats();
});
</script>

<template>
    <div class="group relative col-span-1 flex rounded-xl shadow-smx" :class="{ 'animate-pulse opacity-70': loading && !loaded }">
        <RouterLink
            to="/orders?tab=payments"
            class="flex w-16 flex-shrink-0 items-center justify-center group rounded-s-xl bg-pgray-100 text-sm font-medium text-white group-hover:bg-opacity-75"
        >
            <iconify-icon icon="solar:wallet-money-bold-duotone" class="text-4xl text-primary-400 group-hover:text-primary-500"></iconify-icon>
            <!-- <iconify-icon icon="solar:wallet-money-bold-duotone" class="text-5xl text-primary-600"></iconify-icon> -->
            <!-- <img class="h-10" :src="'/assets/icons/business/044-banknote.svg'" alt=""> -->
        </RouterLink>

        <div class="flex flex-1 items-center justify-between truncate rounded-e-xl border-stone-200 bg-white">
            <RouterLink to="/orders?tab=payments" class="flex-1 truncate px-3 py-3 text-sm">
                <span class="font-semibold text-stone-700 hover:text-stone-600"> المبيعات </span>
                <p class="mt-1 text-stone-400">
                    <Money :formatted="sales.value_formatted" class="me-1 text-2xl font-bold text-pgray-800" />
                    <span class="ms-1 text-xs font-normal" :title="`${sales.growth}% مقارنة بنفس الفترة السابقة`">
                        <span v-if="sales.growth < 0" dir="ltr" class="text-red-500"> ⬇ {{ sales.growth }}%</span>
                        <span v-else dir="ltr" class="text-green-500"> ⬆ {{ sales.growth }}%</span>
                    </span>
                </p>
            </RouterLink>
        </div>
    </div>
</template>
