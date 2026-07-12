<script setup>
import { storeToRefs } from 'pinia';
import { onMounted } from 'vue';
import { useDashboardStatsStore } from '../../stores/dashboardStats.js';

const store = useDashboardStatsStore();
const { clients, loading, loaded } = storeToRefs(store);

onMounted(() => {
    store.fetchStats();
});
</script>

<template>
    <div class="group relative col-span-1 flex rounded-xl shadow-smx" :class="{ 'animate-pulse opacity-70': loading && !loaded }">
        <RouterLink
            to="/clients"
            class="flex w-16 flex-shrink-0 items-center justify-center group rounded-s-xl bg-pgray-100 text-sm font-medium text-white group-hover:bg-opacity-75"
        >
            <iconify-icon icon="solar:users-group-two-rounded-bold-duotone" class="text-4xl text-primary-500 group-hover:text-primary-600"></iconify-icon>
            <!-- <img class="h-10" :src="'/assets/icons/business/025-team work.svg'" alt=""> -->
        </RouterLink>

        <div class="flex flex-1 items-center justify-between truncate rounded-e-xl border-stone-200 bg-white">
            <RouterLink to="/clients" class="flex-1 truncate px-3 py-3 text-sm">
                <span class="font-semibold text-stone-700 hover:text-stone-600"> العملاء </span>
                <p class="mt-1 text-stone-400">
                    <b class="me-1 text-2xl font-bold text-pgray-800">{{ clients.value }}</b>
                    <span class="ms-1 text-xs font-normal" :title="`${clients.growth}% مقارنة بنفس الفترة السابقة`">
                        <span v-if="clients.growth < 0" dir="ltr" class="text-red-500"> 
                            <iconify-icon icon="solar:arrow-down-bold-duotone" class="text-red-500 text-lg"></iconify-icon>
                            {{ clients.growth }}%</span>
                        <span v-else dir="ltr" class="text-green-500"> 
                            <iconify-icon icon="solar:arrow-up-bold-duotone" class="text-green-500 text-lg"></iconify-icon>
                            {{ clients.growth }}%</span>
                    </span>
                </p>
            </RouterLink>
        </div>
    </div>
</template>
